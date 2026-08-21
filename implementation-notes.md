# Implementation notes

_Auto-appended by Claude Code PostToolUse hook. Claude also adds human-readable summaries below file-change lines._
## 2026-08-21 16:xx - Viet hoa giao dien AMS Hbt
- **Why:** User yeu cau dich va Viet hoa toan bo caption/text/phan hien thi cho nguoi dung.
- **Intent:** Dat tieng Viet (`vi-VN`) lam ngon ngu mac dinh cho app/setup/docker/env mau; sua goi `resources/lang/vi-VN` de chuoi co dau hien thi dung UTF-8 va giam fallback sang tieng Anh.
- **Touched surface:** `implementation-notes.md`, `.env`, `.env.example`, `.env.docker`, `config/app.php`, `routes/console.php`, `app/Console/Commands/ResetDemoSettings.php`, `resources/views/setup/user.blade.php`, va cac file dich `resources/lang/vi-VN/**`.
- **Risks:** Translation thay doi rong co the lam mot so copy khong sat nghiep vu hoac test snapshot ky vong tieng Anh bi lech; `.env` hien tai doi locale nen can clear config cache tren moi truong da cache.
- **Rollback/verify:** Rollback bang git diff/checkout cac file tren. Verify bang `php artisan test --filter SnipeTranslatorTest` hoac toi thieu `php artisan config:clear && php artisan route:clear`, mo UI kiem tra setup/login/sidebar/table khong con chu loi encoding.

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
- 2026-08-11 14:34:58 | Edit | .env
- 2026-08-11 14:35:02 | Edit | .env
- 2026-08-11 14:38:19 | Write | storage/logs/socktest.php
- 2026-08-11 14:40:51 | Edit | .env
- 2026-08-11 14:58:19 | Write | C:/Users/HieuBT/AppData/Local/Temp/claude/D--Dev-AMS-Hbt/140e2b4a-2e87-4a37-a82a-0340cbfaf653/scratchpad/ams-hbt-architecture.html
- 2026-08-11 15:24:06 | Write | C:/Users/HieuBT/AppData/Local/Temp/claude/D--Dev-AMS-Hbt/140e2b4a-2e87-4a37-a82a-0340cbfaf653/scratchpad/ams-hbt-roadmap.html
- 2026-08-11 15:38:14 | Edit | CLAUDE.md

## 2026-08-11 15:38 — Merge quy định bắt buộc từ nexgym/CLAUDE.md
- **Why:** User yêu cầu mang toàn bộ quy định bắt buộc từ `D:\Dev\nexgym\CLAUDE.md` sang CLAUDE.md của AMS_Hbt, kể cả các mục xung đột với convention Laravel/PHP hiện có.
- **What:**
  - `CLAUDE.md`: thêm section mới "Quy định bắt buộc (mang từ D:\Dev\nexgym\CLAUDE.md)" ở cuối file — gồm Response Style, Implementation Notes Requirement, mục 1-8 (Vai trò, Quy tắc kỹ thuật, Naming, Ngôn ngữ hiển thị, Auth, CSS, phím tắt F6, Definition of Done, chống lỗi encoding tiếng Việt).
  - Đã gắn cảnh báo ⚠️ inline tại các mục 3/4/5/6/6.1 vì mâu thuẫn trực tiếp với thực tế codebase (naming tiếng Anh/PSR, translation key en-US, bảng `users` chuẩn Laravel không phải `auth_user`, không có `app/globals.css`, không có React).
  - Chủ động **bỏ qua** 2 phần không phải "quy định": các dòng `@CONTEXT.md`/`@AGENTS.md`/`@UI_RULES.md`/`@DB_RULES_MONGODB` ở đầu file gốc (import file không tồn tại trong AMS_Hbt) và mục "Dữ liệu seed" cuối file gốc (dữ liệu payroll mẫu cụ thể của NexGym, không phải quy tắc, không có ý nghĩa với hệ quản lý tài sản).
- **Verify:** Đọc lại `CLAUDE.md` xác nhận section mới nằm sau "## Testing", còn nguyên nội dung Stack/Architecture cũ. Rollback: `git checkout -- CLAUDE.md` (nếu đã commit trước đó) hoặc xoá thủ công section vừa thêm.
- 2026-08-11 15:38:37 | Edit | implementation-notes.md
- 2026-08-21 15:20:29 | Write | C:/Users/HieuBT/AppData/Local/Temp/claude/D--Dev-AMS-hbt/96490915-12ad-4042-a726-9a667946bfa0/scratchpad/prod.env

## 2026-08-21 15:50 — Deploy AMS Hbt lên hieubt-hsb-ams-server (GCE VM)
- **Why:** Yêu cầu deploy bản rebrand AMS Hbt (commit f6d5c57a68) lên production VM `hieubt-hsb-ams-server` (asia-southeast1-b, IP 34.142.200.14).
- **What:**
  - Phát hiện VM đã có deployment cũ chạy 3 tuần (`snipe/snipe-it:latest` + `mysql:8.0`, chưa rebrand) — khác với thông tin "lần đầu setup" ban đầu. Đã xác nhận với user và được đồng ý xóa bỏ (dữ liệu cũ chỉ là test data).
  - Dừng + xóa container `hieubt-hsb-ams-app`, `hieubt-hsb-ams-mysql` và 2 volume liên quan (`hieubt-hsb-ams_hsb-ams-vol`, `hieubt-hsb-ams_hsb-ams-db-data`).
  - Clone repo (`https://github.com/thanhhieucio/AMS_Hbt.git`, public) vào `/opt/ams-hbt` trên VM.
  - Tạo `.env` production mới: APP_KEY/DB_PASSWORD/MYSQL_ROOT_PASSWORD random mới (không tái dùng bất kỳ secret cũ nào), `APP_URL=http://34.142.200.14`, `APP_PORT=80`, `MAIL_MAILER=log` (chưa có SMTP thật), `APP_TIMEZONE=Asia/Ho_Chi_Minh`.
  - `docker-compose.yml` không có mục `build:` (chỉ có `image:`) nên `docker compose build` luôn no-op — phải dùng `docker build -t hieubt/ams-hbt:latest .` trực tiếp từ Dockerfile.
  - Build image trên chính VM (e2-small, 2GB RAM) mất ~10-15 phút do apt-get + composer install; phải chạy detached bằng `setsid` (nohup/`&` thường bị kill khi SSH session qua `gcloud compute ssh`/plink đóng).
  - `docker compose up -d` → 2 container mới: `ams-hbt-app-1` (image `hieubt/ams-hbt:latest`, port 80) + `ams-hbt-db-1` (mariadb:11.4.7, healthy). Container tự chạy `php artisan migrate --force` khi khởi động (theo `docker/startup.sh`).
- **Verify:** `curl http://34.142.200.14/` trả HTTP 302 redirect sang `/setup` (đúng vì DB mới, chưa có admin) — cần vào `http://34.142.200.14/setup` để tạo tài khoản admin đầu tiên. Log container: `sudo docker compose -f /opt/ams-hbt/docker-compose.yml logs app`. Rollback: `cd /opt/ams-hbt && sudo docker compose down` (dữ liệu cũ đã bị xóa vĩnh viễn, không còn để rollback về bản snipe-it gốc).
- **Việc còn thiếu / cần làm tiếp:** Chưa có domain + HTTPS (đang chạy HTTP qua IP). Chưa cấu hình SMTP thật (mail đang chỉ log ra file). Chưa hoàn tất setup wizard (`/setup`) để tạo admin đầu tiên.
- 2026-08-21 15:47:19 | Edit | implementation-notes.md
- 2026-08-21 15:50:28 | Write | deploy.sh
- 2026-08-21 15:57:24 | Write | C:/Users/HieuBT/AppData/Local/Temp/claude/D--Dev-AMS-hbt/96490915-12ad-4042-a726-9a667946bfa0/scratchpad/gen_logo.php

## 2026-08-21 16:00 — Đổi logo mặc định (trang /setup) sang HSB-IT
- **Why:** Yêu cầu sửa `logo.png` thành "HSB-IT" + email liên hệ `hieubt@hsb.edu.vn`, thay cho logo "SNIPE-IT / OPEN SOURCE ASSET MANAGEMENT" gốc.
- **What:**
  - Xác định `public/img/logo.png` (320×78, RGBA) chỉ được dùng ở `resources/views/layouts/setup.blade.php:83` — trang pre-flight/setup wizard (`/setup`). Logo trong giao diện chính (header/footer sau khi cài đặt) không nằm trong code, đã cấu hình qua Admin > Settings > Branding từ trước (xem entry 2026-07-31).
  - Không có PHP/ImageMagick trong PATH của shell — dùng PHP GD qua Herd (`C:\Users\HieuBT\.config\herd\bin\php84\php.exe`) với font `arialbd.ttf`/`arial.ttf` từ `C:\Windows\Fonts` để render text "HSB-IT" (đậm, đen) + "HIEUBT@HSB.EDU.VN" (nhỏ, xám) lên canvas 320×78 nền trong suốt, ghi đè `public/img/logo.png`.
- **Verify:** Đã xem lại ảnh PNG mới bằng Read tool trước khi ghi đè, xác nhận hiển thị đúng "HSB-IT" + email. Kiểm tra thực tế: sau khi deploy, mở `http://34.142.200.14/setup` xem logo mới. Rollback: `git checkout -- public/img/logo.png`.
- 2026-08-21 15:58:03 | Edit | implementation-notes.md
