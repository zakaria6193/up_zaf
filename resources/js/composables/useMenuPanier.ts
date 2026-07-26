import { computed, ref, watch } from 'vue';

export type PanierItem = {
    id: number;
    name: string;
    price: number;
    quantity: number;
};

type StoredPanier = Record<number, PanierItem>;

function storageKey(nanoid: string): string {
    return `up1-menu-panier:${nanoid}`;
}

function loadPanier(nanoid: string): StoredPanier {
    if (typeof window === 'undefined') {
        return {};
    }

    try {
        const raw = window.localStorage.getItem(storageKey(nanoid));
        if (!raw) {
            return {};
        }

        const parsed = JSON.parse(raw) as StoredPanier;

        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch {
        return {};
    }
}

export function useMenuPanier(nanoid: string) {
    const items = ref<StoredPanier>(loadPanier(nanoid));
    const isOpen = ref(false);

    watch(
        items,
        (value) => {
            if (typeof window === 'undefined') {
                return;
            }

            window.localStorage.setItem(storageKey(nanoid), JSON.stringify(value));
        },
        { deep: true },
    );

    const list = computed(() =>
        Object.values(items.value).sort((a, b) => a.name.localeCompare(b.name)),
    );

    const totalQuantity = computed(() =>
        list.value.reduce((sum, item) => sum + item.quantity, 0),
    );

    const totalPrice = computed(() =>
        list.value.reduce((sum, item) => sum + item.price * item.quantity, 0),
    );

    const quantityOf = (id: number): number => items.value[id]?.quantity ?? 0;

    const addItem = (item: { id: number; name: string; price: number }) => {
        const existing = items.value[item.id];

        if (existing) {
            existing.quantity += 1;
            return;
        }

        items.value[item.id] = {
            id: item.id,
            name: item.name,
            price: Number(item.price),
            quantity: 1,
        };
    };

    const increment = (id: number) => {
        const existing = items.value[id];
        if (existing) {
            existing.quantity += 1;
        }
    };

    const decrement = (id: number) => {
        const existing = items.value[id];
        if (!existing) {
            return;
        }

        if (existing.quantity <= 1) {
            delete items.value[id];
            return;
        }

        existing.quantity -= 1;
    };

    const removeItem = (id: number) => {
        delete items.value[id];
    };

    const clear = () => {
        items.value = {};
    };

    const open = () => {
        isOpen.value = true;
    };

    const close = () => {
        isOpen.value = false;
    };

    const toggle = () => {
        isOpen.value = !isOpen.value;
    };

    return {
        items,
        list,
        isOpen,
        totalQuantity,
        totalPrice,
        quantityOf,
        addItem,
        increment,
        decrement,
        removeItem,
        clear,
        open,
        close,
        toggle,
    };
}
