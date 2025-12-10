<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileApiController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $assets = $user->assets->map(function ($asset) {
            return [
                'symbol' => $asset->symbol,
                'amount' => $asset->pivot->balance,
                'locked_amount' => $asset->pivot->locked_amount,
            ];
        });

        return response()->json([
            'balance' => $user->balance,
            'locked_balance' => $user->locked_balance,
            'assets' => $assets,
        ]);
    }
}
