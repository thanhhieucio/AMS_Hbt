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

## 2026-08-22 - Sửa SSH hot deploy không tranh stdin
- Intent: ngăn gcloud compute ssh hỏi xác nhận tương tác khi hot patch truyền script qua stdin.
- Touched surface: deploy.sh, các lệnh gcloud compute ssh của chế độ full và hot.
- Risk: --quiet bỏ qua prompt xác nhận của gcloud; không thay đổi lệnh build/copy từ xa.
- Rollback: khôi phục hai lệnh gcloud compute ssh về dạng không có --quiet.
- Verification: bash -n deploy.sh và deploy.sh --help.

## 2026-08-22 - Tự động commit/push trước khi deploy
- Intent: gom commit, push GitHub và deploy lên Google Compute vào một lần chạy deploy.sh.
- Touched surface: deploy.sh; tự động stage toàn bộ thay đổi trong repository trước khi deploy.
- Risk: git add -A sẽ đưa cả file untracked vào commit; cần kiểm tra .gitignore trước khi chạy production.
- Rollback: dùng git revert commit deploy nếu cần; có thể đặt AUTO_GIT_SYNC=0 để tắt bước tự động.
- Verification: bash -n deploy.sh, --help và kiểm tra diff.

## 2026-08-22 - Sửa deploy.sh bị lẫn line-ending gây lỗi khi hot patch qua SSH

- **Why:** User báo `bash deploy.sh hot` báo lỗi `bash: line 1: y: command not found` khi chạy trên VM production; log dừng ngay sau bước `git push` nên không bắt được dòng lỗi cụ thể từ phía remote.
- **What:**
  - Phát hiện `deploy.sh` bị lẫn line-ending: dòng 14 (`BRANCH=`), dòng 15 (`AUTO_GIT_SYNC=`) và dòng 235 (`printf` bên trong khối `REMOTE_SCRIPT` gửi qua SSH cho bash thật trên VM) có CRLF trong khi phần còn lại của file là LF — xác nhận bằng đếm byte `\r\n` trực tiếp (Git Bash trên Windows có thể âm thầm bỏ qua CR khi đọc script cục bộ nhưng bash thật trên VM Linux thì không).
  - Viết lại đoạn `printf '%s\n' "${skipped_files[@]}"` thành một dòng thay vì dựa vào newline literal nằm giữa chuỗi nháy đơn 2 dòng — cách viết cũ dễ vỡ nếu file bị lẫn line-ending trở lại.
  - Chuẩn hóa toàn bộ `deploy.sh` về LF thuần; thêm `*.sh text eol=lf` vào `.gitattributes` để git luôn checkout `.sh` bằng LF trên Windows, tránh tái diễn.
  - Sửa mojibake tiếng Việt còn sót ở 2 entry log ngày 2026-08-22 phía trên (do lần ghi trước bị sai encoding).
- **Risks:** Chưa xác nhận 100% CRLF là nguyên nhân trực tiếp của lỗi `y: command not found` vì không còn log đầy đủ từ phía remote; đây là sửa dựa trên bằng chứng cụ thể nhất tìm được (file thực sự bị lẫn line-ending ở đúng đoạn script chạy qua SSH) kết hợp làm cứng script để loại trừ khả năng này.
- **Verify:** `grep -cP '\r' deploy.sh` → 0; `file deploy.sh` không còn báo CRLF; `bash -n deploy.sh` syntax OK. Cần chạy lại `bash deploy.sh hot` trên máy thật; nếu vẫn lỗi, chạy `bash deploy.sh hot > deploy.log 2>&1` để lấy đầy đủ log (kể cả output từ VM) rồi rà tiếp.
- 2026-08-22 09:36:48 | Edit | .gitattributes
- 2026-08-22 09:36:51 | Edit | deploy.sh
- 2026-08-22 09:37:49 | Edit | implementation-notes.md
- 2026-08-22 10:09:08 | Edit | deploy.sh
- 2026-08-22 10:09:15 | Edit | deploy.sh

## 2026-08-22 - Bỏ heredoc-qua-stdin trong hot deploy, chuyển sang truyền script qua base64

- **Why:** Sau khi chuẩn hóa line-ending, chạy lại `bash deploy.sh hot` (từ PowerShell gọi `bash.exe`) vẫn báo `bash: line 1: y: command not found`, và hoàn toàn không thấy output nào từ phía remote script (`--- git pull ---`...) — chứng tỏ nội dung `REMOTE_SCRIPT` không hề tới được `bash -s` trên VM, tức lỗi nằm ở khâu forward stdin qua `gcloud compute ssh`, không phải nội dung script.
- **Intent:** Loại bỏ hoàn toàn việc dựa vào stdin để truyền script hot-patch. Khi chạy `bash.exe` từ PowerShell (không có pty thật) rồi gọi `gcloud` (Python) bọc `ssh.exe` (chương trình Windows gốc), luồng stdin bị forward qua nhiều lớp subprocess khác nhau trên Windows và không đáng tin cậy.
- **What:** `deploy.sh` chế độ hot: build `REMOTE_SCRIPT_B64` bằng `base64 -w0 <<'REMOTE_SCRIPT' ... REMOTE_SCRIPT` — heredoc này chỉ được `base64` cục bộ đọc qua command substitution, không đi qua gcloud/ssh nên không bị ảnh hưởng bởi vấn đề forward stdin. Sau đó gọi `gcloud compute ssh ... --command="echo '${REMOTE_SCRIPT_B64}' | base64 -d | bash -s -- '${PATCH_REF}' '${APP_CONTAINER}' '${BRANCH}' '${REMOTE_DIR}'"` — toàn bộ script được truyền như một tham số dòng lệnh (chuỗi base64 chỉ gồm ký tự an toàn, không cần escape), remote tự decode và chạy, không phụ thuộc SSH stdin nữa.
- **Risks:** Payload base64 của script hot-patch hiện ~4KB, an toàn so với giới hạn độ dài lệnh SSH/Windows (không có nguy cơ vượt giới hạn với script hiện tại; nếu script phình to hơn nhiều trong tương lai cần theo dõi lại). Yêu cầu remote VM có `base64` (coreutils chuẩn, có sẵn trên Debian/Ubuntu).
- **Verify:** `bash -n deploy.sh` syntax OK; test round-trip `base64 -w0 <<'X' ... X | base64 -d` cục bộ cho ra đúng nội dung gốc. Cần chạy lại `bash deploy.sh hot` trên máy thật để xác nhận remote thực sự nhận và chạy được script (thấy log `--- git pull ---`, `--- docker cp changed files ---`... xuất hiện).
- 2026-08-22 10:09:57 | Edit | implementation-notes.md
- 2026-08-22 10:28:51 | Edit | app/Http/Requests/SetupDatabaseRequest.php

## 2026-08-22 - Setup wizard tự tạo database/user MySQL thay vì bắt phải trùng giá trị .env

- **Why:** User phản hồi: giá trị Host/Tên đăng nhập/Mật khẩu nhập ở `/setup` phải là giá trị **khởi tạo** ra database, không phải giá trị phải tự đi tra `.env` trên VM rồi nhập lại cho khớp — luồng cũ (`testDatabaseConnection()` chỉ test PDO, không tạo gì) không đáp ứng đúng nhu cầu này.
- **What:**
  - `app/Http/Requests/SetupDatabaseRequest.php`: siết `db_database`/`db_username` chỉ nhận `[A-Za-z0-9_]` (max 64/32 ký tự) — bắt buộc để nhúng an toàn trực tiếp vào câu lệnh DDL `CREATE DATABASE`/`CREATE USER` phía dưới mà không cần escape identifier.
  - `app/Http/Controllers/SetupController.php`: thêm `provisionMysqlDatabase()` — khi `testDatabaseConnection()` thất bại và driver là `mysql`, tự kết nối bằng tài khoản `root` (lấy từ `env('MYSQL_ROOT_PASSWORD')`, biến này docker-compose đã nạp sẵn cho cả container `app` và `db` qua `env_file: .env`), chạy `CREATE DATABASE IF NOT EXISTS`, `CREATE USER IF NOT EXISTS` + `ALTER USER ... IDENTIFIED BY` (để cập nhật mật khẩu nếu user đã tồn tại nhưng mật khẩu khác), `GRANT ALL PRIVILEGES`, `FLUSH PRIVILEGES` — dùng đúng database/username/password người dùng vừa nhập trên form. Sau khi tạo xong, test lại kết nối bằng chính tài khoản mới trước khi lưu cấu hình.
  - `resources/views/setup/index.blade.php`: thêm ghi chú giải thích hành vi tự tạo database/user, gợi ý host `db` khi dùng chung docker-compose nội bộ, thêm `pattern="[A-Za-z0-9_]+"` + help text cho ô tên database/tên đăng nhập khớp với rule validate mới.
- **Risks:** Chỉ áp dụng cho driver `mysql` (MariaDB tự host qua docker-compose) — Cloud SQL Postgres là managed service, không tự provision theo cách này (phải tạo instance/database qua Google Cloud Console trước). Nếu `MYSQL_ROOT_PASSWORD` không có trong môi trường (ví dụ container app không cùng `env_file` với root password) thì tự tạo sẽ báo lỗi rõ ràng, không chặn được đường test PDO thông thường. `ALTER USER` sẽ ghi đè mật khẩu của user đã tồn tại nếu admin nhập mật khẩu khác — cần cẩn trọng khi dùng lại tên đăng nhập cũ trên môi trường production đang có dữ liệu thật.
- **Verify:** `php -l` sạch cho 2 file PHP đã sửa. Cần test thật trên `/setup`: nhập database/username/password hoàn toàn mới (chưa tồn tại) với host `db`, xác nhận hệ thống tự tạo và kết nối thành công thay vì báo lỗi "Access denied"/"Unknown database" như trước.

## 2026-08-22 - Bước 1: form Settings lưu thông số kết nối Firestore nguồn (chuẩn bị mở rộng sang quản lý tài sản chung)

- **Why:** User có kế hoạch mở rộng HSB-IT từ quản lý thiết bị IT sang quản lý tài sản chung cho trường đại học, và muốn lấy một số danh mục (trước mắt xác nhận có collection sinh viên) từ Firestore của một phần mềm khác cùng tổ chức. User yêu cầu làm từng bước: bước đầu tiên **chỉ lưu thông số kết nối** (Project ID, tên collection sinh viên, Service Account base64), chưa gọi Firestore, chưa thiết kế mapping/import — vì cấu trúc dữ liệu bên đó chưa khảo sát xong.
- **What:**
  - `config/services.php`: thêm block `firebase_source.config_file` (mặc định `storage/app/secrets/firebase-source.php`, override được qua `FIREBASE_SOURCE_CONFIG_FILE`) — chỉ trỏ đường dẫn, không chứa giá trị thật.
  - `app/Http/Requests/SettingsFirebaseSourceRequest.php` (mới): validate `firebase_project_id` (đúng định dạng Project ID Firebase), `firestore_students_collection` (ký tự an toàn cho collection id Firestore), `service_account_base64` (nullable — để trống thì giữ khóa cũ). `errorBag = 'firebase_source'`, `dontFlash = ['service_account_base64']`.
  - `app/Http/Controllers/SettingsController.php`: thêm `getFirebaseSourceSettings()` / `postFirebaseSourceSettings()` + helper `readFirebaseSourceConfig()` / `writeFirebaseSourceConfig()`. Khi lưu: giải mã base64 → JSON, kiểm tra có đủ `type=service_account`, `project_id`, `client_email`, `private_key` mới chấp nhận; sai thì báo lỗi rõ ràng thay vì lưu rác. Lưu vào file secret ngoài web root (cùng pattern với `HSBIT_DB_CONFIG_FILE` ở `SetupController`), **không** lưu vào bảng `settings` hay `.env` — vì Service Account là secret có quyền truy cập rộng hơn nhiều so với Client Secret Google/SAML mà app hiện lưu thẳng trong DB.
  - `routes/web.php`: thêm `GET/POST admin/settings/firebase-source` (`settings.firebase_source.index` / `.save`) trong group `prefix('admin')` + `middleware(['auth', 'authorize:superuser'])` sẵn có — chỉ superuser mới thấy/sửa được.
  - `resources/views/settings/firebase-source.blade.php` (mới): form 3 trường (Project ID, Collection sinh viên, Service Account base64 dạng textarea) + banner hiển thị `client_email` hiện tại nếu đã cấu hình (không hiển thị lại khóa).
  - `resources/views/settings/index.blade.php`: thêm tile "Nguồn dữ liệu Firebase" (icon `fa-fire`) trỏ vào trang mới.
  - `resources/lang/vi-VN/admin/settings/general.php`, `resources/lang/en-US/admin/settings/general.php`: thêm key `firebase_source`, `firebase_source_title`, `firebase_source_help`.
- **Risks:** Đây mới là bước lưu cấu hình — **chưa** có logic thực sự kết nối/đọc Firestore (chưa cài SDK `google/cloud-firestore`, chưa test gọi API thật), nên chưa biết Service Account có đủ quyền đọc đúng collection hay không cho tới bước sau. File secret `storage/app/secrets/firebase-source.php` đã nằm trong `.gitignore` sẵn (cùng thư mục với secret DB) nên không lọt vào git. Danh mục cụ thể cần đồng bộ (ngoài "students") chưa xác định — chờ khảo sát cấu trúc Firestore ở bước tiếp theo.
- **Verify:** `php -l` sạch cho 3 file PHP; `php artisan route:list --name=firebase` thấy đủ 2 route; render `settings.firebase-source` và `settings.index` qua bootstrap Laravel (không qua HTTP) ra HTML hợp lệ, chứa đủ 3 field và tile mới. Chưa test qua trình duyệt thật với phiên đăng nhập superuser — cần đăng nhập, vào Cài đặt, mở "Nguồn dữ liệu Firebase", thử lưu với chuỗi base64 hợp lệ/không hợp lệ để xác nhận thông báo lỗi hiển thị đúng errorBag.
- 2026-08-22 10:28:58 | Edit | app/Http/Controllers/SetupController.php
- 2026-08-22 10:29:09 | Edit | app/Http/Controllers/SetupController.php
- 2026-08-22 10:29:35 | Edit | resources/views/setup/index.blade.php
- 2026-08-22 10:29:41 | Edit | resources/views/setup/index.blade.php
- 2026-08-22 10:29:45 | Edit | resources/views/setup/index.blade.php
- 2026-08-22 10:29:49 | Edit | resources/views/setup/index.blade.php
- 2026-08-22 10:30:16 | Edit | implementation-notes.md
- 2026-08-22 10:48:29 | Edit | resources/lang/vi-VN/admin/settings/general.php
- 2026-08-22 10:48:39 | Edit | resources/lang/en-US/admin/settings/general.php
- 2026-08-22 10:48:44 | Edit | config/services.php
- 2026-08-22 10:48:50 | Write | app/Http/Requests/SettingsFirebaseSourceRequest.php
- 2026-08-22 10:48:57 | Edit | app/Http/Controllers/SettingsController.php
- 2026-08-22 10:49:00 | Edit | app/Http/Controllers/SettingsController.php
- 2026-08-22 10:49:14 | Edit | app/Http/Controllers/SettingsController.php
- 2026-08-22 10:49:24 | Edit | routes/web.php
- 2026-08-22 10:50:07 | Write | resources/views/settings/firebase-source.blade.php
- 2026-08-22 10:50:13 | Edit | resources/views/settings/index.blade.php
- 2026-08-22 10:51:01 | Write | C:/Users/HieuBT/AppData/Local/Temp/claude/D--Dev-AMS-hbt/c61cac47-99a6-4cc4-aeac-15b972d2391f/scratchpad/render_firebase_source_test.php
- 2026-08-22 10:51:07 | Write | _render_firebase_test.php
- 2026-08-22 10:51:36 | Write | _render_firebase_check.php
- 2026-08-22 10:52:11 | Edit | implementation-notes.md

## 2026-08-22 - Luu hu?ng d?n HTTPS cho m�y ch? Google Compute
- Intent: luu quy tr�nh c?p ch?ng ch? HTTPS cho HSB-IT v�o t�i li?u Markdown d? th?c hi?n sau.
- Touched surface: docs/huong-dan-cau-hinh-https.md.
- Risk: t�i li?u c� l?nh thao t�c firewall, DNS v� TLS; c?n thay t�n mi?n m?u b?ng t�n mi?n th?t tru?c khi ch?y.
- Rollback: ch? c?n x�a t�i li?u n?u kh�ng c�n s? d?ng; kh�ng c� thay d?i runtime.
- Verification: ki?m tra n?i dung Markdown v� du?ng d?n tham chi?u.

## 2026-08-22 - Ch?y local k?t n?i database production qua SSH tunnel
- Intent: cho ph�p ki?m th? code local v?i database tr�n VM m� kh�ng m? MariaDB ra Internet.
- Touched surface: docker-compose.yml, dev.remote-db.docker-compose.yml, .env.dev.remote-db.example, .gitignore, docs/chay-local-ket-noi-db-remote.md.
- Risk: local d�ng d? li?u th?t; tuy?t d?i kh�ng ch?y migration/reset/seed ph� d? li?u production. C?n full deploy m?t l?n d? bind MariaDB v�o loopback VM.
- Rollback: x�a override port loopback trong docker-compose.yml v� d?ng local compose; database v?n kh�ng public ra Internet.
- Verification: ki?m tra Compose config, file secret b? gitignore, v� hu?ng d?n tunnel.
- 2026-08-22 16:16:10 | Edit | app/Models/Setting.php
- 2026-08-22 16:16:17 | Edit | app/Models/Setting.php
- 2026-08-22 16:16:37 | Edit | app/Http/Controllers/SetupController.php
- 2026-08-22 16:16:45 | Edit | app/Http/Controllers/SetupController.php
- 2026-08-22 16:17:22 | Edit | deploy.sh

## 2026-08-22 16:20 — Vá lỗi "vào lại /setup dù đã cài xong" và khoá lỗ hổng liên quan
- **Why:** Sáng 22/08, sau khi hoàn tất setup wizard và tạo admin, truy cập lại trang chủ vẫn hiện màn hình `/setup` công khai — nguy hiểm vì ai cũng có thể tự tạo admin mới. Điều tra qua SSH vào VM production phát hiện 2 lỗi gốc và 1 sự cố phát sinh khi vá tay.
- **Nguyên nhân gốc (đã xác minh trực tiếp trên VM `hieubt-hsb-ams-server`):**
  1. `deploy.sh` từng trỏ `REMOTE_DIR=/opt/hsb-it` (sai) trước khi được sửa thành `/opt/ams-hbt` trong cùng buổi sáng — khiến các lần deploy sớm không cập nhật đúng container đang chạy thật.
  2. Vì vậy lúc setup wizard chạy (10:33), biến `DB_DATABASE` chưa được cấu hình đầy đủ trên container đang phục vụ, code rơi vào giá trị fallback hardcode `'hsb_it'` trong `config/database.php:114` → admin bị tạo nhầm ở database `hsb_it` thay vì `snipeit` (database thật production dùng). Sau khi deploy đúng chạy xong (11:01/11:18), app chuyển hẳn sang `snipeit` (trống) → tưởng như "mất" admin vừa tạo.
  3. `Setting::setupCompleted()` (trước khi sửa) bắt mọi lỗi kết nối DB và coi là "chưa setup" → bất kỳ sự cố DB nào (kể cả tạm thời) cũng tự động lộ `/setup` công khai ra Internet, cho phép tạo admin mới/tự đổi cấu hình DB.
- **Xử lý khẩn cấp trên production (qua SSH, không đụng dữ liệu nghiệp vụ):** đồng bộ lại password MySQL user `snipeit` khớp `.env`; trỏ app sang database `hsb_it` (nơi admin thật sự được tạo) bằng cách ghi `/var/lib/hsbit/secrets/database.php`; xoá database `snipeit` (rỗng, chỉ có seed mặc định) theo yêu cầu người dùng.
- **Sự cố phát sinh khi vá tay:** file secrets viết qua `docker exec` (mặc định chạy quyền `root`) bị sai owner, Apache (chạy user `docker`) không đọc được nên vẫn fallback về cấu hình cũ — phải `chown docker:root` lại mới hết. Ghi chú vận hành: mọi thao tác ghi vào `/var/lib/hsbit/secrets/*` qua `docker exec` sau này phải `chown docker:root` (hoặc dùng `docker exec -u docker`).
- **What (code, để chặn tái diễn):**
  - `app/Models/Setting.php` — `setupCompleted()`: chỉ coi là "chưa setup" khi lỗi đúng là bảng chưa tồn tại (SQLSTATE `42S02`/`42P01`); các lỗi DB khác (mất kết nối, sai mật khẩu, sai database...) được ném lại (throw) để hiển thị trang lỗi bình thường thay vì lộ `/setup` công khai.
  - `app/Http/Controllers/SetupController.php` — thêm `pinEffectiveDatabaseConfig()`, gọi ở đầu `setupMigrate()`: chốt lại đúng giá trị kết nối DB đang thực sự active (dù đến từ secrets file, `.env`, hay fallback) vào file secrets *trước khi* migrate/tạo admin — đảm bảo database dùng để tạo dữ liệu luôn trùng với database mà các request sau này sẽ đọc, không còn khả năng lệch như sáng nay.
  - `deploy.sh` — cả `full` và `hot` mode: thêm health check sau deploy (curl trang chủ, cảnh báo nếu không phải HTTP 200; cảnh báo riêng nếu trang chủ redirect ra `/setup` trong khi file secrets DB vẫn tồn tại trên volume — đúng dấu hiệu của sự cố sáng nay).
- **Verify:** Đã test trực tiếp trên production qua SSH — trang chủ trả về `/login` (không còn `/setup`), tài khoản `hieubt@hsb.edu.vn` còn nguyên. Code mới đã lint qua `php -l` (không lỗi cú pháp) và `bash -n deploy.sh` (không lỗi cú pháp). Chưa chạy `php artisan test` đầy đủ (không có PHP CLI sẵn trong môi trường dev Windows hiện tại) — nên chạy `php artisan test` trước khi deploy để chắc chắn không phá test khác liên quan `CheckForSetup`/`SetupController`.
- **Rollback:** `git diff`/`git checkout` 3 file trên nếu cần; các thay đổi ở production (đồng bộ password, đổi database, xoá `snipeit`) không thể rollback qua git vì là thao tác trực tiếp trên VM — nếu cần khôi phục `snipeit` phải chạy lại migrate.
- 2026-08-22 16:17:33 | Edit | deploy.sh
- 2026-08-22 16:18:45 | Edit | implementation-notes.md
- 2026-08-22 17:33:12 | Edit | resources/lang/vi-VN/general.php
- 2026-08-22 17:33:15 | Write | app/Http/Controllers/PortalController.php
- 2026-08-22 17:33:17 | Write | app/Http/Controllers/DatRauHuuCoController.php
- 2026-08-22 17:33:20 | Edit | routes/web.php
- 2026-08-22 17:33:21 | Edit | routes/web.php
- 2026-08-22 17:33:24 | Edit | routes/web.php
- 2026-08-22 17:33:53 | Edit | resources/views/layouts/default.blade.php
- 2026-08-22 17:34:15 | Edit | tests/Feature/DashboardTest.php
- 2026-08-22 17:35:24 | Write | resources/views/portal.blade.php
- 2026-08-22 17:35:35 | Write | resources/views/dat-rau-huu-co/index.blade.php

## 2026-08-22 17:40 — Trang portal chọn phân hệ sau đăng nhập + module chờ phát triển "Đặt rau hữu cơ"

- **Why:** User muốn khi vào `/` phải đăng nhập trước, sau đó chọn 1 trong 2 phân hệ: "Quản lý Tài Sản HSB-IT" (như cũ) hoặc "Đặt rau hữu cơ" (module mới, hiện chỉ là placeholder chờ phát triển).
- **What:**
  - Route `home` (`/`) đổi từ trỏ thẳng `DashboardController@index` sang `PortalController@index` — trang hub hiển thị 2 thẻ module. Middleware `auth` giữ nguyên nên khách chưa đăng nhập vẫn bị redirect ra `/login` như trước (không cần đổi gì ở `LoginController`, `redirectTo` vẫn là `/` nên sau login sẽ vào thẳng portal).
  - Dashboard thống kê tài sản (nội dung cũ của `/`) chuyển sang route mới `dashboard` (`/dashboard`), route name `dashboard`. Đã cập nhật link "Dashboard" trong sidebar (`resources/views/layouts/default.blade.php`) trỏ sang `route('dashboard')` thay vì `route('home')`.
  - Thêm route/controller/view mới cho module chờ phát triển: `dat-rau-huu-co` (`DatRauHuuCoController`, view `resources/views/dat-rau-huu-co/index.blade.php`) — chỉ là placeholder "Đang phát triển", có nút quay lại portal.
  - `portal.blade.php` và view placeholder đều `@extends('layouts/default')` (không tách layout riêng) để không mất banner impersonation, sidebar, breadcrumb, dark mode... vốn đang được layout dùng chung xử lý.
  - Thêm các key dịch mới trong `resources/lang/vi-VN/general.php` (nhóm `portal_*` và `organic_vegetable_order`) thay vì hard-code chuỗi trong view.
- **Trade-off đã chọn:** Giữ nguyên route name `home` cho portal (không đổi tên) vì rất nhiều nơi (`BreadcrumbsServiceProvider`, `Helper::getRedirectOption`/`sameOriginUrl`, `GoogleAuthController`, `ImpersonateController`...) đang gọi `route('home')` làm điểm neo breadcrumb "Home" và điểm redirect mặc định — đổi tên sẽ phải sửa rất nhiều chỗ. Hệ quả: icon "Home" trên breadcrumb của mọi trang trong module HSB-IT giờ dẫn về portal thay vì thẳng vào dashboard thống kê (chỉ thêm 1 click) — chấp nhận được vì đúng tinh thần "portal là trang chủ".
  - "Đặt rau hữu cơ" hiện chỉ là placeholder route + view rỗng, chưa có controller/model/migration nghiệp vụ thật (đúng yêu cầu recommended ban đầu).
- **Verify:**
  - `php artisan route:list` xác nhận `/` → `home` (PortalController), `/dashboard` → `dashboard` (DashboardController), `/dat-rau-huu-co` → `dat_rau_huuco` (DatRauHuuCoController).
  - `php artisan view:cache` build sạch toàn bộ Blade (bao gồm 2 view mới) không lỗi cú pháp; đã `view:clear` lại sau khi kiểm tra.
  - `php -l` sạch cho các file PHP đã sửa/thêm.
  - Đã cập nhật `tests/Feature/DashboardTest.php` (đổi `route('home')` → `route('dashboard')`) vì route `home` không còn trả về view `dashboard` nữa. Các test khác gọi `route('home')` chỉ assert redirect target/status nên không bị ảnh hưởng.
  - **Chưa chạy được `php artisan test` đầy đủ** trong phiên này vì thiếu `.env.testing` (chỉ có `.env.testing.example`, cần `DB_DATABASE`/`DB_USERNAME`/`DB_PASSWORD` thật) — cần chạy full test suite trước khi deploy production.
- **Rollback:** `git diff`/`git checkout` các file: `routes/web.php`, `app/Http/Controllers/PortalController.php`, `app/Http/Controllers/DatRauHuuCoController.php`, `resources/views/portal.blade.php`, `resources/views/dat-rau-huu-co/index.blade.php`, `resources/views/layouts/default.blade.php`, `resources/lang/vi-VN/general.php`, `tests/Feature/DashboardTest.php`.
- 2026-08-22 17:37:43 | Edit | implementation-notes.md
- 2026-08-22 17:40:38 | Write | C:/Users/HieuBT/AppData/Local/Temp/claude/D--Dev-AMS-hbt/21fdc647-35e0-4b38-8379-37803f6bbf7c/scratchpad/check_user.php
- 2026-08-22 17:40:50 | Write | C:/Users/HieuBT/AppData/Local/Temp/claude/D--Dev-AMS-hbt/21fdc647-35e0-4b38-8379-37803f6bbf7c/scratchpad/check_user.php
- 2026-08-22 18:24:02 | Edit | C:/Users/HieuBT/.config/herd/config/valet/Nginx/ams_hbt.test.conf
- 2026-08-22 18:53:11 | Edit | app/Http/Controllers/SettingsController.php
- 2026-08-22 18:53:16 | Edit | routes/web.php
- 2026-08-22 18:53:27 | Edit | resources/lang/vi-VN/admin/settings/general.php
- 2026-08-22 18:53:30 | Edit | resources/lang/vi-VN/admin/settings/general.php
- 2026-08-22 18:54:11 | Write | resources/views/settings/domain.blade.php
- 2026-08-22 18:54:31 | Edit | app/Http/Controllers/SettingsController.php
- 2026-08-22 18:54:38 | Edit | resources/views/settings/index.blade.php

## 2026-08-22 19:00 — Thêm form cấu hình Domain (APP_URL) trong menu Cài đặt

- **Why:** Trong lúc chuẩn bị bật SSO đăng nhập Google, phát hiện production chưa có domain/HTTPS thật nên chưa đăng ký được redirect URI với Google. User yêu cầu có 1 form trong menu "Cài đặt" để cấu hình domain thay vì phải sửa `.env` bằng tay qua CLI/SSH.
- **What:**
  - `app/Http/Controllers/SettingsController.php`: thêm `getDomainSettings()`/`postDomainSettings()` — validate `app_url` (`required|url|max:255`), ghi vào `.env` bằng `setEnvValues()`/`quoteEnvValue()` (helper riêng, **cố ý copy** từ `SetupController` chứ không refactor dùng chung — theo quy tắc không sửa hàm sẵn có nếu không có yêu cầu rõ, tránh ảnh hưởng luồng setup wizard đang chạy tốt), rồi `Artisan::call('config:clear')` để áp dụng ngay.
  - `routes/web.php`: thêm `GET/POST admin/domain` → `settings.domain.index`/`settings.domain.save`, cùng nhóm middleware `auth + authorize:superuser` như các trang Settings khác.
  - `resources/views/settings/domain.blade.php` (mới): form 1 field `app_url`, hiển thị URL đang áp dụng hiện tại (`config('app.url')`), có cảnh báo (`x-callout`) về việc Google chỉ chấp nhận `https://` (trừ `localhost`/`127.0.0.1`) — lý do trực tiếp gây ra buổi làm việc này.
  - `resources/views/settings/index.blade.php`: thêm tile "Domain" (icon `globe-us`, đã có sẵn trong `IconHelper`) vào lưới Settings, cạnh "General Settings".
  - `resources/lang/vi-VN/admin/settings/general.php`: thêm các key `domain`, `domain_title`, `domain_help`, `domain_app_url`, `domain_app_url_help`, `domain_current_effective_url`, `domain_https_warning` và mục `keywords.domain` cho ô tìm kiếm ở trang Settings index.
- **Trade-off đã chọn:** Chỉ cấu hình `APP_URL`, không đụng tới `TrustProxies`/`TRUSTED_PROXIES` dù production đứng sau Cloudflare — cần cấu hình sau nếu phát hiện app nhận sai scheme (http/https) từ header `X-Forwarded-Proto`. Không tạo `FormRequest` riêng (không theo pattern `SettingsFirebaseSourceRequest`/`SetupDatabaseRequest`) vì chỉ có 1 field, dùng `Validator::make()` inline cho gọn — nhất quán với `postSecurity()` cùng file.
- **Verify:** `php artisan route:list` xác nhận `GET/POST admin/domain`; `php -l` sạch cho `SettingsController.php`, `routes/web.php`, file lang; `php artisan view:cache` build sạch toàn bộ Blade (gồm `settings/domain.blade.php`) không lỗi, đã `view:clear` lại sau khi kiểm tra. Grep mẫu lỗi dấu hỏi (`[A-Za-zÀ-ỹ]\?`) trên file lang đã sửa — không có ký tự lỗi mới.
  - **Chưa test bằng UI thật** (đăng nhập vào `/admin/domain`, submit form, xác nhận `.env` được ghi đúng) — nên test tay trước khi dùng field này để đổi `APP_URL` thật trên production.
- **Bối cảnh liên quan (chưa xử lý xong):** Domain thật `https://portal.hsb.edu.vn` đã trỏ DNS qua Cloudflare (IP Cloudflare, không phải trỏ thẳng `34.142.200.14`), nhưng `https://portal.hsb.edu.vn/` hiện trả lỗi Cloudflare 521 (không kết nối được tới origin) — cần xử lý ở tầng Cloudflare (SSL/TLS mode) và/hoặc mở port + cấu hình HTTPS trên VM trước khi form Domain này thật sự dùng được cho SSO Google. Việc sửa Nginx config của Herd để test SSO qua `localhost` cục bộ đã thử và **không khả thi** (Herd định tuyến site theo registry nội bộ, không chỉ theo `server_name` — đã revert lại nguyên trạng, xác nhận `ams_hbt.test` vẫn chạy bình thường).
- **Rollback:** `git diff`/`git checkout` các file: `app/Http/Controllers/SettingsController.php`, `routes/web.php`, `resources/views/settings/domain.blade.php`, `resources/views/settings/index.blade.php`, `resources/lang/vi-VN/admin/settings/general.php`.
- 2026-08-22 18:55:58 | Edit | implementation-notes.md
