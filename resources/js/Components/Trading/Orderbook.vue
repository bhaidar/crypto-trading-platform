<script setup>
defineProps({
    bids: {
        type: Array,
        default: () => [],
    },
    asks: {
        type: Array,
        default: () => [],
    },
    asset: {
        type: Object,
        required: true,
    },
});

const formatPrice = (price) => parseFloat(price).toFixed(2);
const formatQuantity = (quantity) => parseFloat(quantity).toFixed(8);
</script>

<template>
    <div class="rounded-lg bg-white p-4 shadow">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Order Book</h3>

        <div class="mb-2 grid grid-cols-3 text-xs font-medium text-gray-500">
            <span>Price (USD)</span>
            <span class="text-center">Quantity</span>
            <span class="text-right">Total</span>
        </div>

        <div class="mb-4">
            <div class="mb-2 text-xs font-medium text-red-600">Asks (Sell Orders)</div>
            <div class="max-h-40 space-y-1 overflow-y-auto">
                <div
                    v-for="ask in [...asks].reverse()"
                    :key="ask.id"
                    class="grid grid-cols-3 rounded bg-red-50 px-2 py-1 text-xs"
                >
                    <span class="font-medium text-red-600">${{ formatPrice(ask.price) }}</span>
                    <span class="text-center text-gray-700">{{ formatQuantity(ask.quantity) }}</span>
                    <span class="text-right text-gray-700">
                        ${{ (parseFloat(ask.price) * parseFloat(ask.quantity)).toFixed(2) }}
                    </span>
                </div>
                <div v-if="asks.length === 0" class="py-2 text-center text-xs text-gray-400">
                    No sell orders
                </div>
            </div>
        </div>

        <div class="my-3 border-t border-gray-200"></div>

        <div>
            <div class="mb-2 text-xs font-medium text-green-600">Bids (Buy Orders)</div>
            <div class="max-h-40 space-y-1 overflow-y-auto">
                <div
                    v-for="bid in bids"
                    :key="bid.id"
                    class="grid grid-cols-3 rounded bg-green-50 px-2 py-1 text-xs"
                >
                    <span class="font-medium text-green-600">${{ formatPrice(bid.price) }}</span>
                    <span class="text-center text-gray-700">{{ formatQuantity(bid.quantity) }}</span>
                    <span class="text-right text-gray-700">
                        ${{ (parseFloat(bid.price) * parseFloat(bid.quantity)).toFixed(2) }}
                    </span>
                </div>
                <div v-if="bids.length === 0" class="py-2 text-center text-xs text-gray-400">
                    No buy orders
                </div>
            </div>
        </div>
    </div>
</template>
