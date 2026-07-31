# Implementation notes

_Auto-appended by Claude Code PostToolUse hook. Claude also adds human-readable summaries below file-change lines._

- 2026-07-31 09:47:40 | Edit | docker-compose.yml
- 2026-07-31 09:47:40 | Edit | Dockerfile
- 2026-07-31 09:47:41 | Edit | .env.docker
- 2026-07-31 09:47:54 | Edit | docker/docker.env
- 2026-07-31 09:47:55 | Edit | .env.example
- 2026-07-31 09:48:01 | Edit | resources/views/layouts/default.blade.php
- 2026-07-31 09:48:03 | Edit | resources/views/layouts/default.blade.php

## 2026-07-31 09:50 — Rebrand Docker image + footer cho AMS Hbt
- **Why:** Build lại image snipe/snipe-it thành image riêng, đổi tên hiển thị và bỏ credit/link hỗ trợ của dự án gốc trong footer.
- **What:**
  - `docker-compose.yml`: đổi `image: snipe/snipe-it:${APP_VERSION:-latest}` → `hieubt/ams-hbt:${APP_VERSION:-latest}`.
  - `Dockerfile`: đổi `LABEL maintainer` sang HieuBT.
  - `.env.docker`, `docker/docker.env`, `.env.example`: thêm `SITE_NAME="AMS Hbt"`.
  - `resources/views/layouts/default.blade.php`: xóa dòng credit "Snipe-IT is open source... by Grokability" + 4 icon mạng xã hội (Bluesky/Github/Mastodon/Discord); đổi link "Bug report" từ `https://snipeitapp.com/support/` sang `mailto:hieubt@hsb.edu.vn`.
  - Logo, favicon, tên site hiển thị đầy đủ không cần sửa code — cấu hình qua Admin > Settings > Branding (site_name, logo, email logo, label logo, PDF logo đều lưu trong DB/storage, sống sót qua update code).
- **Verify:** `docker compose build` rồi `docker compose up -d`, kiểm tra footer không còn credit/social icon, link Bug report trỏ mailto, và title/email dùng tên "AMS Hbt". Rollback: `git checkout -- docker-compose.yml Dockerfile .env.docker .env.example docker/docker.env resources/views/layouts/default.blade.php`.
- 2026-07-31 09:48:37 | Edit | implementation-notes.md
