<?php

namespace App\Http\Resources;

use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Trade */
class TradeResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'buy_order_id' => $this->buy_order_id,
            'sell_order_id' => $this->sell_order_id,
            'asset_id' => $this->asset_id,
            'asset' => new AssetResource($this->whenLoaded('asset')),
            'price' => $this->price,
            'quantity' => $this->quantity,
            'commission' => $this->commission,
            'created_at' => $this->created_at,
        ];
    }
}
