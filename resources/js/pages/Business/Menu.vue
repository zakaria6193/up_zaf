<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref, computed } from 'vue';
import { Plus, Edit, Trash2, Save, X, FolderPlus } from 'lucide-vue-next';
import BusinessSwitcher from '@/components/BusinessSwitcher.vue';

interface MenuItem {
    id: number;
    name: string;
    description: string | null;
    price: string;
    image: string | null;
    order: number;
}

interface Subcategory {
    id: number;
    name: string;
    order: number;
    parent_id: number;
    items: MenuItem[];
}

interface Category {
    id: number;
    name: string;
    order: number;
    parent_id: number | null;
    items: MenuItem[];
    subcategories: Subcategory[];
}

const props = defineProps<{
    business: {
        nanoid: string;
        name: string;
    };
    categories: Category[];
    userBusinesses: Array<{
        nanoid: string;
        name: string;
    }>;
}>();

const showAddCategoryForm = ref(false);
const showAddSubcategoryForm = ref<number | null>(null);
const showAddItemForm = ref<{categoryId: number; subcategoryId?: number} | null>(null);
const editingCategoryId = ref<number | null>(null);
const editingItemId = ref<number | null>(null);

const addCategoryForm = useForm({
    name: '',
    parent_id: null as number | null,
});

const editCategoryForm = useForm({
    name: '',
});

const addItemForm = useForm({
    name: '',
    description: '',
    price: '',
});

const editItemForm = useForm({
    name: '',
    description: '',
    price: '',
});

const sortedCategories = computed(() => {
    return [...props.categories].sort((a, b) => a.order - b.order);
});

const sortedSubcategories = (categoryId: number) => {
    const category = props.categories.find((c) => c.id === categoryId);
    if (!category) return [];
    return [...category.subcategories].sort((a, b) => a.order - b.order);
};

const sortedItems = (categoryId: number, subcategoryId?: number) => {
    const category = props.categories.find((c) => c.id === categoryId);
    if (!category) return [];

    if (subcategoryId) {
        const subcategory = category.subcategories.find((s) => s.id === subcategoryId);
        return subcategory ? [...subcategory.items].sort((a, b) => a.order - b.order) : [];
    }

    return [...category.items].sort((a, b) => a.order - b.order);
};

// Category handlers
const handleAddCategory = () => {
    addCategoryForm.post(route('business.menu.categories.store', props.business.nanoid), {
        preserveScroll: true,
        onSuccess: () => {
            addCategoryForm.reset();
            showAddCategoryForm.value = false;
        },
    });
};

const handleAddSubcategory = (parentId: number) => {
    addCategoryForm.parent_id = parentId;
    addCategoryForm.post(route('business.menu.categories.store', props.business.nanoid), {
        preserveScroll: true,
        onSuccess: () => {
            addCategoryForm.reset();
            showAddSubcategoryForm.value = null;
        },
    });
};

const startEditCategory = (category: Category | Subcategory) => {
    editingCategoryId.value = category.id;
    editCategoryForm.name = category.name;
};

const cancelEditCategory = () => {
    editingCategoryId.value = null;
    editCategoryForm.reset();
};

const handleUpdateCategory = (categoryId: number) => {
    editCategoryForm.put(route('business.menu.categories.update', [props.business.nanoid, categoryId]), {
        preserveScroll: true,
        onSuccess: () => {
            editingCategoryId.value = null;
            editCategoryForm.reset();
        },
    });
};

const handleDeleteCategory = (categoryId: number, categoryName: string) => {
    if (confirm(`Are you sure you want to delete "${categoryName}"? This will also delete all items in this category.`)) {
        router.delete(route('business.menu.categories.destroy', [props.business.nanoid, categoryId]));
    }
};

// Item handlers (inline quick add)
const handleQuickAddItem = (categoryId: number, subcategoryId?: number) => {
    const targetCategoryId = subcategoryId || categoryId;
    addItemForm.post(route('business.menu.items.store', [props.business.nanoid, targetCategoryId]), {
        preserveScroll: true,
        onSuccess: () => {
            addItemForm.reset();
            showAddItemForm.value = null;
        },
    });
};

const startEditItem = (item: MenuItem) => {
    editingItemId.value = item.id;
    editItemForm.name = item.name;
    editItemForm.description = item.description || '';
    editItemForm.price = item.price;
};

const cancelEditItem = () => {
    editingItemId.value = null;
    editItemForm.reset();
};

const handleUpdateItem = (categoryId: number, itemId: number) => {
    editItemForm.post(route('business.menu.items.update', [props.business.nanoid, categoryId, itemId]), {
        preserveScroll: true,
        onSuccess: () => {
            editingItemId.value = null;
            editItemForm.reset();
        },
    });
};

const handleDeleteItem = (categoryId: number, itemId: number, itemName: string) => {
    if (confirm(`Are you sure you want to delete "${itemName}"?`)) {
        router.delete(route('business.menu.items.destroy', [props.business.nanoid, categoryId, itemId]));
    }
};
</script>

<template>
    <Head title="Menu" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-3xl font-bold">Menu</h1>
                <p class="text-sm text-muted-foreground">
                    Manage your menu categories and items
                </p>
            </div>

            <!-- Business Selector (if multiple businesses) -->
            <BusinessSwitcher
                :businesses="userBusinesses"
                :current-nanoid="business.nanoid"
                route="business.menu"
                label="Managing:"
            />
        </div>

        <!-- Quick Add Category Form -->
        <Card v-if="showAddCategoryForm" class="bg-blue-50 border-blue-200">
            <CardContent class="pt-6">
                <form @submit.prevent="handleAddCategory" class="flex gap-3 items-end">
                    <div class="flex-1">
                        <Label for="quick-add-category" class="sr-only">Category Name</Label>
                        <Input
                            id="quick-add-category"
                            v-model="addCategoryForm.name"
                            type="text"
                            placeholder="Enter category name (e.g., Appetizers, Main Courses...)"
                            :class="{ 'border-destructive': addCategoryForm.errors.name }"
                            required
                            autofocus
                        />
                    </div>
                    <Button type="submit" :disabled="addCategoryForm.processing">
                        <Save class="mr-2 h-4 w-4" />
                        Add
                    </Button>
                    <Button type="button" variant="outline" @click="showAddCategoryForm = false">
                        <X class="h-4 w-4" />
                    </Button>
                </form>
            </CardContent>
        </Card>

        <!-- Add Category Button -->
        <Button
            v-if="!showAddCategoryForm"
            @click="showAddCategoryForm = true"
            class="w-fit"
        >
            <Plus class="mr-2 h-4 w-4" />
            Add Category
        </Button>

        <!-- Categories List -->
        <div v-if="sortedCategories.length > 0" class="space-y-4">
            <Card
                v-for="category in sortedCategories"
                :key="category.id"
                class="overflow-hidden"
            >
                <!-- Category Header -->
                <CardHeader class="bg-gray-50 border-b">
                    <div v-if="editingCategoryId !== category.id" class="flex items-start justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                {{ category.name }}
                                <Badge variant="secondary" class="text-xs">
                                    {{ category.items.length + category.subcategories.reduce((sum, sub) => sum + sub.items.length, 0) }} items
                                </Badge>
                            </CardTitle>
                            <CardDescription>
                                {{ category.subcategories.length > 0 ? `${category.subcategories.length} subcategories` : 'Main category' }}
                            </CardDescription>
                        </div>
                        <div class="flex gap-1">
                            <Button
                                size="sm"
                                variant="ghost"
                                @click="showAddSubcategoryForm = category.id"
                                v-if="showAddSubcategoryForm !== category.id"
                            >
                                <FolderPlus class="mr-2 h-4 w-4" />
                                Add Subcategory
                            </Button>
                            <Button size="icon" variant="ghost" @click="startEditCategory(category)">
                                <Edit class="h-4 w-4" />
                            </Button>
                            <Button size="icon" variant="ghost" @click="handleDeleteCategory(category.id, category.name)">
                                <Trash2 class="h-4 w-4 text-destructive" />
                            </Button>
                        </div>
                    </div>

                    <!-- Category Edit Mode -->
                    <form v-else @submit.prevent="handleUpdateCategory(category.id)" class="flex gap-2">
                        <Input v-model="editCategoryForm.name" type="text" required class="flex-1" />
                        <Button type="submit" size="sm" :disabled="editCategoryForm.processing">
                            <Save class="h-4 w-4" />
                        </Button>
                        <Button type="button" size="sm" variant="outline" @click="cancelEditCategory">
                            <X class="h-4 w-4" />
                        </Button>
                    </form>
                </CardHeader>

                <CardContent class="p-4 space-y-4">
                    <!-- Add Subcategory Form (Inline) -->
                    <div v-if="showAddSubcategoryForm === category.id" class="bg-gray-50 rounded-lg p-3 border">
                        <form @submit.prevent="handleAddSubcategory(category.id)" class="flex gap-2">
                            <Input
                                v-model="addCategoryForm.name"
                                type="text"
                                placeholder="Subcategory name (e.g., Beef, Chicken...)"
                                required
                                class="flex-1"
                                autofocus
                            />
                            <Button type="submit" size="sm" :disabled="addCategoryForm.processing">
                                <Save class="h-4 w-4" />
                            </Button>
                            <Button type="button" size="sm" variant="outline" @click="showAddSubcategoryForm = null">
                                <X class="h-4 w-4" />
                            </Button>
                        </form>
                    </div>

                    <!-- Category Direct Items (if no subcategories or mixed) -->
                    <div v-if="category.items.length > 0 || showAddItemForm?.categoryId === category.id && !showAddItemForm.subcategoryId">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-700">Items</h4>
                            <Button
                                v-if="showAddItemForm?.categoryId !== category.id || showAddItemForm?.subcategoryId"
                                size="sm"
                                variant="outline"
                                @click="showAddItemForm = { categoryId: category.id }"
                            >
                                <Plus class="mr-1 h-3 w-3" />
                                Quick Add
                            </Button>
                        </div>

                        <!-- Quick Add Item Form -->
                        <div v-if="showAddItemForm?.categoryId === category.id && !showAddItemForm.subcategoryId" class="bg-blue-50 rounded-lg p-3 border border-blue-200 mb-3">
                            <form @submit.prevent="handleQuickAddItem(category.id)" class="grid grid-cols-[2fr_1fr_1fr_auto] gap-2">
                                <Input v-model="addItemForm.name" type="text" placeholder="Item name" required size="sm" />
                                <Input v-model="addItemForm.price" type="number" step="0.01" placeholder="Price" required size="sm" />
                                <Input v-model="addItemForm.description" type="text" placeholder="Description (optional)" size="sm" />
                                <div class="flex gap-1">
                                    <Button type="submit" size="sm" :disabled="addItemForm.processing">Add</Button>
                                    <Button type="button" size="sm" variant="ghost" @click="showAddItemForm = null"><X class="h-3 w-3" /></Button>
                                </div>
                            </form>
                        </div>

                        <!-- Category Items List -->
                        <div class="space-y-2">
                            <div
                                v-for="item in sortedItems(category.id)"
                                :key="item.id"
                                class="flex items-center justify-between p-2 rounded border bg-white hover:bg-gray-50"
                            >
                                <div v-if="editingItemId !== item.id" class="flex-1">
                                    <p class="font-medium text-sm">{{ item.name }}</p>
                                    <p v-if="item.description" class="text-xs text-gray-600">{{ item.description }}</p>
                                    <p class="text-sm font-semibold text-primary">{{ item.price }} MAD</p>
                                </div>
                                <form v-else @submit.prevent="handleUpdateItem(category.id, item.id)" class="flex-1 flex gap-2">
                                    <Input v-model="editItemForm.name" type="text" size="sm" required />
                                    <Input v-model="editItemForm.price" type="number" step="0.01" size="sm" required class="w-24" />
                                    <Button type="submit" size="sm"><Save class="h-3 w-3" /></Button>
                                    <Button type="button" size="sm" variant="ghost" @click="cancelEditItem"><X class="h-3 w-3" /></Button>
                                </form>
                                <div v-if="editingItemId !== item.id" class="flex gap-1">
                                    <Button size="sm" variant="ghost" @click="startEditItem(item)">
                                        <Edit class="h-3 w-3" />
                                    </Button>
                                    <Button size="sm" variant="ghost" @click="handleDeleteItem(category.id, item.id, item.name)">
                                        <Trash2 class="h-3 w-3 text-destructive" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subcategories -->
                    <div v-if="category.subcategories.length > 0" class="space-y-3">
                        <div
                            v-for="subcategory in sortedSubcategories(category.id)"
                            :key="subcategory.id"
                            class="border-l-4 border-blue-300 pl-4 space-y-2"
                        >
                            <!-- Subcategory Header -->
                            <div class="flex items-center justify-between">
                                <div v-if="editingCategoryId !== subcategory.id">
                                    <h4 class="font-semibold text-sm text-gray-800">{{ subcategory.name }}</h4>
                                    <p class="text-xs text-gray-600">{{ subcategory.items.length }} items</p>
                                </div>
                                <form v-else @submit.prevent="handleUpdateCategory(subcategory.id)" class="flex gap-2 flex-1">
                                    <Input v-model="editCategoryForm.name" type="text" size="sm" required class="flex-1" />
                                    <Button type="submit" size="sm"><Save class="h-3 w-3" /></Button>
                                    <Button type="button" size="sm" variant="ghost" @click="cancelEditCategory"><X class="h-3 w-3" /></Button>
                                </form>
                                <div v-if="editingCategoryId !== subcategory.id" class="flex gap-1">
                                    <Button
                                        v-if="showAddItemForm?.categoryId !== category.id || showAddItemForm?.subcategoryId !== subcategory.id"
                                        size="sm"
                                        variant="ghost"
                                        @click="showAddItemForm = { categoryId: category.id, subcategoryId: subcategory.id }"
                                    >
                                        <Plus class="h-3 w-3" />
                                    </Button>
                                    <Button size="sm" variant="ghost" @click="startEditCategory(subcategory)">
                                        <Edit class="h-3 w-3" />
                                    </Button>
                                    <Button size="sm" variant="ghost" @click="handleDeleteCategory(subcategory.id, subcategory.name)">
                                        <Trash2 class="h-3 w-3 text-destructive" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Quick Add Item for Subcategory -->
                            <div v-if="showAddItemForm?.categoryId === category.id && showAddItemForm?.subcategoryId === subcategory.id" class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                                <form @submit.prevent="handleQuickAddItem(category.id, subcategory.id)" class="grid grid-cols-[2fr_1fr_1fr_auto] gap-2">
                                    <Input v-model="addItemForm.name" type="text" placeholder="Item name" required size="sm" />
                                    <Input v-model="addItemForm.price" type="number" step="0.01" placeholder="Price" required size="sm" />
                                    <Input v-model="addItemForm.description" type="text" placeholder="Description" size="sm" />
                                    <div class="flex gap-1">
                                        <Button type="submit" size="sm" :disabled="addItemForm.processing">Add</Button>
                                        <Button type="button" size="sm" variant="ghost" @click="showAddItemForm = null"><X class="h-3 w-3" /></Button>
                                    </div>
                                </form>
                            </div>

                            <!-- Subcategory Items -->
                            <div class="space-y-2">
                                <div
                                    v-for="item in sortedItems(category.id, subcategory.id)"
                                    :key="item.id"
                                    class="flex items-center justify-between p-2 rounded border bg-white hover:bg-gray-50"
                                >
                                    <div v-if="editingItemId !== item.id" class="flex-1">
                                        <p class="font-medium text-sm">{{ item.name }}</p>
                                        <p v-if="item.description" class="text-xs text-gray-600">{{ item.description }}</p>
                                        <p class="text-sm font-semibold text-primary">{{ item.price }} MAD</p>
                                    </div>
                                    <form v-else @submit.prevent="handleUpdateItem(subcategory.id, item.id)" class="flex-1 flex gap-2">
                                        <Input v-model="editItemForm.name" type="text" size="sm" required />
                                        <Input v-model="editItemForm.price" type="number" step="0.01" size="sm" required class="w-24" />
                                        <Button type="submit" size="sm"><Save class="h-3 w-3" /></Button>
                                        <Button type="button" size="sm" variant="ghost" @click="cancelEditItem"><X class="h-3 w-3" /></Button>
                                    </form>
                                    <div v-if="editingItemId !== item.id" class="flex gap-1">
                                        <Button size="sm" variant="ghost" @click="startEditItem(item)">
                                            <Edit class="h-3 w-3" />
                                        </Button>
                                        <Button size="sm" variant="ghost" @click="handleDeleteItem(subcategory.id, item.id, item.name)">
                                            <Trash2 class="h-3 w-3 text-destructive" />
                                        </Button>
                                    </div>
                                </div>
                                <p v-if="subcategory.items.length === 0" class="text-xs text-gray-500 text-center py-2">No items yet</p>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="category.items.length === 0 && category.subcategories.length === 0" class="text-center py-8 text-sm text-gray-500">
                        <p>No items or subcategories yet.</p>
                        <div class="flex gap-2 justify-center mt-3">
                            <Button size="sm" variant="outline" @click="showAddSubcategoryForm = category.id">
                                <FolderPlus class="mr-2 h-3 w-3" />
                                Add Subcategory
                            </Button>
                            <Button size="sm" variant="outline" @click="showAddItemForm = { categoryId: category.id }">
                                <Plus class="mr-2 h-3 w-3" />
                                Add Item
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Empty State -->
        <Card v-else>
            <CardContent class="py-12">
                <p class="text-center text-sm text-muted-foreground">
                    No categories added yet. Click "Add Category" to create your first menu category.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
