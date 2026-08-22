# Hướng dẫn cấp chứng chỉ HTTPS cho HSB-IT

Tài liệu này dùng cho máy chủ Google Compute Engine có IP công khai `34.142.200.14`.

## Phương án khuyến nghị: dùng tên miền

Không nên dùng IP trực tiếp cho hệ thống vận hành lâu dài. Hãy dùng một tên miền, ví dụ:

```text
it.hsb.vn
```

### 1. Trỏ DNS về máy chủ

Tại nhà cung cấp tên miền, tạo bản ghi:

```text
Loại: A
Tên: it
Giá trị: 34.142.200.14
TTL: 300 hoặc mặc định
```

Sau khi cập nhật, kiểm tra:

```bash
nslookup it.hsb.vn
```

Kết quả phải trả về `34.142.200.14`.

### 2. Mở cổng trên Google Cloud

Trong Google Cloud Console:

1. Vào **VPC network** > **Firewall**.
2. Tạo hoặc kiểm tra rule cho phép TCP `80` và `443`.
3. Gắn rule vào VM `hieubt-hsb-ams-server` bằng network tag hoặc áp dụng cho đúng VPC.

Không mở các cổng database ra Internet nếu không cần thiết.

### 3. Kiểm tra máy chủ đang nhận HTTP

Trên VM:

```bash
cd /opt/ams-hbt
sudo docker compose ps
curl -I http://34.142.200.14
```

Ứng dụng phải đang phục vụ HTTP trên cổng `80` trước khi xin chứng chỉ.

### 4. Cấp chứng chỉ Let’s Encrypt

Cài Certbot trên VM:

```bash
sudo apt update
sudo apt install -y certbot
```

Nếu cổng 80 có thể tạm dừng ứng dụng, dùng chế độ standalone:

```bash
cd /opt/ams-hbt
sudo docker compose stop app
sudo certbot certonly --standalone -d it.hsb.vn
sudo docker compose start app
```

Chứng chỉ được lưu tại:

```text
/etc/letsencrypt/live/it.hsb.vn/fullchain.pem
/etc/letsencrypt/live/it.hsb.vn/privkey.pem
```

### 5. Cấu hình HTTPS cho Apache/Docker

Apache hoặc reverse proxy phải được cấu hình để:

- lắng nghe cổng `443`;
- dùng `fullchain.pem` làm certificate chain;
- dùng `privkey.pem` làm private key;
- chuyển tiếp HTTP sang HTTPS;
- giữ nguyên kết nối tới ứng dụng HSB-IT trong container.

Với Docker, cần mount thư mục chứng chỉ vào container hoặc đặt một reverse proxy như Caddy/Nginx ở phía trước container app. Không chép private key vào GitHub.

Sau khi cấu hình:

```bash
sudo docker compose up -d
curl -I https://it.hsb.vn
```

Trong ứng dụng, đặt URL chính thành:

```text
APP_URL=https://it.hsb.vn
```

Sau đó xóa cache Laravel:

```bash
sudo docker exec ams-hbt-app-1 php artisan optimize:clear
```

### 6. Kiểm tra gia hạn

```bash
sudo certbot renew --dry-run
```

Chứng chỉ Let’s Encrypt thông thường có thời hạn khoảng 90 ngày. Cần bảo đảm timer hoặc cron của Certbot đang chạy để tự gia hạn.

## Phương án dùng trực tiếp IP

Let’s Encrypt hiện hỗ trợ chứng chỉ cho IP, nhưng chứng chỉ IP là chứng chỉ ngắn hạn, thời gian sống khoảng 160 giờ, tức hơn 6 ngày. Certbot cần phiên bản từ `5.4` trở lên và phải tự động gia hạn thường xuyên.

Các điều kiện chính:

- IP phải là IP công khai và thuộc quyền kiểm soát của máy chủ;
- chỉ dùng xác thực `http-01` hoặc `tls-alpn-01`;
- không dùng được `dns-01` cho IP;
- phải có cơ chế gia hạn tự động đáng tin cậy.

Do đó, chỉ dùng chứng chỉ IP cho thử nghiệm hoặc trường hợp đặc biệt. Với hệ thống HSB-IT, nên dùng tên miền trỏ về `34.142.200.14`.

## Lưu ý bảo mật

- Nên chuyển IP VM sang địa chỉ tĩnh trước khi công bố DNS.
- Không commit các file `privkey.pem`, `.env`, mật khẩu database hoặc token vào GitHub.
- Chỉ mở TCP `80` và `443` từ Internet.
- Có thể đóng cổng `80` sau khi dùng DNS-01, nhưng HTTP thường được giữ để chuyển hướng sang HTTPS.
- Nếu đổi IP, phải cập nhật DNS và cấp lại hoặc cấu hình lại chứng chỉ.

## Tài liệu tham khảo

- [Let’s Encrypt: IP Address Certificates](https://letsencrypt.org/2026/01/15/6day-and-ip-general-availability.html)
- [Let’s Encrypt: IP certificates with Certbot](https://letsencrypt.org/2026/03/11/shorter-certs-certbot)
- [Google Cloud: Compute Engine IP addresses](https://cloud.google.com/compute/docs/ip-addresses)