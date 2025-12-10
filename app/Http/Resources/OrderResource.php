<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order */
class OrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'asset_id' => $this->asset_id,
            'asset' => $this->when($this->relationLoaded('asset'), fn () => [
                'id' => $this->asset->id,
                'name' => $this->asset->name,
                'symbol' => $this->asset->symbol,
            ]),
            'side' => $this->side->value,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'status' => $this->status->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
