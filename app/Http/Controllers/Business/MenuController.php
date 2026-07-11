<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    /**
     * Display menu management page.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();

        // Get the business to manage (either from query param or first business)
        $businessId = $request->query('business');
        if ($businessId) {
            $business = $user->businesses()->where('nanoid', $businessId)->firstOrFail();
        } else {
            $business = $user->businesses()->first();
        }

        // If user has no businesses, redirect to dashboard
        if (! $business) {
            return redirect()->route('business.dashboard');
        }

        $business->load(['menuCategories.items', 'menuCategories.subcategories.items']);

        // Only get parent categories (no parent_id)
        $parentCategories = $business->menuCategories->filter(fn ($cat) => is_null($cat->parent_id));

        return Inertia::render('Business/Menu', [
            'business' => [
                'nanoid' => $business->nanoid,
                'name' => $business->name,
            ],
            'categories' => $parentCategories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'order' => $category->order,
                'parent_id' => $category->parent_id,
                'items' => $category->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'image' => $item->imageUrl(),
                    'order' => $item->order,
                ]),
                'subcategories' => $category->subcategories->map(fn ($subcategory) => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'order' => $subcategory->order,
                    'parent_id' => $subcategory->parent_id,
                    'items' => $subcategory->items->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'price' => $item->price,
                        'image' => $item->imageUrl(),
                        'order' => $item->order,
                    ]),
                ]),
            ]),
            'userBusinesses' => $user->businesses->map(fn ($b) => [
                'nanoid' => $b->nanoid,
                'name' => $b->name,
            ]),
        ]);
    }

    /**
     * Store a new category or subcategory.
     */
    public function storeCategory(Request $request, string $nanoid): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:menu_categories,id'],
        ]);

        // If parent_id is provided, get max order of that parent's subcategories
        if (isset($validated['parent_id'])) {
            $maxOrder = MenuCategory::where('parent_id', $validated['parent_id'])->max('order') ?? -1;
        } else {
            // Get max order of parent categories (those without parent_id)
            $maxOrder = $business->menuCategories()->whereNull('parent_id')->max('order') ?? -1;
        }

        $validated['order'] = $maxOrder + 1;

        $business->menuCategories()->create($validated);

        return back()->with('success', $validated['parent_id'] ? 'Subcategory added successfully!' : 'Category added successfully!');
    }

    /**
     * Update a category.
     */
    public function updateCategory(Request $request, string $nanoid, MenuCategory $category): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        // Ensure the category belongs to this business
        if ($category->business_id !== $business->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category->update($validated);

        return back()->with('success', 'Category updated successfully!');
    }

    /**
     * Delete a category.
     */
    public function destroyCategory(string $nanoid, MenuCategory $category): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        // Ensure the category belongs to this business
        if ($category->business_id !== $business->id) {
            abort(403);
        }

        // Delete all item images in this category
        foreach ($category->items as $item) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
        }

        $order = $category->order;
        $category->delete();

        // Reorder remaining categories
        $business->menuCategories()->where('order', '>', $order)
            ->decrement('order');

        return back()->with('success', 'Category deleted successfully!');
    }

    /**
     * Reorder categories.
     */
    public function reorderCategories(Request $request, string $nanoid): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        $validated = $request->validate([
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'exists:menu_categories,id'],
            'categories.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['categories'] as $categoryData) {
            MenuCategory::where('id', $categoryData['id'])
                ->where('business_id', $business->id)
                ->update(['order' => $categoryData['order']]);
        }

        return back()->with('success', 'Categories reordered successfully!');
    }

    /**
     * Store a new menu item.
     */
    public function storeItem(Request $request, string $nanoid, MenuCategory $category): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        // Ensure the category belongs to this business
        if ($category->business_id !== $business->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('menu-items', 'public');
        }

        $maxOrder = $category->items()->max('order') ?? -1;
        $validated['order'] = $maxOrder + 1;

        $category->items()->create($validated);

        return back()->with('success', 'Menu item added successfully!');
    }

    /**
     * Update a menu item.
     */
    public function updateItem(Request $request, string $nanoid, MenuCategory $category, MenuItem $item): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        // Ensure the category belongs to this business
        if ($category->business_id !== $business->id || $item->category_id !== $category->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $validated['image'] = $request->file('image')->store('menu-items', 'public');
        }

        $item->update($validated);

        return back()->with('success', 'Menu item updated successfully!');
    }

    /**
     * Delete a menu item.
     */
    public function destroyItem(string $nanoid, MenuCategory $category, MenuItem $item): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        // Ensure the category belongs to this business
        if ($category->business_id !== $business->id || $item->category_id !== $category->id) {
            abort(403);
        }

        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $order = $item->order;
        $item->delete();

        // Reorder remaining items
        $category->items()->where('order', '>', $order)
            ->decrement('order');

        return back()->with('success', 'Menu item deleted successfully!');
    }

    /**
     * Reorder menu items.
     */
    public function reorderItems(Request $request, string $nanoid, MenuCategory $category): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        // Ensure the category belongs to this business
        if ($category->business_id !== $business->id) {
            abort(403);
        }

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:menu_items,id'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['items'] as $itemData) {
            MenuItem::where('id', $itemData['id'])
                ->where('category_id', $category->id)
                ->update(['order' => $itemData['order']]);
        }

        return back()->with('success', 'Items reordered successfully!');
    }
}
