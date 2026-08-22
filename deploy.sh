#!/usr/bin/env bash
# Deploy HSB-IT to the production VM.
# Usage:
#   ./deploy.sh             # choose interactively
#   ./deploy.sh full        # git pull + docker build + docker compose up
#   ./deploy.sh hot         # git pull + copy changed runtime files into the running container
#   ./deploy.sh hot HEAD~2..HEAD
set -euo pipefail

VM_NAME="${VM_NAME:-hieubt-hsb-ams-server}"
VM_ZONE="${VM_ZONE:-asia-southeast1-b}"
REMOTE_DIR="${REMOTE_DIR:-/opt/ams-hbt}"
APP_CONTAINER="${APP_CONTAINER:-ams-hbt-app-1}"
BRANCH="${BRANCH:-master}"
AUTO_GIT_SYNC="${AUTO_GIT_SYNC:-1}"
COMMIT_MESSAGE="${COMMIT_MESSAGE:-}"

usage() {
  cat <<USAGE
Usage: $0 [full|hot] [git-ref-or-range]

Modes:
  full  Pull code, rebuild Docker image, run docker compose up -d.
  hot   Pull code, copy changed runtime files into the running app container,
        clear Laravel caches, and reload Apache. No Docker image rebuild.

Hot patch defaults to files changed by the pull. If there is nothing new to pull,
it falls back to the latest commit. You can pass a ref/range, for example:
  $0 hot HEAD~2..HEAD
USAGE
}

choose_mode() {
  if [ -n "${1:-}" ]; then
    printf '%s' "$1"
    return
  fi

  if [ ! -t 0 ]; then
    printf '%s' "full"
    return
  fi

  echo "==> Chọn kiểu deploy HSB-IT"
  echo "    1) full  - build lại Docker image và docker compose up -d"
  echo "    2) hot   - đẩy nhanh file code vào container đang chạy, không build image"
  printf "Chọn [1/full, 2/hot] (full): "
  read -r answer

  case "${answer:-full}" in
    1|full|FULL) printf '%s' "full" ;;
    2|hot|HOT) printf '%s' "hot" ;;
    h|help|--help|-h) printf '%s' "help" ;;
    *)
      echo "Lựa chọn không hợp lệ: ${answer}" >&2
      exit 2
      ;;
  esac
}

MODE="$(choose_mode "${1:-}")"
PATCH_REF="${2:-}"

case "$MODE" in
  help|--help|-h)
    usage
    exit 0
    ;;
  full|hot)
    ;;
  *)
    echo "Mode không hợp lệ: $MODE" >&2
    usage >&2
    exit 2
    ;;
esac

echo "==> Deploying HSB-IT to ${VM_NAME} (${VM_ZONE})"
echo "==> Remote source: ${REMOTE_DIR}"
echo "==> Branch: origin/${BRANCH}"
echo "==> Mode: ${MODE}"
[ -n "$PATCH_REF" ] && echo "==> Hot patch ref/range: ${PATCH_REF}"
if [ "$AUTO_GIT_SYNC" = "1" ]; then
  echo "==> Syncing local changes to GitHub"
  git add -A

  if git diff --cached --quiet; then
    echo "--- no local changes to commit ---"
  else
    if [ -z "$COMMIT_MESSAGE" ]; then
      COMMIT_MESSAGE="deploy: update HSB-IT $(date '+%Y-%m-%d %H:%M:%S')"
    fi
    git commit -m "$COMMIT_MESSAGE"
  fi

  git push origin "$BRANCH"
else
  echo "==> AUTO_GIT_SYNC=0: skip local commit and push"
fi
echo ""

if [ "$MODE" = "full" ]; then
  gcloud compute ssh "${VM_NAME}" --zone="${VM_ZONE}" --quiet --command="
    set -e
    cd ${REMOTE_DIR}
    echo '--- git pull ---'
    git pull origin ${BRANCH}
    echo '--- docker build (uses layer cache, but any file change re-runs composer install) ---'
    sudo docker build -t hieubt/hsb-it:latest .
    sudo docker tag hieubt/hsb-it:latest hieubt/ams-hbt:latest
    echo '--- docker compose up -d ---'
    sudo docker compose up -d
    echo '--- status ---'
    sudo docker compose ps
    echo '--- post-deploy health check ---'
    sleep 3
    final_url=\$(curl -s -o /dev/null -w '%{url_effective}' -L http://localhost/ || echo 'CURL_FAILED')
    http_code=\$(curl -s -o /dev/null -w '%{http_code}' -L http://localhost/ || echo '000')
    echo \"trang chu: \$http_code -> \$final_url\"
    if [ \"\$http_code\" != '200' ]; then
      echo ''
      echo '!!! CANH BAO: trang chu tra ve HTTP '\"\$http_code\"', khong phai 200 - kiem tra container/log ngay.'
      echo ''
    fi
    secrets_exists='no'
    sudo docker exec ${APP_CONTAINER} test -f /var/lib/hsbit/secrets/database.php 2>/dev/null && secrets_exists='yes'
    if echo \"\$final_url\" | grep -q '/setup' && [ \"\$secrets_exists\" = 'yes' ]; then
      echo ''
      echo '!!! CANH BAO: trang chu dang redirect ra /setup nhung file cau hinh DB (secrets/database.php) van ton tai tren volume.'
      echo '!!! Rat co the deploy nay khien app mat ket noi/tro sai database da cau hinh truoc do (giong su co 2026-08-22). Kiem tra ngay, dung vao setup ma chua ro nguyen nhan.'
      echo ''
    fi
  "
else
  # Built and base64-encoded locally (not piped through gcloud/ssh stdin): on Windows,
  # forwarding a heredoc as stdin through gcloud -> the native ssh.exe -> the remote
  # shell is unreliable (no real pty when bash.exe is invoked from PowerShell), so the
  # remote side can receive garbage instead of this script. Passing it as a --command
  # argument sidesteps stdin forwarding entirely.
  REMOTE_SCRIPT_B64="$(base64 -w0 <<'REMOTE_SCRIPT'
set -euo pipefail

PATCH_REF="$1"
APP_CONTAINER="$2"
BRANCH="$3"
REMOTE_DIR="$4"

is_patchable_path() {
  case "$1" in
    app/*|bootstrap/*|config/*|database/*|public/*|resources/*|routes/*)
      return 0
      ;;
    *)
      return 1
      ;;
  esac
}

cd "$REMOTE_DIR"

if ! sudo docker ps --format '{{.Names}}' | grep -qx "$APP_CONTAINER"; then
  compose_container="$(sudo docker compose ps -q app 2>/dev/null || true)"
  if [ -n "$compose_container" ]; then
    APP_CONTAINER="$compose_container"
  else
    echo "Không tìm thấy container app đang chạy." >&2
    sudo docker compose ps || true
    exit 1
  fi
fi

echo '--- git pull ---'
before="$(git rev-parse HEAD)"
git pull origin "$BRANCH"
after="$(git rev-parse HEAD)"

if [ -n "$PATCH_REF" ]; then
  diff_target="$PATCH_REF"
elif [ "$before" != "$after" ]; then
  diff_target="$before..$after"
else
  diff_target="HEAD~1..HEAD"
fi

echo "--- hot patch diff: ${diff_target} ---"
mapfile -t changed_records < <(git diff --name-status --find-renames "$diff_target" || true)

if [ "${#changed_records[@]}" -eq 0 ]; then
  echo 'Không có file thay đổi để hot patch.'
  exit 0
fi

patch_files=()
skipped_files=()
deleted_files=()

for record in "${changed_records[@]}"; do
  status="${record%%$'\t'*}"
  rest="${record#*$'\t'}"
  file="$rest"

  if [[ "$status" == R* ]]; then
    old_file="${rest%%$'\t'*}"
    file="${rest#*$'\t'}"
    if is_patchable_path "$old_file"; then
      deleted_files+=("$old_file")
    fi
  fi

  if [ "$status" = "D" ]; then
    if is_patchable_path "$file"; then
      deleted_files+=("$file")
    else
      skipped_files+=("$status $file")
    fi
    continue
  fi

  if [ ! -f "$file" ]; then
    skipped_files+=("$status $file")
    continue
  fi

  if is_patchable_path "$file"; then
    patch_files+=("$file")
  else
    skipped_files+=("$status $file")
  fi
done

if [ "${#patch_files[@]}" -eq 0 ] && [ "${#deleted_files[@]}" -eq 0 ]; then
  echo 'Không có file runtime an toàn để hot patch.'
else
  echo '--- docker cp changed files ---'
  for file in "${patch_files[@]}"; do
    target_dir="/var/www/html/$(dirname "$file")"
    echo "copy: $file"
    sudo docker exec "$APP_CONTAINER" mkdir -p "$target_dir"
    sudo docker cp "$file" "$APP_CONTAINER:/var/www/html/$file"
    sudo docker exec "$APP_CONTAINER" chown docker:staff "/var/www/html/$file" 2>/dev/null || true
  done

  for file in "${deleted_files[@]}"; do
    echo "delete: $file"
    sudo docker exec "$APP_CONTAINER" rm -f "/var/www/html/$file"
  done

  echo '--- run pending migrations ---'
  sudo docker exec "$APP_CONTAINER" php artisan migrate --force

  echo '--- clear Laravel caches ---'
  sudo docker exec "$APP_CONTAINER" php artisan optimize:clear

  echo '--- reload Apache to refresh loaded PHP/opcache state ---'
  sudo docker exec "$APP_CONTAINER" apache2ctl graceful 2>/dev/null || true

  echo '--- post-deploy health check ---'
  sleep 2
  final_url="$(curl -s -o /dev/null -w '%{url_effective}' -L http://localhost/ || echo 'CURL_FAILED')"
  http_code="$(curl -s -o /dev/null -w '%{http_code}' -L http://localhost/ || echo '000')"
  echo "trang chu: ${http_code} -> ${final_url}"
  if [ "$http_code" != '200' ]; then
    echo ''
    echo "!!! CANH BAO: trang chu tra ve HTTP ${http_code}, khong phai 200 - kiem tra container/log ngay."
    echo ''
  fi
  secrets_exists='no'
  sudo docker exec "$APP_CONTAINER" test -f /var/lib/hsbit/secrets/database.php 2>/dev/null && secrets_exists='yes'
  if echo "$final_url" | grep -q '/setup' && [ "$secrets_exists" = 'yes' ]; then
    echo ''
    echo '!!! CANH BAO: trang chu dang redirect ra /setup nhung file cau hinh DB (secrets/database.php) van ton tai tren volume.'
    echo '!!! Rat co the deploy nay khien app mat ket noi/tro sai database da cau hinh truoc do (giong su co 2026-08-22). Kiem tra ngay, dung vao setup ma chua ro nguyen nhan.'
    echo ''
  fi
fi

if [ "${#skipped_files[@]}" -gt 0 ]; then
  echo ''
  echo '--- skipped files (use full deploy if these matter) ---'
  printf '%s\n' "${skipped_files[@]}"
fi

echo '--- status ---'
sudo docker compose ps
REMOTE_SCRIPT
)"

  gcloud compute ssh "${VM_NAME}" --zone="${VM_ZONE}" --quiet --command="echo '${REMOTE_SCRIPT_B64}' | base64 -d | bash -s -- '${PATCH_REF}' '${APP_CONTAINER}' '${BRANCH}' '${REMOTE_DIR}'"
fi

echo ""
echo "==> Done. Check http://34.142.200.14/"
