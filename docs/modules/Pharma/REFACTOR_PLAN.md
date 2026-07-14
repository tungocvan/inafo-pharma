# Kế hoạch refactor module Pharma

Ngày cập nhật: 2026-07-14  
Phạm vi: `Modules/Pharma`  
Nguồn: mã nguồn hiện tại và `ANALYSIS.md`

## 1. Mục tiêu

- Loại bỏ route hỏng và bề mặt truy cập không được bảo vệ.
- Mỗi nhóm dữ liệu chỉ có một luồng import/export chính thức.
- Dùng `shared.import-export.panel` và `BaseImportExportService`.
- Controller/Blade/Livewire không chứa query hoặc business logic.
- Import có mapping, validation, dry-run, duplicate policy và report thống nhất.
- Export áp dụng filter, giới hạn bộ nhớ và tuân thủ `$fillable`/`$exceptExport`.

## 2. Điều kiện khởi động

Không bắt đầu hạng mục thay thế import cho đến khi có:

- Excel mẫu hoặc dữ liệu thật.
- Migration và model (đã có).
- Xác nhận mapping header hoặc A/B/C.
- Unique key, import mode, null-overwrite và rollback policy.

## 3. Lộ trình triển khai

### Giai đoạn 0 — ổn định route và authorization

#### P0-01: xử lý API route

- Nếu không cần API: bỏ đăng ký `GET /pharma` và dọn controller scaffold sau khi test.
- Nếu cần API: thêm guard được dự án chấp thuận, permission, action mỏng và service.

Hoàn thành khi route không còn public/hỏng và có regression test.

#### P0-02: sửa route import/export nhà cung cấp

- Chọn một trong hai: giữ panel ở trang index hoặc tạo page riêng.
- Nếu giữ page riêng, thêm method controller và page Blade hợp lệ.
- Ràng buộc `{id}` là số để hợp đồng route rõ ràng và từ chối ID không hợp lệ sớm.

Hoàn thành khi mọi route trỏ tới action tồn tại.

#### P0-03: thêm authorization

Permission tối thiểu đề xuất:

```text
view_pharma
create_pharma
edit_pharma
delete_pharma
import_pharma
export_pharma
```

Áp dụng tại route/controller cho page access và trong Livewire/shared panel cho action. Kiểm tra lại từng ID ở server cho edit/delete/bulk delete/export selected.

### Giai đoạn 1 — thống nhất nghiệp vụ

#### P1-01: chốt contract import/export

Cho từng aggregate, ghi rõ:

- File mẫu, sheet và dòng header.
- Mapping field.
- Required/nullable/type/range.
- Unique key và import mode.
- Quy tắc update giá trị rỗng.
- Partial import hay atomic import.
- Danh sách cột export và format.

#### P1-02: chuẩn hóa model metadata

- Đổi `Medicine` sang `$fillable` rõ ràng nếu export mặc định theo master prompt.
- Chỉ khai báo `$exceptExport` cho field thực sự không được xuất.
- Chốt cast và relation của cả ba model.
- Không sửa migration lịch sử đã chạy; tạo migration mới cho constraint/index cần bổ sung.

#### P1-03: chốt invariant nhà cung cấp

- Xác nhận unique key đề xuất `medicine_id + supplier_name + working_date`.
- Xác nhận status hợp lệ.
- Giữ bốn field công thức là server-owned.
- Xác nhận lookup thuốc theo số đăng ký và hành vi fallback.

### Giai đoạn 2 — xây luồng dùng chung

#### P1-04: nhà cung cấp

- Giữ `Services/ImportExport.php` làm entry point mỏng.
- Khi service lớn, tách thành `Import/SupplierTrackingImport.php` và `Export/SupplierTrackingExport.php` cùng mapper/normalizer cần thiết.
- Bỏ luồng import/export cũ khỏi Livewire và `SupplierTrackingService` sau khi test tương đương.
- Áp dụng filter thật cho export và thay `get()` toàn bộ bằng lazy/chunk.
- Cache/preload lookup thuốc trong phạm vi import để tránh query từng dòng.

#### P1-05: thuốc

- Chỉ triển khai sau khi có Excel mẫu.
- Hợp nhất `MedicineImportService`, import trong `MedicineService` và console command.
- Console command, nếu giữ, chỉ là adapter gọi canonical service.
- Unique key mặc định đề xuất: `registration_number + packaging_specification`.

#### P1-06: trúng thầu

- Chỉ triển khai sau khi có Excel mẫu.
- Thay CSV service riêng bằng shared foundation hoặc adapter thống nhất.
- Unique key mặc định đề xuất: `bidding_notice_code + medicine_name + winning_company_name`.
- Chốt cách liên kết `medicine_id` và null-overwrite.

### Giai đoạn 3 — service và tính toàn vẹn

#### P1-07: đưa business logic về service

- Controller chỉ trả page.
- Livewire chỉ quản lý state, validate UI, authorize và gọi service.
- Service sở hữu query, transaction, bulk action và công thức.
- Dùng dependency injection nhất quán, không gọi `app()` lặp lại.

#### P1-08: transaction và error handling

- CRUD và bulk mutation chạy trong transaction khi có nhiều bước.
- Import tuân theo rollback policy đã xác nhận.
- Không flash exception thô cho người dùng.
- Report import trả tổng dòng, thành công, bỏ qua, lỗi và chi tiết sheet/row/column/value/reason.

#### P1-09: validation

- Thuốc: composite unique `registration_number + packaging_specification`, bỏ qua record hiện tại khi edit.
- Trúng thầu: composite unique theo key của DB, bỏ qua record hiện tại.
- Nhà cung cấp: validate medicine, supplier, money, percent, date range, URL và status.
- Upload: extension/MIME/kích thước rõ ràng trong shared panel.

### Giai đoạn 4 — hiệu năng và UI

#### P1-10: query và dữ liệu lớn

- Eager load `medicine` khi list/export cần relation.
- Server-side pagination; chỉ cho `10`, `25`, `50`, `100`.
- Nếu giữ `All`, phải có hard cap và cảnh báo.
- Export dùng lazy/chunk; queue chỉ khi đã có authorization context và progress/failure strategy.
- Chỉ thêm index sau khi xác nhận filter/sort thực tế.

#### P1-11: UI

- Dùng duy nhất `shared.import-export.panel`.
- Dùng `x-select-search` cho danh sách thuốc dài.
- Page Blade chỉ mount component và dùng `Admin::layouts.master`.
- Dùng Tailwind hiện hành; không thêm Bootstrap/jQuery mới.
- Link ngoài có `rel="noopener noreferrer"`; ngày hiển thị từ cast.

### Giai đoạn 5 — dọn dẹp và tài liệu

- Xóa luồng/scaffold không dùng sau khi test pass.
- Cập nhật `Modules/Pharma/readme.md`.
- Chốt hoặc bỏ supplier tracking show flow.
- Ghi lại định dạng template, alias, unique key, status và rollback policy.

## 4. Kế hoạch kiểm thử

### Route/authorization

- Route boot không tham chiếu method thiếu.
- API bị gỡ hoặc yêu cầu guard.
- Admin thiếu quyền bị từ chối ở page và action.
- ID bị sửa trên client không thể edit/delete/export trái phép.

### Service

- Filter/search/pagination đúng.
- Công thức nhà cung cấp đúng ở create, update và import.
- Transaction rollback khi lỗi.
- Bulk delete xử lý ID không hợp lệ theo fail-closed.

### Import

- Header/A-B-C mapping đúng với fixture đã duyệt.
- Normalize tiền, ngày, boolean/status và chuỗi rỗng.
- Duplicate policy và null-overwrite đúng.
- Dry-run không ghi DB.
- Report lỗi đúng dòng/cột.
- Derived field không bị Excel ghi đè.

### Export

- Filter được áp dụng.
- `$fillable` và `$exceptExport` được tôn trọng.
- Relation được eager load.
- Dữ liệu lớn không nạp toàn bộ vào bộ nhớ.
- Template đánh dấu field bắt buộc, tùy chọn và tự tính.

## 5. Thứ tự pull request đề xuất

1. Route/API/authorization và regression tests.
2. Model metadata, validation và transaction.
3. Supplier tracking shared import/export.
4. Medicine import/export sau khi duyệt Excel.
5. Drug bid award import/export sau khi duyệt Excel.
6. Hiệu năng, UI cleanup và xóa legacy.

Không gộp xóa legacy vào cùng thay đổi đầu tiên; chỉ xóa sau khi luồng mới có test tương đương.

## 6. Definition of Done

- Không còn route hỏng hoặc public ngoài chủ đích.
- Mọi action có authorization server-side.
- Mỗi aggregate có tối đa một canonical import/export service.
- Shared panel không nhận model trực tiếp, chỉ nhận `serviceClass`.
- Import có dry-run, report chuẩn và policy trùng đã duyệt.
- Export đúng filter và an toàn với dữ liệu lớn.
- Test route, authorization, service, import và export đều pass.
- Tài liệu phản ánh đúng mapping/template production.
