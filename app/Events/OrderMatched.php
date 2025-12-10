<?php

namespace App\Events;

use App\Http\Resources\TradeResource;
use App\Models\Trade;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderMatched implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Trade $trade,
        public int $userId
    ) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->userId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.matched';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'trade' => (new TradeResource($this->trade->load('asset')))->resolve(),
        ];
    }
}
