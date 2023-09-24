<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'product_name',
        'quantity',
        'original_value',
        'discount',
        'freight',
        'total_value',
    ];

    public function formatValue($value) {
        return number_format($value, 2, ',', '.');
    }
}