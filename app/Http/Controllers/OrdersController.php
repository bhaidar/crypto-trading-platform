<?php

namespace App\Http\Controllers;

use App\Enums\OrderSide;
use App\Enums\OrderStatus;
use App\Http\Requests\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Asset;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrdersController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()
            ->orders()
            ->with('asset')
            ->latest()
            ->paginate(20);

        return OrderResource::collection($orders);
    }

    public function store(PlaceOrderRequest $request): RedirectResponse
    {
        $asset = Asset::findOrFail($request->validated('asset_id'));
        $side = OrderSide::from($request->validated('side'));

        $order = $this->orderService->placeOrder(
            $request->user(),
            $asset,
            $side,
            $request->validated('price'),
            $request->validated('quantity')
        );

        return redirect()->back()->with('success', 'Order placed successfully!');
    }

    public function destroy(Order $order): RedirectResponse
    {
        if ($order->user_id !== request()->user()->id) {
            abort(403, 'You can only cancel your own orders.');
        }

        $this->orderService->cancelOrder($order);

        return redirect()->back();
    }

    /** @return array{bids: array<int, OrderResource>, asks: array<int, OrderResource>} */
    public function orderbook(Asset $asset): array
    {
        $orderbook = $this->orderService->getOrderbook($asset);

        return [
            'bids' => OrderResource::collection($orderbook['bids'])->resolve(),
            'asks' => OrderResource::collection($orderbook['asks'])->resolve(),
        ];
    }

    public function openOrders(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()
            ->orders()
            ->with('asset')
            ->where('status', OrderStatus::Open)
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }
}
