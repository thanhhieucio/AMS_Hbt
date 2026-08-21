#!/usr/bin/env bash
# Deploy the current `master` branch (as pushed to GitHub) to the production VM.
# Usage: ./deploy.sh
set -euo pipefail

VM_NAME="hieubt-hsb-ams-server"
VM_ZONE="asia-southeast1-b"
REMOTE_DIR="/opt/ams-hbt"

echo "==> Deploying AMS Hbt to ${VM_NAME} (${VM_ZONE})"
echo "==> Make sure your changes are committed AND pushed to GitHub (origin/master) first."
echo ""

gcloud compute ssh "${VM_NAME}" --zone="${VM_ZONE}" --command="
  set -e
  cd ${REMOTE_DIR}
  echo '--- git pull ---'
  git pull origin master
  echo '--- docker build (uses layer cache, but any file change re-runs composer install) ---'
  sudo docker build -t hieubt/ams-hbt:latest .
  echo '--- docker compose up -d ---'
  sudo docker compose up -d
  echo '--- status ---'
  sudo docker compose ps
"

echo ""
echo "==> Done. Check http://34.142.200.14/"
