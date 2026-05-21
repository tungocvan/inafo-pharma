<?php

namespace Modules\Pharma\Models;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    // Đã cập nhật tên bảng theo yêu cầu
    protected $table = 'pharma_medicines';

    // Cho phép mass assignment tất cả trừ id
    protected $guarded = ['id'];

    // Ép kiểu dữ liệu (Casting) để đảm bảo toàn vẹn dữ liệu
    protected $casts = [
        'visa_validity_date'     => 'date',
        'gmp_certification_date' => 'date',
        'is_special_control'     => 'boolean',
        'declared_price'         => 'decimal:2',
    ];
}
