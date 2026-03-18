<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $table = 'transaction_items';

    protected $fillable = [
        'trx_id',
        'product_id',
        'batch_id',
        'product_name',
        'qty',
        'unit_price',
        'subtotal'
    ];
}
