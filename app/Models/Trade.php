<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    /** @use HasFactory<\Database\Factories\TradeFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'buy_order_id',
        'sell_order_id',
        'asset_id',
        'price',
        'quantity',
        'commission',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:8',
            'quantity' => 'decimal:8',
            'commission' => 'decimal:8',
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Order, $this> */
    public function buyOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'buy_order_id');
    }

    /** @return BelongsTo<Order, $this> */
    public function sellOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'sell_order_id');
    }

    /** @return BelongsTo<Asset, $this> */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
