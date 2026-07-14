# Đặc tả xây dựng lại module Pharma

Phiên bản: 2026-07-14  
Trạng thái: **Import/export của cả ba feature Pharma đã triển khai theo mapping được duyệt**

## 1. Phạm vi

Pharma tiếp tục là module sở hữu:

- Danh mục thuốc.
- Kết quả thuốc trúng thầu.
- Theo dõi nhà cung cấp, giá, chi phí, lợi nhuận và hợp đồng.
- Import/export cho ba nhóm dữ liệu trên.

Không tạo business code Pharma trong `app/Models`, `app/Http` hoặc `app/Services`.

## 2. Kiến trúc bắt buộc

```text
Route
→ Controller
→ Page Blade
→ Livewire PHP
→ Livewire Blade
→ Shared UI
→ Pharma Service
→ Pharma Import/Export
→ Shared Base Service
→ Model
→ Database
```

Quy tắc:

- Controller không query.
- Blade không query.
- Livewire không chứa business logic hoặc thao tác filesystem.
- Service sở hữu query, transaction và invariant.
- UI import/export dùng `shared.import-export.panel` với `serviceClass`.
- Không copy logic normalize/report/storage từ `Modules/Shared`.

## 3. Cấu trúc đích

```text
Modules/Pharma/
├── Http/Controllers/
├── Livewire/
│   ├── Medicine/
│   ├── DrugBidAward/
│   └── SupplierTrackings/
├── Models/
├── Services/
│   ├── MedicineService.php
│   ├── DrugBidAwardService.php
│   ├── SupplierTrackingService.php
│   └── ImportExport.php
├── Import/                  # tạo khi logic đủ lớn
│   └── SupplierTrackingImport.php
├── Export/                  # tạo khi logic đủ lớn
│   └── SupplierTrackingExport.php
├── database/migrations/
├── resources/views/
└── routes/
```

Khi bổ sung import/export cho thuốc và trúng thầu, ưu tiên service riêng theo feature hoặc một facade có cấu hình feature rõ ràng; không nhồi ba schema khác nhau vào một class dài.

## 4. Hợp đồng dữ liệu

### 4.1. Medicine

Table: `pharma_medicines`

Unique key:

```text
registration_number + packaging_specification
```

Date fields: `visa_validity_date`, `gmp_certification_date`.  
Money field: `declared_price`.  
Boolean field: `is_special_control`.

Yêu cầu trước khi code import:

- Cung cấp Excel mẫu.
- Chốt mapping.
- Chuyển model sang `$fillable` rõ ràng để xác định export mặc định.
- Chốt cách update khi ô Excel rỗng.

### 4.2. DrugBidAward

Table: `pharma_drug_bid_awards`

Unique key:

```text
bidding_notice_code + medicine_name + winning_company_name
```

Relationship: `medicine_id` nullable, belongs to `Medicine`.  
Date field: `decision_date`.  
Money field: `unit_price`.  
Integer fields: `quantity`, `contract_duration_months`.

Yêu cầu trước khi code import:

- Cung cấp Excel mẫu.
- Chốt có bắt buộc match thuốc hay không.
- Chốt duplicate mode và null-overwrite.

### 4.3. SupplierTracking

Table: `pharma_supplier_trackings`

Unique key đã duyệt:

```text
medicine_id + supplier_name + working_date
```

Derived fields:

- `invoice_difference_amount`.
- `invoice_difference_fee`.
- `cost_price`.
- `gross_profit_percent`.

Các field này chỉ export để tham khảo và không nhận giá trị persist từ Excel.

Export mặc định dựa trên `$fillable`, loại:

```text
contract_url, status, note
```

Việc loại ba field này cần được nghiệp vụ xác nhận lần cuối.

## 5. Đặc tả import

### Input

- Định dạng: `xlsx`, `xls` hoặc `csv` theo khả năng chính thức của shared foundation.
- Giới hạn dung lượng phải cấu hình rõ trên shared panel.
- Phải hỗ trợ dry-run.
- Mapping dùng header alias hoặc A/B/C, không kết hợp ngầm định.

### Pipeline

```text
Đọc file
→ xác định sheet/header
→ map về DB field
→ normalize
→ resolve relationship
→ validate row
→ tính derived fields
→ áp dụng duplicate policy
→ persist trong transaction đã chọn
→ trả report
```

### Normalize

- String: trim; chuỗi rỗng thành null khi field cho phép.
- Money/number: hỗ trợ `1,000,000`, `1.000.000`, `1000000`, `1 000 000` theo quy tắc không mơ hồ.
- Date: hỗ trợ `dd/mm/yyyy`, `yyyy-mm-dd` và Excel serial.
- Boolean: hỗ trợ `1/0`, `true/false`, `yes/no`, `có/không`.
- URL: validate URL, không chỉ validate string.

### Validation

- Validate sau mapping và normalization.
- Required/type/range/enum/foreign key/unique phải trả lỗi theo dòng.
- Không để exception thô xuất hiện trên UI.

### Duplicate mode

Chỉ cho phép mode đã duyệt:

```text
create_only | update_or_create | skip_duplicate
```

Không dùng `replace` nếu không có xác nhận riêng.

### Report

```php
[
    'success' => true,
    'total_rows' => 0,
    'success_rows' => 0,
    'error_rows' => 0,
    'skipped_rows' => 0,
    'errors' => [
        [
            'sheet' => 'Sheet1',
            'row' => 2,
            'column' => 'supplier_name',
            'value' => null,
            'reason' => 'Nhà cung cấp không được để trống.',
        ],
    ],
    'debug' => [],
]
```

## 6. Đặc tả export

- Query nhận filter đang hoạt động từ UI.
- Export mặc định theo `$fillable`, trừ `$exceptExport`.
- Cột relation và derived field phải được khai báo rõ trong mapper.
- Date xuất theo format đã chốt; money là numeric cell, không lưu chuỗi tiền tệ.
- Dữ liệu lớn dùng lazy/chunk; không `get()` toàn bảng.
- File lưu qua shared export storage và trả URL tải hợp lệ.
- Export selected phải kiểm tra quyền trên từng ID.

## 7. Template

Template phải có:

- Header chính thức.
- Một hoặc vài dòng mẫu an toàn.
- Ghi chú required/optional.
- Danh sách giá trị status/boolean hợp lệ.
- Định dạng ngày và tiền.
- Đánh dấu field hệ thống tự tính.
- Không đưa `id` DB làm business key mặc định.

Template A–V đang có cho SupplierTracking chỉ là đề xuất cho đến khi đối chiếu file thật.

## 8. UI và Livewire

Mount panel theo mẫu:

```blade
@livewire('shared.import-export.panel', [
    'serviceClass' => \Modules\Pharma\Services\ImportExport::class,
    'title' => 'Import / Export theo dõi nhà cung cấp',
    'description' => 'Nhập dữ liệu từ Excel hoặc xuất dữ liệu hiện tại.',
])
```

Shared panel chỉ quản lý upload, mode, dry-run, loading và hiển thị report. Panel không query model, map field, validate từng row hoặc persist dữ liệu.

## 9. Authorization

Mọi page/action phải kiểm tra quyền phía server:

- View.
- Create/update.
- Delete/bulk delete.
- Import.
- Export/export selected.

Ẩn hoặc disable button không được xem là authorization.

## 10. Transaction và idempotency

- CRUD nhiều bước và bulk action dùng transaction.
- `update_or_create` chỉ dùng business key đã duyệt.
- Retry không tạo bản ghi trùng.
- Chiến lược import toàn file hay partial theo dòng phải được xác nhận và có test.
- Dry-run không được tạo/sửa/xóa dữ liệu hoặc để lại file tạm ngoài chủ đích.

## 11. Tiêu chí nghiệm thu

- Không còn route trỏ tới method thiếu.
- Không có Pharma API public ngoài chủ đích.
- Shared panel là UI import/export duy nhất cho feature đã chuyển đổi.
- Mapping khớp file mẫu đã duyệt.
- Derived field không bị import ghi đè.
- Duplicate, null-overwrite và rollback đúng tài liệu.
- Export áp dụng filter và không nạp toàn bộ dataset.
- Report lỗi xác định đúng sheet/dòng/cột.
- Test authorization, import, export, dry-run và dữ liệu lớn đều pass.

## 12. Điểm đang chờ xác nhận

Các quyết định đã chốt:

1. SupplierTracking dùng mapping A–V với header tiếng Việt.
2. Unique key: `medicine_id + supplier_name + working_date`.
3. Status: `active`, `completed`, `paused`, `cancelled`.
4. Lookup thuốc ưu tiên số đăng ký, fallback theo tên.
5. Import mode `update_or_create`; ô rỗng giữ dữ liệu cũ.
6. Lỗi theo dòng không rollback các dòng hợp lệ.
