<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'trx_no',
        'user_id',
        'customer_name',
        'subtotal',
        'total_amount',
        'paid_amount',
        'change_amount',
        'transaction_date',
        'status',
        'note',
        'void_reason'
    ];
}
