<script setup>
import { computed, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrderForm from '@/Components/Trading/OrderForm.vue';
import Orderbook from '@/Components/Trading/Orderbook.vue';
import OpenOrders from '@/Components/Trading/OpenOrders.vue';
import RecentTrades from '@/Components/Trading/RecentTrades.vue';
import Toast from '@/Components/Toast.vue';
import { useOrderMatched } from '@/Composables/useEcho';

const toast = ref({
    show: false,
    type: 'success',
    message: '',
});

const showToast = (type, message) => {
    toast.value = { show: true, type, message };
};

useOrderMatched((event) => {
    console.log('Order matched:', event);
    showToast('success', `Order matched! ${event.trade.quantity} ${event.trade.asset.symbol} @ $${event.trade.price}`);
    refreshData();
});

const props = defineProps({
    assets: {
        type: Array,
        default: () => [],
    },
    selectedAsset: {
        type: Object,
        default: null,
    },
    orderbook: {
        type: Object,
        default: () => ({ bids: [], asks: [] }),
    },
    userBalance: {
        type: String,
        default: '0',
    },
    userLockedBalance: {
        type: String,
        default: '0',
    },
    userAssets: {
        type: Array,
        default: () => [],
    },
    userOpenOrders: {
        type: Array,
        default: () => [],
    },
    recentTrades: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const currentAssetId = ref(props.selectedAsset?.id);

const availableBalance = computed(() => {
    return (parseFloat(props.userBalance) - parseFloat(props.userLockedBalance)).toFixed(2);
});

const userAssetBalance = computed(() => {
    if (!props.selectedAsset) {
        return '0';
    }
    const asset = props.userAssets.find((a) => a.id === props.selectedAsset.id);
    if (!asset) {
        return '0';
    }
    const balance = parseFloat(asset.balance || 0);
    const locked = parseFloat(asset.locked_amount || 0);
    return (balance - locked).toFixed(8);
});

const changeAsset = (assetId) => {
    currentAssetId.value = assetId;
    router.get(route('trading'), { asset_id: assetId }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const refreshData = () => {
    router.reload({ only: ['orderbook', 'userOpenOrders', 'recentTrades', 'userBalance', 'userLockedBalance', 'userAssets'] });
};
</script>

<template>
    <Head title="Trading" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Trading
                    </h2>
                    <select
                        v-if="assets.length > 0"
                        v-model.number="currentAssetId"
                        @change="changeAsset($event.target.value)"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option v-for="asset in assets" :key="asset.id" :value="asset.id">
                            {{ asset.symbol }} - {{ asset.name }}
                        </option>
                    </select>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">
                        Balance: <span class="font-semibold">${{ availableBalance }}</span> USD
                    </span>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="!selectedAsset" class="rounded-lg bg-white p-8 text-center shadow">
                    <p class="text-gray-500">No assets available for trading. Please seed the database.</p>
                </div>

                <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="space-y-6 lg:col-span-2">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <Orderbook
                                :bids="orderbook.bids"
                                :asks="orderbook.asks"
                                :asset="selectedAsset"
                            />
                            <RecentTrades :trades="recentTrades" />
                        </div>
                        <OpenOrders
                            :orders="userOpenOrders"
                            @orderCancelled="refreshData"
                        />
                    </div>

                    <div>
                        <OrderForm
                            :asset="selectedAsset"
                            :user-balance="availableBalance"
                            :user-asset-balance="userAssetBalance"
                            @orderPlaced="refreshData"
                        />

                        <div class="mt-6 rounded-lg bg-white p-4 shadow">
                            <h3 class="mb-3 text-sm font-semibold text-gray-900">Your Assets</h3>
                            <div class="space-y-2">
                                <div
                                    v-for="asset in userAssets"
                                    :key="asset.id"
                                    class="flex justify-between text-sm"
                                >
                                    <span class="text-gray-600">{{ asset.symbol }}</span>
                                    <span class="font-medium text-gray-900">
                                        {{ parseFloat(asset.balance || 0).toFixed(8) }}
                                    </span>
                                </div>
                                <div v-if="userAssets.length === 0" class="text-center text-xs text-gray-400">
                                    No assets owned
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <Toast
            :show="toast.show"
            :type="toast.type"
            :message="toast.message"
            @close="toast.show = false"
        />
    </AuthenticatedLayout>
</template>
