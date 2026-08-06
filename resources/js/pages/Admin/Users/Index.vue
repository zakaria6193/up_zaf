<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import { ref, watch } from 'vue';
import { destroy, edit, show, create } from '@/actions/App/Http/Controllers/Admin/UserController';
import { debounce } from 'lodash-es';
import { Pencil, Trash2, Eye, Plus } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/adminos/dashboard' },
            { title: 'Users' },
        ],
    },
});

const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            phone: string | null;
            businesses_count: number;
            created_at: string;
        }>;
        links: {
            first: string | null;
            last: string | null;
            prev: string | null;
            next: string | null;
        };
        meta: {
            current_page: number;
            from: number | null;
            last_page: number;
            per_page: number;
            to: number | null;
            total: number;
        };
    };
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');
const showDeleteDialog = ref(false);
const selectedUser = ref<typeof props.users.data[0] | null>(null);

const debouncedSearch = debounce((value: string) => {
    router.get(
        window.location.pathname,
        { search: value },
        {
            preserveState: true,
            replace: true,
        }
    );
}, 300);

watch(search, (value) => {
    debouncedSearch(value);
});

const openDeleteDialog = (user: typeof props.users.data[0]) => {
    selectedUser.value = user;
    showDeleteDialog.value = true;
};

const handleDelete = () => {
    if (selectedUser.value) {
        router.delete(destroy.url(selectedUser.value.id), {
            onFinish: () => {
                showDeleteDialog.value = false;
                selectedUser.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Users" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Users</h1>
                <p class="text-sm text-muted-foreground">
                    Manage business owner accounts
                </p>
            </div>
            <Button as-child>
                <Link :href="create.url()">
                    <Plus class="mr-2 h-4 w-4" />
                    Add user
                </Link>
            </Button>
        </div>

        <!-- Search -->
        <div class="flex items-center gap-4">
            <Input
                v-model="search"
                type="search"
                placeholder="Search by name, email, or phone..."
                class="max-w-sm"
            />
        </div>

        <!-- Users List -->
        <div v-if="users.data.length > 0" class="space-y-3">
            <div
                v-for="user in users.data"
                :key="user.id"
                class="group relative overflow-hidden rounded-lg border border-sidebar-border/70 bg-card transition-all hover:border-sidebar-border hover:shadow-md"
            >
                <div class="flex items-center gap-4 p-4">
                    <!-- Avatar -->
                    <div class="h-12 w-12 shrink-0 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-lg font-semibold">
                        {{ user.name.charAt(0).toUpperCase() }}
                    </div>

                    <!-- User Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-semibold text-lg truncate">
                                {{ user.name }}
                            </h3>
                            <Badge v-if="user.businesses_count > 0" variant="secondary">
                                {{ user.businesses_count }} {{ user.businesses_count === 1 ? 'business' : 'businesses' }}
                            </Badge>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ user.email }}
                        </p>
                        <p v-if="user.phone" class="text-sm text-muted-foreground">
                            {{ user.phone }}
                        </p>
                        <p class="text-xs text-muted-foreground mt-1">
                            Created {{ user.created_at }}
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-1 shrink-0">
                        <!-- View Button -->
                        <Button
                            size="icon"
                            variant="ghost"
                            as-child
                            title="View details"
                        >
                            <Link :href="show.url(user.id)">
                                <Eye class="h-4 w-4" />
                            </Link>
                        </Button>

                        <!-- Edit Button -->
                        <Button
                            size="icon"
                            variant="ghost"
                            as-child
                            title="Edit"
                        >
                            <Link :href="edit.url(user.id)">
                                <Pencil class="h-4 w-4" />
                            </Link>
                        </Button>

                        <!-- Delete Button -->
                        <Button
                            size="icon"
                            variant="ghost"
                            class="text-destructive hover:text-destructive hover:bg-destructive/10"
                            @click.prevent="openDeleteDialog(user)"
                            title="Delete"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex min-h-[400px] flex-col items-center justify-center rounded-lg border border-dashed border-sidebar-border/70 p-8 text-center">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="mb-4 h-12 w-12 text-muted-foreground"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"
                />
            </svg>
            <h3 class="mb-2 text-lg font-semibold">No users yet</h3>
            <p class="mb-4 text-sm text-muted-foreground">
                Start by creating your first user
            </p>
            <Button as-child>
                <Link :href="create.url()">
                    <Plus class="mr-2 h-4 w-4" />
                    Add your first user
                </Link>
            </Button>
        </div>

        <!-- Pagination -->
        <div
            v-if="users.data.length > 0 && users.meta?.last_page > 1"
            class="flex items-center justify-between"
        >
            <p class="text-sm text-muted-foreground">
                Showing {{ users.meta?.from }} to {{ users.meta?.to }} of
                {{ users.meta?.total }} users
            </p>
            <div class="flex gap-2">
                <Button
                    v-if="users.links.prev"
                    as-child
                    variant="outline"
                    size="sm"
                >
                    <Link :href="users.links.prev">Previous</Link>
                </Button>
                <Button
                    v-if="users.links.next"
                    as-child
                    variant="outline"
                    size="sm"
                >
                    <Link :href="users.links.next">Next</Link>
                </Button>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <DeleteConfirmDialog
            v-if="selectedUser"
            v-model:open="showDeleteDialog"
            title="Delete user"
            description="Are you sure you want to delete"
            :item-name="selectedUser.name"
            @confirm="handleDelete"
        />
    </div>
</template>
