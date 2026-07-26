<?php

namespace App\Models;

use App\Services\QrCardGenerator;
use App\Support\Currency;
use App\Support\QrStyle;
use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'currency',
        'qr_code',
        'qr_style',
        'qr_label',
        'qr_headline',
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
     * Get the business display currency (ISO 4217), falling back to MAD.
     */
    public function currencyCode(): string
    {
        $currency = strtoupper((string) ($this->currency ?: 'MAD'));

        return Currency::isValid($currency) ? $currency : 'MAD';
    }

    /**
     * Get the selected QR card design.
     */
    public function qrStyleCode(): string
    {
        return QrStyle::normalize($this->qr_style, filled($this->logo));
    }

    /**
     * Small label printed above the headline (guest-facing).
     */
    public function qrLabelText(): string
    {
        $label = trim((string) $this->qr_label);

        return $label !== '' ? mb_substr($label, 0, 24) : 'MENU';
    }

    /**
     * Main headline printed on the QR card (guest-facing).
     */
    public function qrHeadlineText(): string
    {
        $headline = trim((string) $this->qr_headline);
        if ($headline !== '') {
            return mb_substr($headline, 0, 48);
        }

        $style = $this->qrStyleCode();

        return QrStyle::options()[$style]['tagline'] ?? 'Discover the menu';
    }

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

            if (! $business->qr_style) {
                $business->qr_style = QrStyle::default();
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
     * Generate a designed QR code card for this business.
     */
    public function generateQrCode(): void
    {
        $filename = app(QrCardGenerator::class)->generate($this);

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
     * Get the QR code URL (host-relative so tunnels / public links work).
     *
     * Appends a version query so browsers refresh after regenerating the same file path.
     */
    public function qrCodeUrl(): ?string
    {
        if (! $this->qr_code) {
            return null;
        }

        $version = collect([
            $this->qr_style,
            $this->qr_label,
            $this->qr_headline,
            $this->updated_at?->timestamp,
            $this->logo,
            $this->color,
            $this->name,
        ])->filter()->implode('-');

        return '/storage/'.$this->qr_code.'?v='.substr(sha1((string) $version), 0, 12);
    }

    /**
     * Get the logo URL (host-relative so tunnels / public links work).
     */
    public function logoUrl(): ?string
    {
        return $this->logo ? '/storage/'.$this->logo : null;
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
