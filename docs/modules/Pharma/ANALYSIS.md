# Phân tích module Pharma

Ngày rà soát: 2026-07-14  
Phạm vi: `Modules/Pharma`  
Trạng thái: **Import/export của Medicine, DrugBidAward và SupplierTracking đã được xác nhận và triển khai**

## 1. Kết luận nhanh

Module Pharma quản lý ba nhóm dữ liệu:

1. Hồ sơ thuốc (`pharma_medicines`).
2. Kết quả trúng thầu (`pharma_drug_bid_awards`).
3. Theo dõi nhà cung cấp (`pharma_supplier_trackings`).

Module đã có CRUD, service và nhiều luồng import/export khác nhau. Riêng theo dõi nhà cung cấp đã sử dụng `BaseImportExportService` và giao diện dùng chung, nhưng luồng cũ vẫn tồn tại song song. Điều này làm cho mapping, validation, xử lý trùng và kết quả export không đồng nhất.

Migration và model đã có đầy đủ. Medicine dùng `storage/app/import/hssp.xlsx` làm file chuẩn A–U; DrugBidAward dùng cấu trúc A–L từ `storage/app/import/thong-tin-trung-thau.csv`. Cả hai dùng `update_or_create`, giữ dữ liệu cũ khi ô import rỗng và tiếp tục xử lý khi một dòng lỗi.

## 2. Kiểm tra dữ liệu đầu vào

| Thành phần | Trạng thái | Ghi chú |
|---|---|---|
| Excel/CSV Medicine và DrugBidAward | Có | `hssp.xlsx` và `thong-tin-trung-thau.csv` |
| Excel SupplierTracking | Đã đối chiếu | File nguồn 17 cột được nâng thành template tiếng Việt A–V gồm 22 cột |
| Migration thuốc | Có | `2026_05_21_145242_create_medicines_table.php` |
| Migration trúng thầu | Có | `2026_05_22_135028_create_drug_bid_awards_table.php` |
| Migration nhà cung cấp | Có | `2026_05_23_141810_create_supplier_trackings_table.php` |
| Model | Có | `Medicine`, `DrugBidAward`, `SupplierTracking` |
| Mapping | Đã xác nhận | Medicine A–U, DrugBidAward A–L, SupplierTracking A–V |
| Import mode | Đã xác nhận | `update_or_create`, không ghi đè null, partial theo dòng |

## 3. Kiến trúc hiện tại

Luồng trang quản trị:

```text
Route → Controller → Page Blade → Livewire → Service → Model → Database
```

Luồng import/export mục tiêu:

```text
Page Blade
→ shared.import-export.panel
→ Modules/Pharma/Services/ImportExport.php
→ BaseImportExportService
→ Import/Export class của Pharma (khi cần tách)
→ Model
```

Các điểm chưa đúng hoặc chưa thống nhất:

- `MedicineService`, `MedicineImportService`, `DrugBidAwardService`, `SupplierTrackingService` và `ImportExport` đều chứa một phần logic import/export.
- `SupplierTrackings/Index.php` và Blade tương ứng còn luồng riêng bên cạnh shared panel.
- Một số thao tác query, filesystem và xử lý file vẫn nằm trong Livewire/service cũ.
- Import thuốc và trúng thầu dùng CSV theo vị trí cột, trong khi nhà cung cấp dùng FastExcel/shared foundation.
- Chưa có một service import/export riêng, thống nhất cho từng aggregate.

## 4. Route và bảo mật

### Web

Các route web nằm dưới `admin/pharma`, dùng `web` và `auth:admin`. Có ba nhóm route: `hssp`, `drug-bid-awards` và `supplier-trackings`.

Lỗi nghiêm trọng:

- Route `admin.pharma.supplier-trackings.import-export` gọi `SupplierTrackingController@importExport`, nhưng controller không có method này.
- Chưa thấy permission nghiệp vụ được áp dụng cho view/create/edit/delete/import/export.

### API

`Modules/Pharma/routes/api.php` công khai `GET /pharma`, nhưng `Api\PharmaController` không có `index()`. Route này vừa không có authentication vừa hỏng hợp đồng controller.

Khuyến nghị P0: nếu chưa có nhu cầu API đã được xác nhận, loại route khỏi đăng ký. Nếu cần API, phải bổ sung guard, authorization, controller mỏng và test.

## 5. Phân tích database và model

### 5.1. Thuốc

Table: `pharma_medicines`  
Model: `Modules\Pharma\Models\Medicine`

Business key hiện có trong DB:

```text
registration_number + packaging_specification
```

Các trường bắt buộc theo migration:

- `active_ingredients`, `concentration`, `name`, `dosage_form`.
- `route_of_administration`, `unit`, `packaging_specification`.
- `registration_number`, `shelf_life`, `registered_company`.
- `manufacturing_company`, `manufacturing_country`.

Các trường nullable: số thứ tự/nhóm thông tư, hai ngày chứng nhận, giá kê khai, link hồ sơ, ghi chú. `is_special_control` mặc định `false`.

Vấn đề model:

- Model dùng `$guarded = ['id']`, không khai báo `$fillable`.
- Master prompt quy định export mặc định theo `$fillable`; do đó chưa thể suy ra danh sách export chuẩn từ model hiện tại.
- Chưa có `$exceptExport`.

### 5.2. Kết quả trúng thầu

Table: `pharma_drug_bid_awards`  
Model: `Modules\Pharma\Models\DrugBidAward`

Business key trong DB:

```text
bidding_notice_code + medicine_name + winning_company_name
```

`medicine_id` nullable và `SET NULL` khi thuốc bị xóa. Các trường nghiệp vụ còn lại phần lớn bắt buộc. Model có `$fillable`, `$casts` và quan hệ `medicine()` phù hợp với schema.

Điểm cần xác nhận:

- Import có bắt buộc liên kết được `medicine_id` hay chỉ lưu snapshot `medicine_name`.
- Khi đã có bản ghi trùng business key, cập nhật trường nào và trường nào không được ghi đè bởi giá trị rỗng.
- File CSV hiện tại không phải bằng chứng cho định dạng Excel chuẩn tương lai.

### 5.3. Theo dõi nhà cung cấp

Table: `pharma_supplier_trackings`  
Model: `Modules\Pharma\Models\SupplierTracking`

Model có `$fillable`, `$casts`, quan hệ `medicine()` và `$exceptExport` gồm:

```text
contract_url, status, note
```

Các field hệ thống phải tự tính lại, không tin dữ liệu Excel:

```text
invoice_difference_amount = invoice_price - import_price
invoice_difference_fee    = invoice_difference_amount × invoice_difference_percent / 100
cost_price                = import_price + invoice_difference_fee
gross_profit_percent      = (selling_price - cost_price) / selling_price × 100
```

Business key chưa được ràng buộc unique trong migration. Service dùng chung hiện đề xuất:

```text
medicine_id + supplier_name + working_date
```

Key này cần nghiệp vụ xác nhận trước khi dùng cho `update_or_create`.

Trạng thái hiện không nhất quán:

- Migration mặc định `active`.
- Shared service cho phép `active`, `inactive`, `draft`, `expired`.
- Các form/luồng cũ cần được đối chiếu để chốt một danh sách duy nhất.

## 6. Phân tích import/export hiện tại

### Thuốc

- `MedicineImportService` và `ImportMedicineCommand` xử lý import theo luồng riêng.
- `MedicineService` có import/export CSV riêng.
- Chưa sử dụng một contract/report chung với shared foundation.
- Chưa có file Excel mẫu để xác nhận mapping và chuẩn hóa null/date/money/boolean.

### Trúng thầu

- `DrugBidAwardService` đọc/ghi CSV dấu `;`.
- Duplicate hiện dựa trên đúng composite key của migration.
- Export có eager load thuốc và chunk, nhưng format/cột không đi qua shared foundation.
- Validation và báo lỗi theo dòng chưa theo report chuẩn.

### Theo dõi nhà cung cấp

`Modules/Pharma/Services/ImportExport.php`:

- Extends `BaseImportExportService`.
- Dùng mode `update_or_create`.
- Map vị trí A–V.
- Tìm thuốc theo số đăng ký, sau đó fallback theo tên.
- Tự tính lại bốn field công thức.
- Có template mẫu và `$exceptExport`.

Các rủi ro còn lại:

- Mapping A–V chưa được xác minh bằng file thật.
- Fallback theo tên thuốc có thể chọn sai khi tên không duy nhất.
- `exportRows()` bỏ qua `$filters` và gọi `get()` toàn bộ dữ liệu.
- Service truy vấn model trực tiếp cho từng dòng import, có nguy cơ N+1.
- `contract_url` chỉ validate string thay vì URL.
- Các field công thức vẫn có trong column mapping dù không được tin khi persist; template cần ghi rõ chỉ để tham khảo.
- Luồng cũ trong `SupplierTrackingService` vẫn tồn tại và có cách parse số khác shared service.

## 7. Mapping đề xuất (chưa phê duyệt)

### Theo dõi nhà cung cấp

| Cột | Field | Import | Quy tắc |
|---|---|---:|---|
| A | `working_date` | Có | Date, nullable |
| B | `medicine_name` | Tra cứu | Không persist trực tiếp |
| C | `registration_number` | Tra cứu | Ưu tiên để tìm thuốc |
| D | `supplier_name` | Có | Required |
| E | `supplier_representative` | Có | Nullable string |
| F | `area` | Có | Nullable string |
| G | `import_price` | Có | Decimal |
| H | `selling_price` | Có | Decimal |
| I | `invoice_price` | Có | Decimal |
| J | `invoice_difference_amount` | Không | Hệ thống tự tính |
| K | `invoice_difference_percent` | Có | Decimal/percent |
| L | `invoice_difference_fee` | Không | Hệ thống tự tính |
| M | `cost_price` | Không | Hệ thống tự tính |
| N | `gross_profit_percent` | Không | Hệ thống tự tính |
| O | `committed_quantity` | Có | Decimal, nullable |
| P | `unit` | Có | Nullable string |
| Q | `deposit_amount` | Có | Decimal, nullable |
| R | `start_date` | Có | Date, nullable |
| S | `end_date` | Có | Date, nullable |
| T | `contract_url` | Có | URL, nullable |
| U | `status` | Có | Enum sau khi xác nhận |
| V | `note` | Có | Nullable string |

Mapping thuốc và trúng thầu chưa được đề xuất vì thiếu Excel mẫu. Không nên lấy thứ tự CSV cũ làm chuẩn ngầm định.

## 8. Các vấn đề theo mức ưu tiên

### P0 — chặn lỗi và rủi ro truy cập

- Gỡ hoặc bảo vệ API route hỏng.
- Sửa/gỡ route import-export gọi method không tồn tại và ràng buộc `{id}`.
- Bổ sung authorization server-side cho page và mọi action thay đổi/xuất dữ liệu.
- Không tin các ID do Livewire gửi lên khi sửa, xóa hoặc xóa hàng loạt.

### P1 — tính đúng và thống nhất kiến trúc

- Chốt một luồng import/export cho mỗi loại dữ liệu.
- Chỉ dùng `shared.import-export.panel` cho UI.
- Tách import/export class khi service vượt quá một trách nhiệm rõ ràng.
- Chốt status, unique key, import mode, null-overwrite và rollback strategy.
- Áp dụng filter cho export; dùng lazy/chunk cho dữ liệu lớn.
- Đưa transaction, query, công thức và bulk action vào service.
- Thống nhất normalization số/ngày/URL và report lỗi theo dòng.

### P2 — chất lượng và bảo trì

- Xóa scaffold/luồng cũ sau khi có test chứng minh không còn caller.
- Chuẩn hóa Tailwind, liên kết ngoài an toàn và hiển thị ngày theo cast.
- Bổ sung route, authorization, service, import và export tests.

## 9. Thông tin cần xác nhận trước khi viết code import/export

Vui lòng cung cấp hoặc xác nhận:

1. File Excel mẫu/dữ liệu thật cho từng loại cần import.
2. Mapping theo header hay theo vị trí A/B/C.
3. Import mode: `create_only`, `update_or_create` hay `skip_duplicate`.
4. Business key cho theo dõi nhà cung cấp.
5. Danh sách status hợp lệ.
6. Có cho phép fallback tìm thuốc theo tên hay chỉ theo số đăng ký.
7. Quy tắc không ghi đè null/rỗng khi update.
8. Import partial theo dòng hay rollback toàn file.
9. Cột export cuối cùng và các field cần loại trừ.

Đến khi các mục trên được xác nhận, không triển khai lại import/export production.
