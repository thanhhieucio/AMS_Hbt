# CLAUDE.md

Tài liệu này hướng dẫn Claude Code (`claude.ai/code`) khi làm việc với mã nguồn trong repository HSB-IT.

## Stack

- **PHP 8.2+** / **Laravel 12** cho backend; **Laravel Mix** và webpack cho asset frontend.
- **AdminLTE 2** / **Bootstrap 3** cho giao diện; dùng Blade view, không dùng Livewire/Inertia cho phần UI chính.
- **Chart.js v2.9.4** được bundle tại `public/js/dist/Chart.min.js`; dùng kiểu chart `horizontalBar` theo API v2, không dùng API v3.

## Lệnh thường dùng

```bash
# Chạy toàn bộ test
php artisan test
# hoặc
vendor/bin/phpunit

# Chạy một file test
php artisan test tests/Feature/Assets/AssetsTest.php

# Chạy một test method cụ thể
php artisan test --filter testSomeMethod

# Build asset frontend cho dev
npm run dev

# Build production
npm run prod

# Theo dõi thay đổi bằng Laravel Mix
npm run watch

# Tinker / REPL
php artisan tinker

# Xóa cache sau khi đổi config/route
php artisan optimize:clear
```

Dev server thường chạy qua **Laravel Herd**. Dùng `herd coverage` khi cần báo cáo coverage.

## Kiến trúc

### Controller

Có hai nhánh controller song song:

- `app/Http/Controllers/`: controller web/UI trả Blade view.
- `app/Http/Controllers/Api/`: controller REST API trả JSON, dùng cho datatable và Select2.

Các nhóm thư mục chính: `Assets/`, `Licenses/`, `Users/`, `Accessories/`, `Consumables/`, `Components/`, `Kits/`, `Account/`, `Auth/`.

### Pattern API

Mọi API controller phải trả dữ liệu qua **Transformer** trong `app/Http/Transformers/`. Không trả trực tiếp raw model attributes từ API controller. `DatatablesTransformer` bọc kết quả phân trang.

```php
return (new AssetsTransformer)->transformAssets($assets, $assets->count());
```

### Phân quyền

Toàn bộ phân quyền đi qua **Policy** trong `app/Policies/`. `CheckoutablePermissionsPolicy` là lớp nền cho tài sản, license, phụ kiện và vật tư. Các method `checkout()` / `checkin()` chấp nhận `$item = null`, nên có thể dùng `@can('checkout', \App\Models\Asset::class)` khi chưa có instance.

### FMCS (Full Multiple Company Support)

`Setting::getSettings()->full_multiple_companies_support == '1'` bật lọc theo công ty. Các endpoint Select2 API (`selectlist()`) nhận query param `companyId`; áp dụng như sau:

```php
if ((Setting::getSettings()->full_multiple_companies_support == '1') && ($request->filled('companyId'))) {
    $query->where('table.company_id', $request->input('companyId'));
}
```

Trong Blade, truyền `data-company-id="{{ $user->company_id }}"` để nối dữ liệu này vào Select2.

### Dropdown Select2 AJAX

Dùng `class="js-data-ajax"` với `data-endpoint="hardware|licenses|consumables|..."`. `hsbit.js` tự khởi tạo các dropdown này, đồng thời chuyển `data-company-id` thành `companyId` và `data-asset-status-type` thành `statusType` cho API.

### Route

Route nằm trong `routes/web.php` cho UI và `routes/api.php` cho API. Breadcrumb được định nghĩa inline bằng `->breadcrumbs(fn (Trail $trail) => ...)` từ `tabuna/breadcrumbs`. Mỗi route UI nên có breadcrumb.

Lưu ý: route `reports/unaccepted_assets` được đặt tên bằng dấu gạch chéo, không phải dấu chấm; dùng `route('reports/unaccepted_assets')`.

### Bản dịch

Chuỗi hiển thị nằm trong `resources/lang/vi-VN/*.php` và các thư mục con tương ứng. Khi thêm UI text mới, ưu tiên thêm key dịch thay vì hard-code chuỗi trong view/controller.

### Luồng redirect sau checkout

Sau checkout, `Helper::getRedirectOption()` đọc `$request->redirect_option`. Muốn redirect về người dùng được cấp phát sau checkout:

- Đặt `redirect_option=target` trong form.
- Đặt `checkout_to_type=user` trong form.
- Đặt `assigned_user={{ $user->id }}` trong form.

### Helper quan trọng (`app/Helpers/Helper.php`)

- `Helper::deployableStatusLabelList()`: danh sách status label dùng cho form checkout.
- `Helper::defaultChartColors()`: bảng 10 màu dùng trong chart.
- `Helper::getRedirectOption($request, $id, $table)`: logic redirect sau checkout.

### Biến view toàn cục

`$hsbSettings` được inject vào mọi view qua service provider. Không cần truyền `Setting::getSettings()` từ từng controller; dùng trực tiếp trong Blade.

## Kiểm thử

Test nằm trong `tests/Feature/` theo từng entity và `tests/Unit/`. Feature test chạm cơ sở dữ liệu. Môi trường test dùng driver `array` cho cache, session và mail. Dữ liệu setup qua factory.

## Quy định bắt buộc

### Phong cách phản hồi

Trả lời ngắn gọn, đi thẳng vào việc. Mỗi update nên là một câu. Không thêm tổng kết thừa, không lặp lại điều vừa làm, không dùng câu đệm. Chỉ thêm comment trong code khi lý do không hiển nhiên.

### Ghi chú triển khai

Khi triển khai tính năng hoặc thay đổi có phạm vi rõ, phải duy trì `implementation-notes.md` ở root dự án. File này cần ghi:

- Quyết định đã đưa ra khi spec chưa nêu rõ.
- Điểm thay đổi so với spec ban đầu và lý do.
- Trade-off đã chọn và lập luận.
- Điều reviewer hoặc người vận hành cần biết.

Cập nhật file này trong quá trình làm, không chỉ viết ở cuối.

### 1. Vai trò

- Senior Leader: quản trị phạm vi, mục tiêu và tiến độ.
- Senior Developer: chịu trách nhiệm kỹ thuật, chất lượng và bảo mật.

### 2. Quy tắc kỹ thuật

1. Code đơn giản, dễ đọc, dễ bảo trì.
2. Tách rõ client/server.
3. Route private bắt buộc verify session ở server.
4. Không để secret trong source code.
5. Không sửa hàm tính toán sẵn có nếu không có yêu cầu rõ ràng.

### 3. Quy tắc naming

Repo này là Laravel/PHP nên giữ convention kỹ thuật của hệ sinh thái: class PascalCase, method camelCase, DB column snake_case theo Eloquent. Chỉ dùng tiếng Việt không dấu cho định danh mới khi phạm vi đó đã có quy ước nội bộ tương ứng và không phá chuẩn framework.

Ví dụ tham khảo tinh thần đặt tên rõ nghĩa:

```js
const thong_tin_hoi_vien = {};
const danh_sach_co_so = [];

function tao_tai_khoan_nguoi_dung(du_lieu_dang_ky) {}
function kiem_tra_quyen_truy_cap(ma_quyen, pham_vi) {}
```

### 4. Ngôn ngữ hiển thị

1. Nội dung hiển thị cho người dùng trên UI phải dùng tiếng Việt có dấu chuẩn Unicode.
2. Câu chữ phải rõ nghĩa, minh bạch, không viết tắt khó hiểu.
3. Tài liệu `.md`, caption, title và client text trong dự án phải viết tiếng Việt có dấu chuẩn.
4. Chỉ dùng không dấu cho định danh kỹ thuật như biến, hàm, field, bảng, cột và tên file kỹ thuật.

### 5. Auth

HSB-IT dùng hệ auth hiện có của Laravel với bảng `users`, đồng thời có các phần LDAP/SAML/SCIM. Khi sửa auth, phải kiểm tra kỹ policy, middleware, session và guard liên quan.

### 6. CSS

HSB-IT dùng Laravel Mix và LESS, xem `webpack.mix.js` và `resources/assets/less/`. CSS mới phải kế thừa cấu trúc hiện có và không phá style toàn app.

### 6.1. Phím tắt form

Hiện HSB-IT chưa có quy ước phím tắt F6 toàn cục. Nếu bổ sung phím tắt lưu form, phải bảo đảm đúng form đang focus hoặc modal ở lớp trên cùng được lưu, không tác động form phía sau.

### 7. Tiêu chí hoàn tất

1. Naming đúng chuẩn kỹ thuật của repo.
2. Ngôn ngữ hiển thị dùng tiếng Việt Unicode chuẩn.
3. Auth và phân quyền không bị vỡ.
4. Có kiểm tra local tối thiểu như lint hoặc build nếu thay đổi có liên quan.
5. Tài liệu liên quan được cập nhật.

### 8. Chống lỗi tiếng Việt thành dấu hỏi

#### Triệu chứng

Chuỗi tiếng Việt trên UI hiển thị dạng `Th? T?p`, `Phi?u xu?t`, `s?n ph?m`; ký tự có dấu bị thay bằng `?`.

#### Nguyên nhân gốc

File chứa string literal tiếng Việt bị lưu sai encoding, ví dụ ANSI/Windows-1252 thay vì UTF-8. Build tool đọc file rồi làm hỏng byte đa ký tự. Đây không phải lỗi font và không phải lỗi database.

#### Quy tắc bắt buộc

1. Tạo/sửa file có tiếng Việt bằng cơ chế ghi UTF-8 rõ ràng.
2. Sau khi tạo file mới, đọc lại và kiểm tra trực quan; nếu thấy `?` thay cho ký tự có dấu thì phải sửa ngay.
3. Grep nhanh trên file vừa sửa bằng pattern `[A-Za-zÀ-ỹ]\?` để phát hiện chữ liền dấu hỏi.
4. Không bàn giao UI, component, API response hoặc tài liệu còn lỗi dấu tiếng Việt.
5. Khi phát hiện lỗi: đọc file xác nhận lỗi, ghi lại toàn bộ file bằng UTF-8, đọc lại xác nhận sạch.