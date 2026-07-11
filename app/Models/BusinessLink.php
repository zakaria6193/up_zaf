<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessLink extends Model
{
    use HasFactory;
    protected $fillable = [
        'business_id',
        'type',
        'label',
        'url',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Link types available.
     */
    public const TYPES = [
        'google_reviews' => 'Google Reviews',
        'google_maps' => 'Google Maps',
        'menu' => 'Menu',
        'instagram' => 'Instagram',
        'whatsapp' => 'WhatsApp',
        'website' => 'Website',
        'other' => 'Other',
    ];

    /**
     * Get the business that owns this link.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
