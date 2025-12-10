<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    userBalance: {
        type: String,
        required: true,
    },
    userAssetBalance: {
        type: String,
        default: '0',
    },
});

const emit = defineEmits(['orderPlaced']);

const activeTab = ref('buy');

const form = useForm({
    asset_id: props.asset.id,
    side: 'buy',
    price: '',
    quantity: '',
});

// Watch for asset changes and update form.asset_id
watch(() => props.asset.id, (newAssetId) => {
    form.asset_id = newAssetId;
});

const total = computed(() => {
    const price = parseFloat(form.price) || 0;
    const quantity = parseFloat(form.quantity) || 0;
    return (price * quantity).toFixed(8);
});

const commission = computed(() => {
    const totalValue = parseFloat(total.value) || 0;
    return (totalValue * 0.015).toFixed(2); // 1.5% commission
});

const totalWithCommission = computed(() => {
    const totalValue = parseFloat(total.value) || 0;
    const commissionValue = parseFloat(commission.value) || 0;
    return (totalValue + commissionValue).toFixed(2);
});

const setTab = (tab) => {
    activeTab.value = tab;
    form.side = tab;
};

const submit = () => {
    form.post(route('orders.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('price', 'quantity');
            emit('orderPlaced');
        },
    });
};
</script>

<template>
    <div class="rounded-lg bg-white p-4 shadow">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Place Order</h3>

        <div class="mb-4 flex gap-2">
            <button
                type="button"
                @click="setTab('buy')"
                :class="[
                    'flex-1 rounded-md px-4 py-2 text-sm font-medium transition',
                    activeTab === 'buy'
                        ? 'bg-green-600 text-white'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                ]"
            >
                Buy
            </button>
            <button
                type="button"
                @click="setTab('sell')"
                :class="[
                    'flex-1 rounded-md px-4 py-2 text-sm font-medium transition',
                    activeTab === 'sell'
                        ? 'bg-red-600 text-white'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                ]"
            >
                Sell
            </button>
        </div>

        <div class="mb-4 rounded-md bg-gray-50 p-3">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Available:</span>
                <span class="font-medium text-gray-900">
                    <template v-if="activeTab === 'buy'">
                        ${{ parseFloat(userBalance).toFixed(2) }} USD
                    </template>
                    <template v-else>
                        {{ parseFloat(userAssetBalance).toFixed(8) }} {{ asset.symbol }}
                    </template>
                </span>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="price" value="Price (USD)" />
                <TextInput
                    id="price"
                    v-model="form.price"
                    type="number"
                    step="0.00000001"
                    min="0"
                    class="mt-1 block w-full"
                    placeholder="0.00000000"
                />
                <InputError :message="form.errors.price" class="mt-2" />
            </div>

            <div>
                <InputLabel for="quantity" :value="`Quantity (${asset.symbol})`" />
                <TextInput
                    id="quantity"
                    v-model="form.quantity"
                    type="number"
                    step="0.00000001"
                    min="0"
                    class="mt-1 block w-full"
                    placeholder="0.00000000"
                />
                <InputError :message="form.errors.quantity" class="mt-2" />
            </div>

            <div class="space-y-2 rounded-md bg-gray-50 p-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Subtotal:</span>
                    <span class="font-medium text-gray-900">${{ total }} USD</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Commission (1.5%):</span>
                    <span class="font-medium text-gray-900">${{ commission }} USD</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-2 text-sm font-semibold">
                    <span class="text-gray-700">Total:</span>
                    <span class="text-gray-900">${{ totalWithCommission }} USD</span>
                </div>
            </div>

            <PrimaryButton
                :class="[
                    'w-full justify-center',
                    activeTab === 'buy' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700',
                ]"
                :disabled="form.processing"
            >
                <span v-if="form.processing">Processing...</span>
                <span v-else>{{ activeTab === 'buy' ? 'Buy' : 'Sell' }} {{ asset.symbol }}</span>
            </PrimaryButton>
        </form>
    </div>
</template>
