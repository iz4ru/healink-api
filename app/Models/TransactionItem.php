<?php

namespace App\Models;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'trx_id');
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }
}
