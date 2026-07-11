<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { index as businessIndex, show as businessShow } from '@/actions/App/Http/Controllers/Admin/BusinessController';
import {
    index as linksIndex,
    store as storeLink,
    update as updateLink,
    destroy as destroyLink,
    reorder as reorderLinks,
} from '@/actions/App/Http/Controllers/Admin/BusinessLinkController';
import { ref, computed } from 'vue';
import { Plus, Edit, Trash2, Save, X, ChevronUp, ChevronDown } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/adminos/dashboard' },
            { title: 'Businesses', href: businessIndex().url() },
            { title: 'Business Links' },
        ],
    },
});

const props = defineProps<{
    business: {
        nanoid: string;
        name: string;
    };
    links: Array<{
        id: number;
        type: string;
        label: string;
        url: string;
        is_active: boolean;
        order: number;
    }>;
    link_types: Record<string, string>;
    can_add_more: boolean;
}>();

const showAddForm = ref(false);
const editingId = ref<number | null>(null);

const addForm = useForm({
    type: 'google_reviews',
    label: '',
    url: '',
    is_active: true,
});

const editForm = useForm({
    type: '',
    label: '',
    url: '',
    is_active: true,
});

const sortedLinks = computed(() => {
    return [...props.links].sort((a, b) => a.order - b.order);
});

const handleAddLink = () => {
    addForm.post(storeLink(props.business.nanoid).url(), {
        preserveScroll: true,
        onSuccess: () => {
            addForm.reset();
            showAddForm.value = false;
        },
    });
};

const startEdit = (link: typeof props.links[0]) => {
    editingId.value = link.id;
    editForm.type = link.type;
    editForm.label = link.label;
    editForm.url = link.url;
    editForm.is_active = link.is_active;
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
};

const handleUpdateLink = (linkId: number) => {
    editForm.put(updateLink(props.business.nanoid, linkId).url(), {
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null;
            editForm.reset();
        },
    });
};

const handleDeleteLink = (linkId: number, linkLabel: string) => {
    if (confirm(`Are you sure you want to delete "${linkLabel}"?`)) {
        router.delete(destroyLink(props.business.nanoid, linkId).url());
    }
};

const moveUp = (link: typeof props.links[0]) => {
    const currentIndex = sortedLinks.value.findIndex((l) => l.id === link.id);
    if (currentIndex === 0) {
        return;
    }

    const newLinks = sortedLinks.value.map((l, index) => {
        if (index === currentIndex - 1) {
            return { id: l.id, order: currentIndex };
        }
        if (index === currentIndex) {
            return { id: l.id, order: currentIndex - 1 };
        }
        return { id: l.id, order: index };
    });

    router.post(reorderLinks(props.business.nanoid).url(), { links: newLinks });
};

const moveDown = (link: typeof props.links[0]) => {
    const currentIndex = sortedLinks.value.findIndex((l) => l.id === link.id);
    if (currentIndex === sortedLinks.value.length - 1) {
        return;
    }

    const newLinks = sortedLinks.value.map((l, index) => {
        if (index === currentIndex) {
            return { id: l.id, order: currentIndex + 1 };
        }
        if (index === currentIndex + 1) {
            return { id: l.id, order: currentIndex };
        }
        return { id: l.id, order: index };
    });

    router.post(reorderLinks(props.business.nanoid).url(), { links: newLinks });
};
</script>

<template>
    <Head title="Manage Links" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Manage Links</h1>
                <p class="text-sm text-muted-foreground">
                    Configure links for {{ business.name }}. Maximum 4 active links.
                </p>
            </div>
            <Button as-child variant="outline">
                <Link :href="businessShow(business.nanoid).url()">
                    Back to Business
                </Link>
            </Button>
        </div>

        <!-- Add Link Form -->
        <Card v-if="showAddForm">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <CardTitle>Add New Link</CardTitle>
                    <Button
                        size="icon"
                        variant="ghost"
                        @click="showAddForm = false"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="handleAddLink" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="add-type">Type *</Label>
                            <Select v-model="addForm.type">
                                <SelectTrigger id="add-type">
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="(label, value) in link_types"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="addForm.errors.type" class="text-sm text-destructive">
                                {{ addForm.errors.type }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="add-label">Label *</Label>
                            <Input
                                id="add-label"
                                v-model="addForm.label"
                                type="text"
                                placeholder="Visit our Instagram"
                                :class="{ 'border-destructive': addForm.errors.label }"
                                required
                            />
                            <p v-if="addForm.errors.label" class="text-sm text-destructive">
                                {{ addForm.errors.label }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="add-url">URL *</Label>
                        <Input
                            id="add-url"
                            v-model="addForm.url"
                            type="url"
                            placeholder="https://instagram.com/yourbusiness"
                            :class="{ 'border-destructive': addForm.errors.url }"
                            required
                        />
                        <p v-if="addForm.errors.url" class="text-sm text-destructive">
                            {{ addForm.errors.url }}
                        </p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Checkbox
                            id="add-active"
                            :checked="addForm.is_active"
                            @update:checked="addForm.is_active = $event"
                        />
                        <Label for="add-active" class="cursor-pointer">
                            Active (visible on public page)
                        </Label>
                    </div>
                    <p v-if="addForm.errors.is_active" class="text-sm text-destructive">
                        {{ addForm.errors.is_active }}
                    </p>

                    <div class="flex gap-2">
                        <Button type="submit" :disabled="addForm.processing">
                            <Save class="mr-2 h-4 w-4" />
                            {{ addForm.processing ? 'Adding...' : 'Add Link' }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="showAddForm = false"
                        >
                            Cancel
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>

        <!-- Add Link Button -->
        <Button
            v-if="!showAddForm"
            @click="showAddForm = true"
            :disabled="!can_add_more && addForm.is_active"
            class="w-fit"
        >
            <Plus class="mr-2 h-4 w-4" />
            Add Link
        </Button>

        <!-- Links List -->
        <Card>
            <CardHeader>
                <CardTitle>Links ({{ links.length }})</CardTitle>
                <CardDescription>Manage and reorder your business links</CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="sortedLinks.length > 0" class="space-y-3">
                    <div
                        v-for="link in sortedLinks"
                        :key="link.id"
                        class="rounded-lg border p-4"
                    >
                        <!-- View Mode -->
                        <div v-if="editingId !== link.id" class="flex items-start justify-between">
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center gap-2">
                                    <Badge :variant="link.is_active ? 'default' : 'secondary'">
                                        {{ link_types[link.type] }}
                                    </Badge>
                                    <Badge v-if="!link.is_active" variant="outline">
                                        Inactive
                                    </Badge>
                                </div>
                                <p class="font-medium">{{ link.label }}</p>
                                <p class="text-sm text-muted-foreground">{{ link.url }}</p>
                            </div>
                            <div class="flex gap-1">
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    @click="moveUp(link)"
                                    :disabled="sortedLinks.findIndex((l) => l.id === link.id) === 0"
                                >
                                    <ChevronUp class="h-4 w-4" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    @click="moveDown(link)"
                                    :disabled="sortedLinks.findIndex((l) => l.id === link.id) === sortedLinks.length - 1"
                                >
                                    <ChevronDown class="h-4 w-4" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    @click="startEdit(link)"
                                >
                                    <Edit class="h-4 w-4" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    @click="handleDeleteLink(link.id, link.label)"
                                >
                                    <Trash2 class="h-4 w-4 text-destructive" />
                                </Button>
                            </div>
                        </div>

                        <!-- Edit Mode -->
                        <form
                            v-else
                            @submit.prevent="handleUpdateLink(link.id)"
                            class="space-y-4"
                        >
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label :for="`edit-type-${link.id}`">Type *</Label>
                                    <Select v-model="editForm.type">
                                        <SelectTrigger :id="`edit-type-${link.id}`">
                                            <SelectValue placeholder="Select type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="(label, value) in link_types"
                                                :key="value"
                                                :value="value"
                                            >
                                                {{ label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="space-y-2">
                                    <Label :for="`edit-label-${link.id}`">Label *</Label>
                                    <Input
                                        :id="`edit-label-${link.id}`"
                                        v-model="editForm.label"
                                        type="text"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label :for="`edit-url-${link.id}`">URL *</Label>
                                <Input
                                    :id="`edit-url-${link.id}`"
                                    v-model="editForm.url"
                                    type="url"
                                    required
                                />
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox
                                    :id="`edit-active-${link.id}`"
                                    :checked="editForm.is_active"
                                    @update:checked="editForm.is_active = $event"
                                />
                                <Label :for="`edit-active-${link.id}`" class="cursor-pointer">
                                    Active (visible on public page)
                                </Label>
                            </div>
                            <p v-if="editForm.errors.is_active" class="text-sm text-destructive">
                                {{ editForm.errors.is_active }}
                            </p>

                            <div class="flex gap-2">
                                <Button type="submit" :disabled="editForm.processing" size="sm">
                                    <Save class="mr-2 h-4 w-4" />
                                    {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="cancelEdit"
                                >
                                    Cancel
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
                <p v-else class="text-center text-sm text-muted-foreground py-8">
                    No links added yet. Click "Add Link" to create your first link.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
