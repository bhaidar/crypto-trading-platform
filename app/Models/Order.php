<?php

namespace App\Models;

use App\Enums\OrderSide;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset_id',
        'side',
        'price',
        'quantity',
        'status',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'side' => OrderSide::class,
            'status' => OrderStatus::class,
            'price' => 'decimal:8',
            'quantity' => 'decimal:8',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Asset, $this> */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /** @return HasMany<Trade, $this> */
    public function buyTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'buy_order_id');
    }

    /** @return HasMany<Trade, $this> */
    public function sellTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'sell_order_id');
    }
}
