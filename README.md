# HSB-IT

HSB-IT là hệ thống quản lý tài sản CNTT được cá nhân hóa cho HSB/HieuBT, phát triển từ nền tảng quản lý tài sản mã nguồn mở trên Laravel.

Hệ thống hỗ trợ quản lý vòng đời tài sản CNTT: máy tính, thiết bị, phụ kiện, license phần mềm, vật tư tiêu hao, linh kiện, người dùng, phòng ban, địa điểm, nhà cung cấp, bảo trì và lịch sử bàn giao/thu hồi.

## Mục tiêu

- Tập trung hóa dữ liệu tài sản CNTT của đơn vị.
- Theo dõi ai đang giữ thiết bị nào, thời điểm cấp phát, thu hồi, bảo trì và kiểm kê.
- Hỗ trợ tem nhãn, mã vạch/QR, import CSV, báo cáo và audit.
- Việt hóa giao diện mặc định và thương hiệu hóa thành HSB-IT.
- Triển khai bằng Docker để dễ vận hành trên máy chủ nội bộ hoặc cloud.

## Công nghệ

- Laravel 12 / PHP 8.2+
- MariaDB/MySQL
- Docker / Docker Compose
- Bootstrap/AdminLTE
- Locale mặc định: `vi-VN`
- Tiền tệ mặc định: `VND`

## Cài đặt nhanh bằng Docker

1. Sao chép file cấu hình mẫu:

```bash
cp .env.example .env
```

2. Cập nhật các giá trị quan trọng trong `.env`:

```env
APP_URL=http://your-domain-or-ip
APP_KEY=base64:...
DB_DATABASE=hsbit
DB_USERNAME=hsbit
DB_PASSWORD=...
MYSQL_ROOT_PASSWORD=...
SITE_NAME="HSB-IT"
APP_LOCALE=vi-VN
```

3. Build và chạy container:

```bash
docker compose build
docker compose up -d
```

4. Mở trình duyệt đến `APP_URL` và hoàn tất wizard tạo tài khoản admin đầu tiên.

## Deploy production

Repo có sẵn script `deploy.sh` cho VM production hiện tại. Trước khi chạy deploy, cần đảm bảo code đã commit và push lên GitHub:

```bash
./deploy.sh
```

Script sẽ pull code trên server, build image `hieubt/hsb-it:latest`, sau đó chạy lại Docker Compose.

## Cấu hình sau khi cài đặt

Trong giao diện admin, nên kiểm tra các mục sau:

- Settings > Branding: logo, favicon, tên hệ thống, màu sắc.
- Settings > Localization: ngôn ngữ `Vietnamese`, tiền tệ `VND`, timezone `Asia/Ho_Chi_Minh`.
- Settings > Email: SMTP thật nếu muốn gửi email bàn giao/thông báo.
- Settings > Barcodes: định dạng tem nhãn và QR theo quy trình nội bộ.
- Users / Departments / Locations: tạo cấu trúc người dùng, phòng ban, địa điểm.

## Ghi chú vận hành

- Không commit secret production vào GitHub.
- Sao lưu database và thư mục upload trước khi nâng cấp.
- Khi đổi `.env` trên production, chạy lại container hoặc clear config cache.
- Nếu đổi domain/HTTPS, cập nhật `APP_URL` và cấu hình reverse proxy tương ứng.

## Bản quyền và nguồn gốc

HSB-IT là bản cá nhân hóa nội bộ dựa trên nền tảng mã nguồn mở AGPL-3.0-or-later. Khi phân phối hoặc triển khai cho bên thứ ba, cần tuân thủ giấy phép của dự án gốc và các thư viện phụ thuộc.