<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import {
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import type { User } from '@/types';
import { computed } from 'vue';

type Props = {
    user: User;
};

const props = defineProps<Props>();

const logoutUrl = computed(() => {
    return props.user.is_admin ? '/adminos/logout' : '/logout';
});

const handleLogout = () => {
    router.post(logoutUrl.value, {}, {
        onFinish: () => router.flushAll(),
    });
};
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuItem>
        <button
            class="flex w-full cursor-pointer items-center"
            @click="handleLogout"
            type="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Déconnexion
        </button>
    </DropdownMenuItem>
</template>
