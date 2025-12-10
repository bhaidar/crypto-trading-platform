<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssetResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\TradeResource;
use App\Models\Asset;
use App\Models\Trade;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TradingController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $assets = Asset::all();
        
        // Get selected asset from request or default to first asset
        $assetId = $request->input('asset_id');
        $selectedAsset = $assetId 
            ? $assets->firstWhere('id', $assetId)
            : $assets->first();

        $orderbook = $selectedAsset
            ? $this->orderService->getOrderbook($selectedAsset)
            : ['bids' => [], 'asks' => []];

        return Inertia::render('Trading/Index', [
            'assets' => AssetResource::collection($assets)->resolve(),
            'selectedAsset' => $selectedAsset ? (new AssetResource($selectedAsset))->resolve() : null,
            'orderbook' => [
                'bids' => OrderResource::collection($orderbook['bids'])->resolve(),
                'asks' => OrderResource::collection($orderbook['asks'])->resolve(),
            ],
            'userBalance' => $user->balance,
            'userLockedBalance' => $user->locked_balance,
            'userAssets' => AssetResource::collection($user->assets)->resolve(),
            'userOpenOrders' => OrderResource::collection(
                $user->orders()->where('status', 'open')->with('asset')->get()
            )->resolve(),
            'recentTrades' => TradeResource::collection(
                Trade::with('asset')
                    ->when($selectedAsset, fn ($q) => $q->where('asset_id', $selectedAsset->id))
                    ->latest('created_at')
                    ->limit(20)
                    ->get()
            )->resolve(),
        ]);
    }
}
