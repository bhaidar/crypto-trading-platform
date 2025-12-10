<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: 'success', // success, error, info
    },
    message: {
        type: String,
        required: true,
    },
    duration: {
        type: Number,
        default: 3000,
    },
});

const emit = defineEmits(['close']);

const visible = ref(props.show);

watch(() => props.show, (newVal) => {
    visible.value = newVal;
    if (newVal) {
        setTimeout(() => {
            visible.value = false;
            emit('close');
        }, props.duration);
    }
});

onMounted(() => {
    if (props.show) {
        setTimeout(() => {
            visible.value = false;
            emit('close');
        }, props.duration);
    }
});

const bgColor = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    info: 'bg-blue-500',
};

const icon = {
    success: '✓',
    error: '✕',
    info: 'ℹ',
};
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
    >
        <div
            v-if="visible"
            :class="[
                'fixed bottom-4 right-4 z-50 flex items-center gap-3 rounded-lg px-4 py-3 text-white shadow-lg',
                bgColor[type],
            ]"
        >
            <span class="text-xl font-bold">{{ icon[type] }}</span>
            <span class="text-sm">{{ message }}</span>
            <button
                @click="visible = false; emit('close')"
                class="ml-2 text-white hover:text-gray-200"
            >
                ✕
            </button>
        </div>
    </Transition>
</template>
