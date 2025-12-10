<script setup>
defineProps({
    trades: {
        type: Array,
        default: () => [],
    },
});

const formatPrice = (price) => parseFloat(price).toFixed(2);
const formatQuantity = (quantity) => parseFloat(quantity).toFixed(8);
const formatTime = (timestamp) => {
    return new Date(timestamp).toLocaleTimeString();
};
</script>

<template>
    <div class="rounded-lg bg-white p-4 shadow">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Recent Trades</h3>

        <div class="mb-2 grid grid-cols-3 text-xs font-medium text-gray-500">
            <span>Price</span>
            <span class="text-center">Quantity</span>
            <span class="text-right">Time</span>
        </div>

        <div class="max-h-60 space-y-1 overflow-y-auto">
            <div
                v-for="trade in trades"
                :key="trade.id"
                class="grid grid-cols-3 rounded bg-gray-50 px-2 py-1 text-xs"
            >
                <span class="font-medium text-gray-900">${{ formatPrice(trade.price) }}</span>
                <span class="text-center text-gray-700">{{ formatQuantity(trade.quantity) }}</span>
                <span class="text-right text-gray-500">{{ formatTime(trade.created_at) }}</span>
            </div>
            <div v-if="trades.length === 0" class="py-4 text-center text-xs text-gray-400">
                No recent trades
            </div>
        </div>
    </div>
</template>
