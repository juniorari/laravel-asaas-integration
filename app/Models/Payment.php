<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Payment
 * @property string $asaas_id
 * @property string $billing_type
 * @property int $installment
 * @property string $installment_token
 * @package App\Models
 */
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
