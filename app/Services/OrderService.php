<?php

namespace App\Services;

use App\Enums\OrderSide;
use App\Enums\OrderStatus;
use App\Events\OrderMatched;
use App\Models\Asset;
use App\Models\Order;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    private const COMMISSION_RATE = 0.015;

    public function placeOrder(User $user, Asset $asset, OrderSide $side, string $price, string $quantity): Order
    {
        return DB::transaction(function () use ($user, $asset, $side, $price, $quantity) {
            $user->lockForUpdate()->find($user->id);

            $this->validateAndLockFunds($user, $asset, $side, $price, $quantity);

            $order = Order::create([
                'user_id' => $user->id,
                'asset_id' => $asset->id,
                'side' => $side,
                'price' => $price,
                'quantity' => $quantity,
                'status' => OrderStatus::Open,
            ]);

            $this->tryMatchOrder($order);

            return $order->fresh();
        });
    }

    public function cancelOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order = Order::lockForUpdate()->find($order->id);

            if ($order->status !== OrderStatus::Open) {
                throw ValidationException::withMessages([
                    'order' => 'Only open orders can be cancelled.',
                ]);
            }

            $user = User::lockForUpdate()->find($order->user_id);

            $this->releaseFunds($user, $order);

            $order->update(['status' => OrderStatus::Cancelled]);

            return $order->fresh();
        });
    }

    /** @return array{bids: array<int, Order>, asks: array<int, Order>} */
    public function getOrderbook(Asset $asset): array
    {
        $bids = Order::where('asset_id', $asset->id)
            ->where('side', OrderSide::Buy)
            ->where('status', OrderStatus::Open)
            ->orderByDesc('price')
            ->orderBy('created_at')
            ->limit(20)
            ->get();

        $asks = Order::where('asset_id', $asset->id)
            ->where('side', OrderSide::Sell)
            ->where('status', OrderStatus::Open)
            ->orderBy('price')
            ->orderBy('created_at')
            ->limit(20)
            ->get();

        return [
            'bids' => $bids->all(),
            'asks' => $asks->all(),
        ];
    }

    private function validateAndLockFunds(User $user, Asset $asset, OrderSide $side, string $price, string $quantity): void
    {
        if ($side === OrderSide::Buy) {
            $totalCost = bcmul($price, $quantity, 8);
            $availableBalance = bcsub($user->balance, $user->locked_balance, 8);

            if (bccomp($availableBalance, $totalCost, 8) < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient USD balance. You need $' . $totalCost . ' but only have $' . $availableBalance . ' available.',
                ]);
            }

            $user->update([
                'locked_balance' => bcadd($user->locked_balance, $totalCost, 8),
            ]);
        } else {
            $pivot = $user->assets()->where('asset_id', $asset->id)->first()?->pivot;

            if (! $pivot) {
                throw ValidationException::withMessages([
                    'quantity' => 'You do not own any ' . $asset->symbol . '.',
                ]);
            }

            $availableBalance = bcsub($pivot->balance, $pivot->locked_amount, 8);

            if (bccomp($availableBalance, $quantity, 8) < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient ' . $asset->symbol . ' balance. You need ' . $quantity . ' but only have ' . $availableBalance . ' available.',
                ]);
            }

            $user->assets()->updateExistingPivot($asset->id, [
                'locked_amount' => bcadd($pivot->locked_amount, $quantity, 8),
            ]);
        }
    }

    private function releaseFunds(User $user, Order $order): void
    {
        if ($order->side === OrderSide::Buy) {
            $totalCost = bcmul($order->price, $order->quantity, 8);

            $user->update([
                'locked_balance' => bcsub($user->locked_balance, $totalCost, 8),
            ]);
        } else {
            $pivot = $user->assets()->where('asset_id', $order->asset_id)->first()?->pivot;

            if ($pivot) {
                $user->assets()->updateExistingPivot($order->asset_id, [
                    'locked_amount' => bcsub($pivot->locked_amount, $order->quantity, 8),
                ]);
            }
        }
    }

    private function tryMatchOrder(Order $order): void
    {
        $matchingOrder = $this->findMatchingOrder($order);

        if (! $matchingOrder) {
            return;
        }

        if (bccomp($order->quantity, $matchingOrder->quantity, 8) !== 0) {
            return;
        }

        $this->executeMatch($order, $matchingOrder);
    }

    private function findMatchingOrder(Order $order): ?Order
    {
        $query = Order::where('asset_id', $order->asset_id)
            ->where('status', OrderStatus::Open)
            ->where('user_id', '!=', $order->user_id)
            ->where('quantity', $order->quantity);

        if ($order->side === OrderSide::Buy) {
            $query->where('side', OrderSide::Sell)
                ->where('price', '<=', $order->price)
                ->orderBy('price')
                ->orderBy('created_at');
        } else {
            $query->where('side', OrderSide::Buy)
                ->where('price', '>=', $order->price)
                ->orderByDesc('price')
                ->orderBy('created_at');
        }

        return $query->lockForUpdate()->first();
    }

    private function executeMatch(Order $order, Order $matchingOrder): void
    {
        $buyOrder = $order->side === OrderSide::Buy ? $order : $matchingOrder;
        $sellOrder = $order->side === OrderSide::Sell ? $order : $matchingOrder;

        $tradePrice = $matchingOrder->price;
        $tradeQuantity = $order->quantity;
        $commission = bcmul(bcmul($tradePrice, $tradeQuantity, 8), (string) self::COMMISSION_RATE, 8);

        $trade = Trade::create([
            'buy_order_id' => $buyOrder->id,
            'sell_order_id' => $sellOrder->id,
            'asset_id' => $order->asset_id,
            'price' => $tradePrice,
            'quantity' => $tradeQuantity,
            'commission' => $commission,
        ]);

        $order->update(['status' => OrderStatus::Filled]);
        $matchingOrder->update(['status' => OrderStatus::Filled]);

        $this->settleBalances($buyOrder, $sellOrder, $tradePrice, $tradeQuantity, $commission);

        event(new OrderMatched($trade, $buyOrder->user_id));
        event(new OrderMatched($trade, $sellOrder->user_id));
    }

    private function settleBalances(Order $buyOrder, Order $sellOrder, string $price, string $quantity, string $commission): void
    {
        $totalCost = bcmul($price, $quantity, 8);
        $buyerTotalCost = bcadd($totalCost, $commission, 8);
        $originalLockedCost = bcmul($buyOrder->price, $buyOrder->quantity, 8);

        $buyer = User::lockForUpdate()->find($buyOrder->user_id);
        $seller = User::lockForUpdate()->find($sellOrder->user_id);

        $buyer->update([
            'balance' => bcsub($buyer->balance, $buyerTotalCost, 8),
            'locked_balance' => bcsub($buyer->locked_balance, $originalLockedCost, 8),
        ]);

        $this->creditAssetToUser($buyer, $buyOrder->asset_id, $quantity);

        $seller->update([
            'balance' => bcadd($seller->balance, $totalCost, 8),
        ]);

        $this->debitAssetFromUser($seller, $sellOrder->asset_id, $quantity);
    }

    private function creditAssetToUser(User $user, int $assetId, string $quantity): void
    {
        $pivot = $user->assets()->where('asset_id', $assetId)->first()?->pivot;

        if ($pivot) {
            $user->assets()->updateExistingPivot($assetId, [
                'balance' => bcadd($pivot->balance, $quantity, 8),
            ]);
        } else {
            $user->assets()->attach($assetId, [
                'balance' => $quantity,
                'locked_amount' => '0',
            ]);
        }
    }

    private function debitAssetFromUser(User $user, int $assetId, string $quantity): void
    {
        $pivot = $user->assets()->where('asset_id', $assetId)->first()?->pivot;

        if ($pivot) {
            $user->assets()->updateExistingPivot($assetId, [
                'balance' => bcsub($pivot->balance, $quantity, 8),
                'locked_amount' => bcsub($pivot->locked_amount, $quantity, 8),
            ]);
        }
    }
}
