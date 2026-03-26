<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    use HasFactory;

    protected $table =  'logs';

    protected $fillable = [
        'user_id',
        'activity',
        'detail'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
