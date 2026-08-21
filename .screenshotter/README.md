# Screenshotter

Screenshotter là walkthrough chạy bằng Playwright cho giao diện HSB-IT, dùng để tạo ảnh PNG phục vụ tài liệu, marketing hoặc tham chiếu nội bộ. Script đăng nhập, đi qua các trang và tương tác chuẩn, rồi ghi ảnh ra thư mục ngoài mã nguồn (`.screenshotter/screenshots/` theo mặc định, đã gitignore). Việc sử dụng các PNG tạo ra là trách nhiệm của người chạy script.

## Yêu cầu

- Node 18+ vì script dùng `node:util.parseArgs`.
- Playwright và Chromium. Cả hai đã nằm trong dev dependency của repo, nên `npm install` là đủ.
- Một bản HSB-IT đang chạy để script trỏ tới. Herd hoặc `php artisan serve` đều dùng được. Target mặc định là `https://hsb-it.test`.
- Tài khoản superuser trên bản cài đó. Mặc định là `admin` / `password`, đúng với dữ liệu demo seeder tạo ra.

## Cảnh báo dữ liệu

Script chụp nguyên trạng dữ liệu đang có trong database được kết nối. **Không chạy script này trên production, staging mirror production, hoặc bất kỳ database nào chứa dữ liệu khách hàng thật, PII người dùng thật, avatar/tài liệu upload thật, license key, IP address, mã nhân viên hoặc bất kỳ thông tin nào bạn không muốn công khai.**

Khi ảnh đã nằm trên máy, chỉ cần kéo-thả nhầm là ảnh có thể đi lên GitHub, Discord, Reddit, ticket hỗ trợ, site tài liệu, Slack, attachment bug report hoặc nơi khác. Ảnh chụp từ bản cài thật từng là nguồn rò rỉ dữ liệu trong nhiều dự án. Hãy chỉ dùng bản demo-seeded trừ khi bạn đã tự kiểm tra dữ liệu trong bản cài đó.

## Cách dùng

Trỏ script tới một bản HSB-IT bất kỳ và nó sẽ chụp dữ liệu đang có. Trong thực tế, nên dùng bản demo mới seed lại để ảnh có thể tái tạo và an toàn khi công bố.

```bash
# Khuyến nghị: seed lại trước để ảnh phản ánh dữ liệu demo chuẩn
php artisan migrate:fresh --seed
npm run screenshotter

# Cũng được, nếu bạn biết rõ dữ liệu local DB là an toàn để chụp
npm run screenshotter
```

Khi bắt đầu, script in cấu hình để bạn biết đang chạy mode nào; khi kết thúc, script in thời gian chạy:

```text
Base URL:  https://hsb-it.test
Login as:  admin
Output:    .screenshotter/screenshots
Viewport:  1840x900
Framing:   local (generic-light)
Submit:    on (edit forms are posted after shot)
Tabs:      off (view pages shoot base only)
Color:     light

→ logging in
→ assets (as admin)
  ✓ assets/admin-assets-index-...png (framed)
  ...
Done. 166 screenshots written to .screenshotter/screenshots in 4m 12.3s.
```

Mỗi lần chạy full, script xóa ảnh walkthrough cũ trước khi chụp để ảnh cũ không trộn với ảnh mới. Chỉ giữ lại `.screenshotter/README.md`, `.screenshotter/src/screenshotter.mjs` và thư mục `.screenshotter/screenshots/adhoc/`.

## Biến môi trường override

```bash
BASE_URL=https://staging.example.com      # mặc định: https://hsb-it.test
USERNAME=hsb                              # mặc định: admin
PASSWORD=secret                           # mặc định: password
OUT=/tmp/hsb-shots                        # mặc định: .screenshotter/screenshots
HEADLESS=false                            # mặc định: true; false để xem trình duyệt chạy
VIEWPORT_WIDTH=1920 VIEWPORT_HEIGHT=1080  # mặc định: 1840x900
FRAME=false                               # mặc định: true
SUBMIT_FORMS=false                        # mặc định: true
TABS=true                                 # mặc định: false
ALL_ROUTES=false                          # mặc định: true
COLOR_SCHEME=dark                         # mặc định: light
TABLE_PAGE_SIZE=25                        # mặc định: 10; số dòng bootstrap-table mỗi ảnh
```

`HEADLESS=false` mở trình duyệt thật để bạn quan sát walkthrough, hữu ích khi thêm block mới hoặc debug selector. `TABLE_PAGE_SIZE` giảm số dòng trong bảng index để ảnh không dài không cần thiết.

## Tác động phụ lên database

Walkthrough submit từng form edit sau khi chụp để ghi lại UI sau lưu, ví dụ callout thành công hoặc trạng thái lỗi validation. Vì vậy một lần chạy full có ghi vào database được kết nối. Cụ thể:

- Mỗi object chính có một lần update no-op cho mỗi viewer, tạo thêm một dòng `action_logs` cho mỗi lần submit.
- Observer, notification hoặc webhook gắn vào event update sẽ chạy như một lần sửa thật. Với bản demo thì thường ổn, nhưng nếu bản cài có webhook trỏ tới endpoint thật như Slack hoặc service nội bộ, chúng cũng sẽ được gọi.
- Script không cố ý đổi dữ liệu vì form được submit với giá trị sẵn có trên trang, nhưng "không đổi giá trị" không đồng nghĩa với "không có tác động phụ".

Trước khi submit, script ép hidden field `redirect_option=index` để sau khi lưu luôn quay về trang index của section. Điều này tạo ảnh ổn định với success callout trên index, bất kể logic `redirect_option` mặc định của HSB-IT sẽ chọn gì dựa trên session.

Với bản demo local mới seed, tác động này là bình thường. Với bất kỳ môi trường nào khác, không chạy full walkthrough hoặc tắt bước submit:

```bash
# Bỏ ghi ngược và bỏ ảnh `-edit-submitted`
SUBMIT_FORMS=false npm run screenshotter
```

Khi `SUBMIT_FORMS=false`, walkthrough ở chế độ chỉ đọc: không post form, không tạo `action_logs`, không kích hoạt observer/webhook. Đổi lại bạn mất ảnh UI sau khi lưu.

## Chặn overlay dev-tool ở tầng network

Debugbar, Telescope và Clockwork đều bị abort request asset bằng Playwright network interception. JavaScript của chúng không tải, overlay không render, nên ảnh không có panel debug. Cách này chắc hơn ẩn bằng CSS, vì CSS từng hỏng trên trang lỗi HSB-IT khi debugbar render JSON collector panel bằng selector không truy cập được.

Nếu thêm dev-tool khác có overlay toàn trang, hãy bổ sung asset path của nó vào block `context.route(...)` gần đầu script.

## Chạy theo section cụ thể

Dùng `--section <name>` để tạo lại một hoặc nhiều section thay vì chạy toàn bộ walkthrough. Tên section khớp thư mục dưới `screenshots/`, đồng thời filter này cũng quyết định resource-manager nào được chạy.

```bash
# Chỉ section assets (admin + assetmgr)
npm run screenshotter -- --section assets

# Nhiều section, phân tách bằng dấu phẩy
npm run screenshotter -- --section settings,reports,dashboard

# Section độc lập cũng dùng được
npm run screenshotter -- --section dashboard
```

Các section hiện có: `assets`, `licenses`, `accessories`, `consumables`, `components`, `users`, `models`, `categories`, `manufacturers`, `suppliers`, `locations`, `departments`, `kits`, `companies`, `statuslabels`, `depreciations`, `custom-fields`, `fieldsets`, `maintenance-types`, `dashboard`, `settings`, `reports`.

Khi đặt `--section`, ảnh của section khác từ lần chạy trước được giữ lại và lượt quét `all-routes` bị bỏ qua vì không gắn với section cụ thể.

## Chụp các tab trên trang xem chi tiết

Mặc định tắt. Đặt `TABS=true` để chụp từng Bootstrap tab pane trên trang view. Riêng trang xem tài sản có khoảng 10 tab như Licenses, Components, Maintenances, Audits, Notes, Files. Tên ảnh có dạng `{section}/{user}-{section}-view-tab-{slug}`.

```bash
# Chụp toàn bộ tab
TABS=true npm run screenshotter

# Chỉ assets, kèm tab
TABS=true npm run screenshotter -- --section assets
```

Các trang không có tab sẽ được bỏ qua âm thầm. Tab đang active sẵn cũng được bỏ qua vì ảnh view gốc đã chụp nội dung đó.

## Light mode và dark mode

Dùng `COLOR_SCHEME=light` theo mặc định hoặc `COLOR_SCHEME=dark`. Script dùng option `colorScheme` của Playwright để đặt `prefers-color-scheme` ở cấp browser. Người dùng HSB-IT có theme preference là `system` sẽ render theo scheme yêu cầu mà không cần thao tác UI.

```bash
COLOR_SCHEME=dark npm run screenshotter
COLOR_SCHEME=dark npm run screenshotter -- --section assets
```

Nếu theme preference của người dùng được đặt cụ thể như `always dark` hoặc `always light`, ứng dụng sẽ ưu tiên cấu hình đó và flag này không có tác dụng với tài khoản đó.

## Khung trình duyệt

Mặc định mọi ảnh tạo ra được bọc bằng browser chrome tự dựng: bo góc, titlebar xám có ba chấm kiểu traffic-light và bóng mềm bốn phía. Đặt `FRAME=false` để bỏ bước xử lý khung và lấy ảnh viewport thô.

```bash
# Có khung (mặc định)
npm run screenshotter

# Ảnh thô, không khung
FRAME=false npm run screenshotter
```

Khung được tạo hoàn toàn local bằng template HTML inline và ảnh Playwright của kết quả đã compose. Không gọi dịch vụ ngoài, không gửi nội dung ảnh ra khỏi máy.

Thanh địa chỉ trong khung chỉ hiển thị URL path của ảnh, ví dụ `/hardware/1/edit`, dưới dạng pill bo góc ở giữa chrome. Không render full URL. Cách này giữ ảnh sạch dù host test local là `hsb-it.test`, tunnel ngrok hay hostname khác, đồng thời tránh lộ hostname local trong ảnh công bố.

## Chế độ chụp một ảnh ad-hoc

Bỏ qua full walkthrough và chỉ chụp một URL với một user cụ thể. Hữu ích khi cần tạo lại một ảnh cũ, hoặc chụp một trang ngoài danh mục cho nhu cầu một lần.

```bash
# Chỉ một URL, dùng USERNAME mặc định (admin)
node .screenshotter/src/screenshotter.mjs --one /hardware

# Với một vai trò cụ thể
node .screenshotter/src/screenshotter.mjs --one /hardware/create --as assetmgr

# Với tên output tùy chỉnh
node .screenshotter/src/screenshotter.mjs --one /licenses/5 --as licensemgr --name license-detail

# Chụp một tab cụ thể trên trang view
node .screenshotter/src/screenshotter.mjs --one /hardware/1 --tab licenses
node .screenshotter/src/screenshotter.mjs --one /hardware/1 --tab "components"

# Dark mode, không khung
COLOR_SCHEME=dark FRAME=false node .screenshotter/src/screenshotter.mjs --one /hardware --as admin
```

Ảnh ad-hoc được lưu vào `.screenshotter/screenshots/adhoc/{username}-{name}-{timestamp}.png`, hoặc có thêm `-tab-{slug}` trong tên file khi dùng `--tab`. Thư mục `adhoc/` được giữ lại qua các lần full walkthrough để ảnh một lần không bị xóa, và mỗi ảnh đều có timestamp để chụp lại cùng URL không ghi đè ảnh cũ.

Tham số:

- `--one <path>`: bắt buộc, URL path cần chụp, có hoặc không có dấu `/` đầu.
- `--as <username>`: user dùng để đăng nhập. Mặc định lấy từ env `USERNAME`, nếu không có thì là `admin`. User demo hợp lệ gồm `admin`, `hsb`, `assetmgr`, `licensemgr`, `accessorymgr`, `consumablemgr`, `componentmgr`, `usermgr`, v.v.
- `--name <slug>`: slug tên file. Mặc định lấy URL path và đổi `/` thành `__`. Timestamp được tự động thêm.
- `--tab <label>`: tìm không phân biệt hoa thường theo substring trên nhãn Bootstrap tab hiển thị ở trang đích. `--tab licenses` khớp cả `Licenses` và `Licenses (5)`. Nếu không có tab phù hợp, run dừng và in danh sách tab hiện có.

## Kết quả của một lần chạy full

Mọi PNG tạo ra dùng quy ước tên `{section}/{username}-{section}-{page}-{timestamp}.png`, nên sắp xếp alphabet sẽ nhóm theo section, rồi vai trò, rồi lần chạy. Section xuất hiện cả trong tên thư mục và tên file, để một PNG đứng riêng trong Discord, PR comment hoặc ticket vẫn tự mô tả được.

Ví dụ nội dung thư mục section sau một lần chạy với `admin` và các resource manager:

```text
.screenshotter/screenshots/assets/
├── admin-assets-index-2026-07-21-141230.png
├── admin-assets-view-2026-07-21-141230.png
├── admin-assets-edit-2026-07-21-141230.png
├── admin-assets-edit-submitted-2026-07-21-141230.png
├── admin-assets-checkout-2026-07-21-141230.png
├── admin-assets-create-2026-07-21-141230.png
├── admin-assets-create-status-dropdown-2026-07-21-141230.png
├── admin-assets-bulk-checkout-2026-07-21-141230.png
├── admin-assets-bulk-checkin-2026-07-21-141230.png
├── assetmgr-assets-index-2026-07-21-141230.png
├── assetmgr-assets-view-2026-07-21-141230.png
├── assetmgr-assets-edit-2026-07-21-141230.png
├── assetmgr-assets-edit-submitted-2026-07-21-141230.png
├── assetmgr-assets-checkout-2026-07-21-141230.png
└── assetmgr-assets-create-2026-07-21-141230.png
```

Phạm vi mỗi section:

- **Index, view, edit, edit-submitted** cho mọi object chính.
- **Toggle info-panel** trên trang view có panel: ảnh `-view` chụp trạng thái mặc định, ảnh phụ `-view-info-collapsed` hoặc `-view-info-expanded` chụp trạng thái còn lại.
- **Checkout** cho các đối tượng có thể checkout: tài sản, license, phụ kiện, vật tư, linh kiện, kit.
- **Create form** khi hữu ích, ví dụ tài sản, người dùng, license.
- **Bulk-checkout và bulk-checkin** trong section assets (`/hardware/bulkcheckout`, `/hardware/bulkcheckin`).
- **Ảnh tương tác**, hiện chủ yếu là dropdown trạng thái đang mở trên form tạo tài sản.

Các section mặc định: `assets`, `licenses`, `accessories`, `consumables`, `components`, `users`, `models`, `categories`, `manufacturers`, `suppliers`, `locations`, `departments`, `kits`, `companies`, `statuslabels`, `depreciations`, `custom-fields`, `fieldsets`, `maintenance-types`, `dashboard`, `settings`, `reports`.

Ngoài ra, script còn chạy walkthrough theo góc nhìn của các resource manager đã seed quyền đúng cho từng resource: `assetmgr`, `licensemgr`, `accessorymgr`, `consumablemgr`, `componentmgr`, `usermgr`. Ảnh của các user này nằm cùng thư mục section với ảnh admin để dễ so sánh.

Cuối cùng, superuser sẽ quét mọi route GET không có parameter trong app và lưu dưới `all-routes/` để kiểm tra nhanh bằng mắt. Đặt `ALL_ROUTES=false` để bỏ qua lượt này.

## Thêm ảnh chụp mới

Mỗi block trong script được viết rõ ràng. Thêm trang hoặc tương tác mới nghĩa là thêm một block nhỏ: điều hướng, chờ trạng thái cần thiết, rồi gọi helper `shot(name)`.

```js
await page.goto(`${BASE_URL}/consumables`);
await waitForTable();
await shot(`consumables/${USERNAME}-consumables-index`);
```

Với ảnh tương tác như dropdown đang mở, modal đang mở hoặc trạng thái giữa flow, hãy click trigger, chờ element mục tiêu xuất hiện rồi chụp. Helper `shot()` gọi `page.waitForLoadState('networkidle')` để giảm nhiễu do render async của AdminLTE; helper `waitForTable()` chờ overlay loading của bootstrap-table biến mất trước khi chụp trang bảng.

Nếu block mới bao phủ một object chính gồm list, detail và edit page, hãy thêm entry vào mảng config `firstClassObjects` gần đầu phần walkthrough để vòng lặp tự tạo ba ảnh. Thêm `hasCheckout: true` nếu entity có checkout; thêm `hasView: false` nếu entity không có trang detail.

## Chụp cùng một trang bằng nhiều user

Script có helper `asUser(username, fn)` để xóa cookie, đăng nhập bằng user chỉ định, chạy callback, rồi mọi ảnh trong block sẽ được chụp trong session đó.

```js
await asUser('viewer', async () => {
    await page.goto(`${BASE_URL}/hardware`);
    await waitForTable();
    await shot('assets/viewer-assets-index');
});
```

User dùng trong các block này phải tồn tại trong database đã seed. Demo seeder có sáu resource-manager user (`assetmgr`, `licensemgr`, `accessorymgr`, `consumablemgr`, `componentmgr`, `usermgr`) cùng các tài khoản chuẩn `admin` và `hsb`. Nếu cần vai trò chi tiết hơn cho tài liệu so sánh, hãy bổ sung bằng state của `UserFactory`.

## Kỳ vọng workflow

Khi PR thêm hoặc thay đổi đáng kể một màn hình, modal, form, dropdown hoặc tương tác người dùng, hãy thêm hoặc cập nhật block tương ứng trong `.screenshotter/src/screenshotter.mjs` trong cùng PR. Nếu tài liệu downstream tham chiếu một filename ảnh cụ thể và block tạo ảnh đó bị xóa, tài liệu sẽ hỏng rõ ràng ở lần regenerate tiếp theo; đó là vòng phản hồi mong muốn.

## Ghi chú triển khai

Script dùng đuôi `.mjs` thay vì `.js` để luôn chạy như ES module, bất kể root `package.json` cấu hình thế nào. Phương án khác là thêm `"type": "module"` vào `package.json`, nhưng cách đó sẽ chuyển mọi file `.js` khác trong repo sang ESM cùng lúc và tạo phạm vi thay đổi lớn hơn nhu cầu của script.

Source và output được tách riêng (`.screenshotter/src/` và `.screenshotter/screenshots/`) để logic xóa trước khi chạy không bao giờ xóa nhầm chính script. Phiên bản cũ từng để chung một thư mục và dùng skip list theo filename; cách đó có thể tự xóa script đang chạy ngay khi ai đó đổi tên thư mục output.