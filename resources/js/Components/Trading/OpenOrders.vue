<script setup>
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['orderCancelled']);

const filters = ref({
    symbol: '',
    side: '',
    status: '',
});

const filteredOrders = computed(() => {
    return props.orders.filter(order => {
        if (filters.value.symbol && order.asset?.symbol !== filters.value.symbol) return false;
        if (filters.value.side && order.side !== filters.value.side) return false;
        if (filters.value.status && order.status !== filters.value.status) return false;
        return true;
    });
});

const uniqueSymbols = computed(() => {
    return [...new Set(props.orders.map(order => order.asset?.symbol).filter(Boolean))];
});

const cancelOrder = (orderId) => {
    if (confirm('Are you sure you want to cancel this order?')) {
        router.delete(route('orders.destroy', orderId), {
            preserveScroll: true,
            onSuccess: () => emit('orderCancelled'),
        });
    }
};

const formatPrice = (price) => parseFloat(price).toFixed(2);
const formatQuantity = (quantity) => parseFloat(quantity).toFixed(8);
</script>

<template>
    <div class="rounded-lg bg-white p-4 shadow">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Open Orders</h3>

        <div class="mb-4 flex flex-wrap gap-3">
            <select
                v-model="filters.symbol"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">All Symbols</option>
                <option v-for="symbol in uniqueSymbols" :key="symbol" :value="symbol">
                    {{ symbol }}
                </option>
            </select>

            <select
                v-model="filters.side"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">All Sides</option>
                <option value="buy">Buy</option>
                <option value="sell">Sell</option>
            </select>

            <select
                v-model="filters.status"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">All Statuses</option>
                <option value="open">Open</option>
                <option value="filled">Filled</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Asset</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Side</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Price</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Quantity</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Total</th>
                        <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="order in filteredOrders" :key="order.id">
                        <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-900">
                            {{ order.asset?.symbol || 'N/A' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-sm">
                            <span
                                :class="[
                                    'rounded-full px-2 py-1 text-xs font-medium',
                                    order.side === 'buy'
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800',
                                ]"
                            >
                                {{ order.side.toUpperCase() }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-900">
                            ${{ formatPrice(order.price) }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-900">
                            {{ formatQuantity(order.quantity) }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-900">
                            ${{ (parseFloat(order.price) * parseFloat(order.quantity)).toFixed(2) }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-right text-sm">
                            <button
                                @click="cancelOrder(order.id)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Cancel
                            </button>
                        </td>
                    </tr>
                    <tr v-if="filteredOrders.length === 0">
                        <td colspan="6" class="px-3 py-4 text-center text-sm text-gray-400">
                            No orders found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
