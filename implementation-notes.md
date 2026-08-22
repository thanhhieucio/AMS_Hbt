# Implementation Notes

File này ghi lại các thay đổi đáng chú ý trong quá trình cá nhân hóa và Việt hóa HSB-IT. Nội dung được lưu bằng UTF-8 không BOM để tránh lỗi tiếng Việt thành dấu hỏi hoặc mojibake.

## 2026-08-21 - Việt hóa tài liệu Markdown HSB-IT

- **Why:** User yêu cầu dịch tiếp toàn bộ file Markdown và chuẩn hóa tiếng Việt Unicode.
- **Intent:** Việt hóa các tài liệu `.md` ngoài `vendor/node_modules/storage`, giữ nguyên lệnh/code block/đường dẫn kỹ thuật, sửa sạch mojibake.
- **Touched surface:** `README.md`, `TESTING.md`, `SECURITY.md`, `CONTRIBUTING.md`, `CODE_OF_CONDUCT.md`, `CONTRIBUTORS.md`, `CLAUDE.md`, `docker/README.md`, `.screenshotter/README.md`, `implementation-notes.md`.
- **Risks:** Dịch tài liệu rộng có thể làm lệch một số câu hướng dẫn upstream; cần giữ nguyên command, env key và code block để không ảnh hưởng vận hành.
- **Rollback/verify:** Rollback bằng git diff/checkout các file Markdown. Verify bằng quét các mẫu mojibake phổ biến, đọc lại heading/link/code block và kiểm tra UTF-8.

## 2026-08-21 - Rebrand tên cũ sang HSB-IT

- **Why:** User yêu cầu cá nhân hóa dự án, đổi tên app/caption/đường dẫn/biến liên quan từ thương hiệu cũ sang HSB-IT.
- **Intent:** Đổi bề mặt người dùng và định danh kỹ thuật liên quan sang HSB-IT: tên site, env mặc định, mail/PDF metadata, setup wizard, email template, command Artisan, class/helper/policy, JS global, CSS class, Docker path và README.
- **Touched surface:** `.env*`, `app.json`, `composer.json`, `composer.lock`, `config/*`, `routes/*`, `app/Console/Commands/*`, model/service/provider/policy đổi tên sang `Hsb*`, `resources/views/*`, `resources/lang/*`, `resources/assets/js/*`, `public/css/*`, `public/js/*`, Docker/deploy docs và logo demo.
- **Risks:** Thay đổi rất rộng; Docker volume/deploy path đổi từ tên cũ sang `hsbit`/`hsb-it`, nên production cũ cần kế hoạch migrate volume nếu giữ dữ liệu. Prefix custom field đổi sang `_hsbit_`; dữ liệu lịch sử dùng prefix cũ cần migration riêng nếu còn cần tương thích.
- **Rollback/verify:** Verify đã chạy: `npm run production`, `php artisan config:clear`, `composer validate --no-check-publish`, `php -l app/Console/Commands`, `php artisan list --raw`, quét tên thương hiệu cũ ngoài vendor/node_modules/storage/bootstrap cache.

## 2026-08-21 - Việt hóa giao diện HSB-IT

- **Why:** User yêu cầu dịch và Việt hóa toàn bộ caption/text/phần hiển thị cho người dùng.
- **Intent:** Đặt `vi-VN` làm ngôn ngữ mặc định; sửa các chuỗi setup, command description, prompt CLI, metadata app và file ngôn ngữ để giảm fallback tiếng Anh.
- **Touched surface:** `.env`, `.env.example`, `.env.docker`, `docker/docker.env`, `config/app.php`, `app.json`, `routes/console.php`, `app/Console/Commands/*`, `resources/views/setup/*`, `resources/lang/vi-VN/**`, `resources/lang/en-US/**` và các file view/email liên quan.
- **Risks:** Một số test snapshot có thể kỳ vọng tiếng Anh; console Windows có thể hiển thị dấu tiếng Việt thành `?` do code page dù file đã là UTF-8.
- **Rollback/verify:** Verify bằng `php artisan config:clear`, `php artisan list --raw`, lint PHP các command và quét lỗi encoding trong file nguồn.

## 2026-08-21 - Deploy HSB-IT lên VM production

- **Why:** Deploy bản rebrand HSB-IT lên VM `hieubt-hsb-ams-server` tại IP `34.142.200.14`.
- **What:** Clone repo vào `/opt/hsb-it`, tạo `.env` production mới, build image `hieubt/hsb-it:latest`, chạy `docker compose up -d` với app và database mới. Dữ liệu test cũ đã được user đồng ý xóa.
- **Verify:** `curl http://34.142.200.14/` trả 302 sang `/setup`; database mới cần hoàn tất setup wizard để tạo admin đầu tiên.
- **Risks:** Chưa có domain/HTTPS và SMTP thật; mail đang dùng log nếu chưa cấu hình lại.

## 2026-08-21 - Đổi logo mặc định trang setup

- **Why:** User yêu cầu thay logo setup thành HSB-IT và email liên hệ `hieubt@hsb.edu.vn`.
- **What:** Ghi đè `public/img/logo.png` bằng logo HSB-IT; logo trong giao diện chính vẫn cấu hình qua Admin > Settings > Branding.
- **Verify:** Mở `/setup` sau deploy để kiểm tra logo mới.

## 2026-08-21 - Redeploy logo và bỏ workflow lỗi

- **Why:** Đưa logo mới lên production và xử lý GitHub Actions lỗi do action bên thứ ba không còn resolve được.
- **What:** Sửa `deploy.sh` để không prune image build cache sau deploy; xóa workflow ethical check kế thừa không liên quan.
- **Verify:** Redeploy thành công, app vẫn redirect về `/setup`.

## 2026-08-21 - Dọn rebrand còn sót

- **Why:** Rà soát sau rebrand phát hiện script screenshot vẫn dùng selector/domain cũ.
- **What:** Đổi `.screenshotter/src/screenshotter.mjs` sang selector `hsb-table`, domain test sang `hsb-it.test`; xóa các file cấu hình contributor/code owner gốc không còn phù hợp.
- **Verify:** Quét tên cũ ngoài vendor/node_modules không còn match ở bề mặt app.

## 2026-08-11 - Merge quy định bắt buộc từ NexGym

- **Why:** User yêu cầu mang các quy định bắt buộc từ dự án NexGym sang tài liệu hướng dẫn agent của HSB-IT.
- **What:** Cập nhật `CLAUDE.md` với quy định về response style, implementation notes, naming, ngôn ngữ hiển thị, auth, CSS, phím tắt, tiêu chí hoàn tất và chống lỗi encoding tiếng Việt.
- **Risks:** Một số quy định từ NexGym không khớp hoàn toàn với Laravel/PHP của HSB-IT, nên `CLAUDE.md` đã được chuẩn hóa lại để nêu rõ cách áp dụng phù hợp với repo này.

## 2026-07-31 - Rebrand Docker image và footer cho HSB-IT

- **Why:** Đổi image Docker, tên hiển thị và footer sang bản cá nhân hóa HSB-IT.
- **What:** Cập nhật Dockerfile/docker-compose/env mẫu, footer layout, link báo lỗi và cấu hình site name.
- **Verify:** Build/chạy Docker Compose, kiểm tra footer không còn credit/social link gốc và site hiển thị HSB-IT.- 2026-08-21 18:13:10 | Write | C:/Users/HieuBT/AppData/Local/Temp/claude/D--Dev-AMS-hbt/c61cac47-99a6-4cc4-aeac-15b972d2391f/scratchpad/render_setup_test.php
- 2026-08-21 18:13:15 | Edit | C:/Users/HieuBT/AppData/Local/Temp/claude/D--Dev-AMS-hbt/c61cac47-99a6-4cc4-aeac-15b972d2391f/scratchpad/render_setup_test.php
- 2026-08-21 18:13:37 | Write | _render_setup_test.php
- 2026-08-21 18:13:50 | Edit | _render_setup_test.php

## 2026-08-21 - Thêm form cấu hình Database ở bước 1 setup wizard (hỗ trợ Cloud SQL for PostgreSQL)

- **Why:** User muốn đổi database sang Cloud SQL for PostgreSQL. Firebase (Firestore/Realtime DB) là NoSQL, không tương thích Eloquent nên đã tư vấn dùng Cloud SQL for PostgreSQL (nói chuẩn giao thức PostgreSQL, driver `pgsql` đã có sẵn trong Laravel). Setup wizard trước đó **không có** form cấu hình DB — Step 1 chỉ tự kiểm tra kết nối dựa trên `.env` có sẵn, người dùng phải tự sửa file bằng tay.
- **What:**
  - `config/database.php`: thêm `sslmode` (env `DB_SSLMODE`, mặc định `prefer`) vào connection `pgsql` — cần `require` khi nối Cloud SQL qua IP công khai, để `prefer`/bỏ trống khi qua Cloud SQL Auth Proxy trên localhost.
  - `.env.example`: ghi chú `DB_CONNECTION` hỗ trợ `mysql`/`pgsql`, thêm biến `DB_SSLMODE`.
  - `app/Http/Requests/SetupDatabaseRequest.php` (mới): validate `db_connection` (mysql|pgsql), host, port, database, username, password (nullable), sslmode (checkbox). `errorBag = 'database'` để tách khỏi lỗi validate của các form khác trên cùng trang; `dontFlash = ['db_password']` để không lưu mật khẩu vào session khi validate lỗi.
  - `app/Http/Controllers/SetupController.php`: thêm `postSaveDatabase()` — test kết nối bằng PDO thô (không đụng connection Laravel đang chạy) trước khi ghi `.env`, tránh làm hỏng `.env` với thông tin sai. Nếu để trống ô password, giữ nguyên password cũ trong `.env` thay vì ghi đè rỗng. Sau khi ghi `.env` thành công thì gọi `Artisan::call('config:clear')` rồi redirect về `route('setup')` — request mới sẽ đọc lại `.env` từ đĩa.
  - `routes/web.php`: thêm `POST setup/database` → `setup.database.save` (đã nằm trong route group `prefix('setup')` nên tự động được `CheckForSetup` middleware chặn sau khi cài đặt xong, không cần thêm auth riêng).
  - `resources/views/setup/index.blade.php`: thêm panel "Cấu hình kết nối Database" (collapse, tự mở khi `db_conn=false` hoặc có lỗi bag `database`) với select driver (MySQL/PostgreSQL), host/port/database/username/password, checkbox "Bắt buộc SSL". JS đổi port mặc định 3306↔5432 theo driver và ẩn/hiện checkbox SSL (chỉ áp dụng pgsql).
  - `resources/views/blade/form/error.blade.php`: thêm prop `bag` tùy chọn (mặc định `'default'`) để component `<x-form.error>` hiển thị được lỗi từ bag `database` — không phá các nơi gọi cũ vì họ không truyền `bag`.
  - `resources/lang/vi-VN/general.php`, `resources/lang/en-US/general.php`: thêm key `setup_db_saved`.
- **Risks:** `.env` được ghi trực tiếp bằng regex theo dòng (không dùng thư viện), giả định file `.env` tồn tại và mỗi biến chỉ xuất hiện một dòng dạng `KEY=value`; nếu `.env` có định dạng bất thường (nhiều dòng trùng key, multiline value) có thể ghi sai. Việc đổi `.env` chỉ có hiệu lực ở **request kế tiếp** (PHP-FPM/Herd đọc lại `.env` mỗi request mới) — nếu app chạy dưới Octane hoặc worker giữ process lâu dài, cần restart worker thủ công vì `env()` đã cache trong process. Form không giới hạn bằng quyền admin vì tại bước 1 chưa có user nào — an toàn vì `CheckForSetup` middleware đã chặn toàn bộ `/setup*` một khi `Setting::setupCompleted()` trả `true`.
- **Verify:** `php -l` sạch cho 4 file PHP đã sửa/thêm; `php artisan route:list --name=setup` thấy `setup.database.save`; render `resources/views/setup/index.blade.php` trực tiếp qua bootstrap Laravel (không qua HTTP, có share `$errors` giả lập) ra HTML hợp lệ và chứa panel mới. Chưa test được qua trình duyệt thật (dev server Herd không chạy ở `127.0.0.1:8001` lúc thao tác) — cần mở `/setup` thủ công, chọn PostgreSQL, nhập thông tin Cloud SQL thật và bấm "Kiểm tra & lưu cấu hình database" để xác nhận round-trip ghi `.env` + redirect hoạt động đúng trên môi trường thật trước khi coi là xong.
- 2026-08-21 18:14:53 | Edit | implementation-notes.md
- 2026-08-21 18:25:42 | Edit | config/app.php

## 2026-08-21 - Sửa trang setup production còn tiếng Anh/Snipe-IT

- **Why:** User báo `http://34.142.200.14/setup` vẫn hiển thị tiếng Anh và footer `Snipe-IT Version v8.7.0`.
- **Intent:** Chuẩn hóa riêng setup wizard sang tiếng Việt Unicode, bảo đảm fallback `en-US` của setup cũng trả tiếng Việt, đổi default locale admin đầu tiên về `vi-VN`, rồi kiểm tra/redeploy production để xóa cache/source cũ.
- **Touched surface:** `resources/views/setup/*`, `resources/views/layouts/setup.blade.php`, `resources/lang/en-US/general.php`, `app/Http/Controllers/SetupController.php`, production deploy/cache.
- **Risks:** Đổi một phần key trong `en-US` sang tiếng Việt là cố ý để setup vẫn Việt hóa nếu production `.env` còn locale cũ; không tác động business logic ngoài wizard cài đặt.
- **Rollback/verify:** Rollback bằng git diff theo các file trên. Verify bằng lint PHP, quét chuỗi `Snipe-IT Version`/setup tiếng Anh còn sót, deploy và `curl http://34.142.200.14/setup` xác nhận `lang=vi-VN`, text tiếng Việt, footer HSB-IT.
- 2026-08-21 18:39:00 | Deploy note | Production VM source path is /opt/ams-hbt; deploy.sh now pulls there and tags the rebuilt image for both hieubt/hsb-it and legacy hieubt/ams-hbt compose compatibility.

## 2026-08-21 - Gắn Firebase CLI project hbt-software

- **Why:** User chọn Firebase project `hbt-software` sau khi đăng nhập Firebase CLI bằng tài khoản mới.
- **Intent:** Tạo cấu hình Firebase tối thiểu cho workspace để CLI nhận project mặc định là `hbt-software`, chưa bật deploy service nào.
- **Touched surface:** `.firebaserc`, `firebase.json`.
- **Risks:** `firebase.json` rỗng không triển khai hosting/functions/rules; cần cấu hình thêm trước khi deploy Firebase service cụ thể.
- **Rollback/verify:** Xóa `.firebaserc` và `firebase.json` nếu không dùng Firebase trong repo. Verify bằng `firebase use`.- 2026-08-21 21:09:14 | Write | C:/Users/HieuBT/AppData/Local/Temp/claude/D--Dev-AMS-hbt/c61cac47-99a6-4cc4-aeac-15b972d2391f/scratchpad/nosql-migration-audit.html
- 2026-08-21 21:18:36 | Edit | app/Http/Controllers/SetupController.php
- 2026-08-21 21:18:49 | Edit | app/Http/Controllers/SetupController.php
- 2026-08-21 21:19:04 | Edit | routes/web.php
- 2026-08-21 21:19:23 | Edit | resources/views/setup/index.blade.php
- 2026-08-21 21:19:27 | Edit | resources/views/setup/index.blade.php
- 2026-08-21 21:19:33 | Edit | resources/views/blade/form/error.blade.php
- 2026-08-21 21:19:38 | Edit | config/database.php
- 2026-08-21 21:19:50 | Edit | .env.example
- 2026-08-21 21:20:04 | Edit | resources/lang/vi-VN/general.php
- 2026-08-21 21:20:07 | Edit | resources/lang/en-US/general.php

## 2026-08-22 - Chuẩn hóa DB_DATABASE mặc định cho setup

- **Why:** User muốn phần cấu hình cơ sở dữ liệu trong setup mặc định dùng `DB_DATABASE=hsb_it`, nhưng người cài đặt vẫn nhập được tên database mới và hệ thống lưu ngược lại vào `.env`.
- **Intent:** Đặt `hsb_it` làm tên database mặc định/fallback ở env mẫu, Docker env và form setup; giữ input database là field editable và `postSaveDatabase()` tiếp tục ghi giá trị người dùng nhập vào `DB_DATABASE`.
- **Touched surface:** `.env`, `.env.example`, `.env.docker`, `docker/docker.env`, `config/database.php`, `resources/views/setup/index.blade.php`, `implementation-notes.md`.
- **Risks:** Đổi default không tự rename database production đang có; nếu muốn chuyển database thật từ `snipeit` sang `hsb_it` cần tạo/migrate database trên MariaDB hoặc cài mới volume DB.
- **Rollback/verify:** Rollback bằng git diff các file trên. Verify bằng quét `DB_DATABASE`, lint/config clear, và kiểm tra `/setup` hiển thị `hsb_it` trong ô tên cơ sở dữ liệu nhưng vẫn submit được tên khác.
## 2026-08-22 - Lưu cấu hình database vào file secret

- **Why:** User muốn các biến bootstrap database (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) không bị ghi tiếp vào `.env` sau bước setup để giảm nguy cơ lộ thông tin nhạy cảm.
- **Intent:** Cho Laravel đọc cấu hình database từ file PHP secret trước, fallback về `.env` khi chưa có file; setup wizard test kết nối rồi ghi cấu hình DB vào file secret ngoài web root.
- **Touched surface:** `config/database.php`, `app/Http/Controllers/SetupController.php`, `resources/views/setup/index.blade.php`, `.env*`, Docker entrypoint/Dockerfile, `.gitignore`, `implementation-notes.md`.
- **Risks:** File secret phải ghi được bởi web user/container; nếu mất file secret và `.env` không còn DB fallback thì app không kết nối được DB. Docker Compose nội bộ vẫn có thể cần biến DB ở host `.env` để khởi tạo service MariaDB.
- **Rollback/verify:** Rollback bằng git diff các file trên. Verify bằng `php -l`, `php artisan config:clear`, kiểm tra setup hiển thị đường dẫn file secret và submit DB ghi được file cấu hình.

## 2026-08-22 - Thêm lựa chọn hot patch vào deploy.sh

- **Why:** User muốn có cách đẩy nhanh các file code nhỏ lên server/container mà không phải build lại Docker mỗi lần.
- **Intent:** Giữ deploy chuẩn build Docker như cũ, bổ sung mode hot patch để `git pull`, copy file đã thay đổi vào container đang chạy và clear cache Laravel.
- **Touched surface:** `deploy.sh`, `implementation-notes.md`.
- **Risks:** Hot patch chỉ sửa container đang chạy, không cập nhật Docker image; nếu container bị recreate thì cần deploy chuẩn hoặc hot patch lại. Không phù hợp cho thay đổi Dockerfile, composer/npm dependency, migration cần build/runtime package mới.
- **Rollback/verify:** Rollback bằng git diff `deploy.sh`. Verify bằng `bash -n deploy.sh` nếu có bash và đọc lại command SSH/docker cp sinh ra.

## 2026-08-22 - S?a SSH hot deploy kh�ng tranh stdin
- Intent: ngan gcloud compute ssh h?i x�c nh?n tuong t�c khi hot patch truy?n script qua stdin.
- Touched surface: deploy.sh, c�c l?nh gcloud compute ssh c?a ch? d? full v� hot.
- Risk: --quiet b? qua prompt x�c nh?n c?a gcloud; kh�ng thay d?i l?nh build/copy t? xa.
- Rollback: kh�i ph?c hai l?nh gcloud compute ssh v? d?ng kh�ng c� --quiet.
- Verification: bash -n deploy.sh v� deploy.sh --help.

## 2026-08-22 - T? d?ng commit/push tru?c khi deploy
- Intent: gom commit, push GitHub v� deploy l�n Google Compute v�o m?t l?n ch?y deploy.sh.
- Touched surface: deploy.sh; t? d?ng stage to�n b? thay d?i trong repository tru?c khi deploy.
- Risk: git add -A s? dua c? file untracked v�o commit; c?n ki?m tra .gitignore tru?c khi ch?y production.
- Rollback: d�ng git revert commit deploy n?u c?n; c� th? d?t AUTO_GIT_SYNC=0 d? t?t bu?c t? d?ng.
- Verification: bash -n deploy.sh, --help v� ki?m tra diff.
