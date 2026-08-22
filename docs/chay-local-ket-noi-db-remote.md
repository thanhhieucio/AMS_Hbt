# Chạy HSB-IT local với database trên VM

Mục tiêu: chạy code trên máy Windows tại `http://localhost:8000`, nhưng kết nối database MariaDB đang chạy trên VM `34.142.200.14` mà không mở cổng database ra Internet.

## Kiến trúc

```text
HSB-IT local container
  -> host.docker.internal:3307
  -> SSH tunnel
  -> VM 127.0.0.1:3306
  -> MariaDB container production
```

Cách này cho phép sửa code local và kiểm thử trước khi deploy. Vì local đang dùng dữ liệu thật, không chạy các lệnh `migrate:fresh`, `db:wipe`, reset database hoặc seed phá dữ liệu.

## 1. Chuẩn bị server một lần

File `docker-compose.yml` đã bind database production vào loopback của VM:

```yaml
ports:
  - "127.0.0.1:${DB_FORWARD_PORT:-3306}:3306"
```

Cần deploy thay đổi này lên VM bằng full deploy một lần:

```powershell
& "C:\Program Files\Git\bin\bash.exe" deploy.sh full
```

Sau khi deploy, kiểm tra trên VM:

```bash
cd /opt/ams-hbt
sudo docker compose ps
sudo ss -ltnp | grep 3306
```

Kết quả đúng phải là MariaDB lắng nghe trên `127.0.0.1:3306`, không phải `0.0.0.0:3306`.

## 2. Tạo file cấu hình local

Trong PowerShell tại thư mục dự án:

```powershell
Copy-Item .env.dev.remote-db.example .env.dev.remote-db
```

Mở `.env.dev.remote-db` và thay:

```dotenv
APP_KEY=copy-your-local-app-key-here
DB_USERNAME=replace-with-db-user
DB_PASSWORD=replace-with-db-password
```

Các giá trị kết nối phải là:

```dotenv
DB_CONNECTION=mysql
DB_HOST=host.docker.internal
DB_PORT=3307
DB_DATABASE=hsb_it
```

File `.env.dev.remote-db` đã được thêm vào `.gitignore`, không được commit lên GitHub.

## 3. Mở SSH tunnel

Mở một cửa sổ PowerShell riêng và để cửa sổ này chạy:

```powershell
gcloud compute ssh hieubt-hsb-ams-server `
  --zone=asia-southeast1-b `
  -- -N -L 3307:127.0.0.1:3306
```

Giữ cửa sổ này mở trong suốt thời gian kiểm thử. Tunnel chuyển cổng local `3307` vào MariaDB trên VM qua SSH mã hóa.

Có thể kiểm tra cổng local:

```powershell
Test-NetConnection 127.0.0.1 -Port 3307
```

Kết quả cần có:

```text
TcpTestSucceeded : True
```

## 4. Chạy ứng dụng local

Mở cửa sổ PowerShell thứ hai:

```powershell
docker compose -f dev.remote-db.docker-compose.yml up --build
```

Sau đó truy cập:

```text
http://localhost:8000
```

Ứng dụng dùng bind mount `.:/var/www/html`, vì vậy thay đổi PHP, Blade, route và resource local sẽ được phản ánh trực tiếp trong container. Không cần deploy lên VM cho mỗi lần sửa code.

## 5. Dừng ứng dụng

```powershell
docker compose -f dev.remote-db.docker-compose.yml down
```

Để dừng tunnel, chuyển sang cửa sổ PowerShell đang chạy tunnel và nhấn `Ctrl+C`.

## 6. Lưu ý quan trọng

- Không chạy migration thay đổi schema trên database production khi chưa backup và kiểm tra kỹ.
- Không dùng `migrate:fresh`, `db:wipe`, `db:seed` hoặc các thao tác xóa dữ liệu trên môi trường này.
- Nên tạo một database test riêng trên VM nếu cần thử migration hoặc dữ liệu mẫu.
- Không mở TCP `3306` trên Google Cloud Firewall.
- Không đưa `.env.dev.remote-db`, mật khẩu database hoặc private key vào GitHub.
- Nếu VM chưa có IP tĩnh, nên reserve static external IP trước khi sử dụng lâu dài.