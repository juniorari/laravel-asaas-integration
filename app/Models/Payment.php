<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asaas_id',
        'customer_id',
        'billing_type',
        'due_date',
        'value',
        'installment',
        'installment_token',
        'description',
        'bank_slip_url',
        'invoice_url',
        'status'
    ];
}
