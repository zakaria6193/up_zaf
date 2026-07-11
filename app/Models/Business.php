<?php

namespace App\Models;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Business extends Model
{
    use HasFactory;
    protected $fillable = [
        'business_user_id',
        'nanoid',
        'name',
        'address',
        'lat',
        'lng',
        'logo',
        'color',
        'qr_code',
        'is_active',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Business $business) {
            if (! $business->nanoid) {
                $client = new Client;
                $business->nanoid = $client->generateId(8, Client::MODE_DYNAMIC);
            }
        });

        static::created(function (Business $business) {
            $business->generateQrCode();
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'nanoid';
    }

    /**
     * Generate QR code for this business.
     */
    public function generateQrCode(): void
    {
        $url = $this->publicUrl();

        $builder = new Builder(
            writer: new PngWriter,
            data: $url,
            size: 300,
            margin: 10
        );

        $result = $builder->build();

        $filename = "qrcodes/{$this->nanoid}.png";
        Storage::disk('public')->put($filename, $result->getString());

        $this->update(['qr_code' => $filename]);
    }

    /**
     * Get the public URL for this business.
     */
    public function publicUrl(): string
    {
        return url("/p/{$this->nanoid}");
    }

    /**
     * Get the QR code URL.
     */
    public function qrCodeUrl(): ?string
    {
        return $this->qr_code ? Storage::disk('public')->url($this->qr_code) : null;
    }

    /**
     * Get the logo URL.
     */
    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }

    /**
     * Get all links for this business.
     */
    public function links(): HasMany
    {
        return $this->hasMany(BusinessLink::class)->orderBy('order');
    }

    /**
     * Get only active links.
     */
    public function activeLinks(): HasMany
    {
        return $this->hasMany(BusinessLink::class)
            ->where('is_active', true)
            ->orderBy('order');
    }

    /**
     * Get all menu categories for this business.
     */
    public function menuCategories(): HasMany
    {
        return $this->hasMany(MenuCategory::class)->orderBy('order');
    }

    /**
     * Get the business user that owns this business.
     */
    public function businessUser(): BelongsTo
    {
        return $this->belongsTo(BusinessUser::class);
    }
}
