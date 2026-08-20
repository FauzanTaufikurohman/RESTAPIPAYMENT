<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'transaction_id',
        'user_id',
        'type',
        'amount',
        'description',
        'balance_before',
        'balance_after',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
