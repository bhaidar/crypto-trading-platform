import { ref } from 'vue';
import { onMounted, onUnmounted } from 'vue';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { usePage, router } from '@inertiajs/vue3';

let echoInstance = null;
const isInitialized = ref(false);

export function useEcho() {
    const page = usePage();

    const initializeEcho = () => {
        if (isInitialized.value || echoInstance) {
            return echoInstance;
        }

        echoInstance = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
            forceTLS: true,
        });

        window.Echo = echoInstance;
        isInitialized.value = true;

        return echoInstance;
    };

    const disconnect = () => {
        if (echoInstance) {
            echoInstance.disconnect();
            echoInstance = null;
            isInitialized.value = false;
        }
    };


    const subscribeToUserChannel = (callback) => {
        const userId = page.props.auth?.user?.id;

        if (!userId || !window.Echo) {
            return null;
        }

        return window.Echo.private(`user.${userId}`).listen('.order.matched', (event) => {
            if (callback) {
                callback(event);
            }

            router.reload({ only: ['orderbook', 'userOpenOrders', 'recentTrades', 'userBalance', 'userLockedBalance', 'userAssets'] });
        });
    };

    const unsubscribeFromUserChannel = () => {
        const userId = page.props.auth?.user?.id;

        if (userId && window.Echo) {
            window.Echo.leave(`user.${userId}`);
        }
    };

    return {
        initializeEcho,
        disconnect,
        isInitialized,
        subscribeToUserChannel,
        unsubscribeFromUserChannel,
    };
}

export function useOrderMatched(callback) {
    const { subscribeToUserChannel, unsubscribeFromUserChannel } = useEcho();

    onMounted(() => {
        subscribeToUserChannel(callback);
    });

    onUnmounted(() => {
        unsubscribeFromUserChannel();
    });
}
