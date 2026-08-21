# Chạy bộ kiểm thử

Tài liệu này dành cho lập trình viên cần sửa mã nguồn HSB-IT và chạy bộ kiểm thử hiện có.

Trước khi bắt đầu, hãy làm theo phần [cài đặt](README.md#cai-dat-nhanh) để chạy ứng dụng ở máy local và bảo đảm có thể mở ứng dụng trong trình duyệt.

## Kiểm thử Unit và Feature

Trước khi chạy test, hãy sao chép file môi trường mẫu cho test và chỉnh lại các giá trị theo môi trường của bạn:

`cp .env.testing.example .env.testing`

Cấu hình sau có thể dùng để chạy test bằng SQLite trong bộ nhớ:

```env
# --------------------------------------------
# BẮT BUỘC: CẤU HÌNH ỨNG DỤNG CƠ BẢN
# --------------------------------------------
APP_ENV=testing
APP_DEBUG=true
APP_KEY=base64:glJpcM7BYwWiBggp3SQ/+NlRkqsBQMaGEOjemXqJzOU=
APP_URL=http://localhost:8000
APP_TIMEZONE='Asia/Ho_Chi_Minh'
APP_LOCALE=vi-VN

# --------------------------------------------
# BẮT BUỘC: CẤU HÌNH CƠ SỞ DỮ LIỆU
# --------------------------------------------
DB_CONNECTION=sqlite_testing
#DB_HOST=127.0.0.1
#DB_PORT=3306
#DB_DATABASE=null
#DB_USERNAME=null
#DB_PASSWORD=null
```

Nếu dùng MySQL, hãy cập nhật các biến `DB_` cho khớp cơ sở dữ liệu test local:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE={}
DB_USERNAME={}
DB_PASSWORD={}
```

Sau đó có thể chạy toàn bộ bộ kiểm thử từ terminal:

```shell
php artisan test
```

Chạy một file test cụ thể bằng cách truyền đường dẫn file:

```shell
php artisan test tests/Unit/AccessoryTest.php
```

Một số test, ví dụ nhóm liên quan LDAP, được đánh dấu bằng annotation `@group`. Có thể chạy riêng hoặc loại trừ nhóm đó bằng `--group` hoặc `--exclude-group`:

```shell
php artisan test --group=ldap

php artisan test --exclude-group=ldap
```

Cách này hữu ích khi một nhóm test thất bại vì máy local chưa cài extension tương ứng, ví dụ LDAP.