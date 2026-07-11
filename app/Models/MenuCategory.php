<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'business_id',
        'parent_id',
        'name',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the business that owns this category.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get all items in this category.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'category_id')->orderBy('order');
    }

    /**
     * Get the parent category (for subcategories).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'parent_id');
    }

    /**
     * Get all subcategories (children) of this category.
     */
    public function subcategories(): HasMany
    {
        return $this->hasMany(MenuCategory::class, 'parent_id')->orderBy('order');
    }

    /**
     * Check if this is a parent category (no parent).
     */
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this is a subcategory (has a parent).
     */
    public function isSubcategory(): bool
    {
        return ! is_null($this->parent_id);
    }
}
