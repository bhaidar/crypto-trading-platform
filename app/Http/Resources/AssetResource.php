<?php

namespace App\Http\Resources;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Asset */
class AssetResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'balance' => $this->whenPivotLoaded('asset_user', fn () => $this->pivot->balance),
            'locked_amount' => $this->whenPivotLoaded('asset_user', fn () => $this->pivot->locked_amount),
        ];
    }
}
