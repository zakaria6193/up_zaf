<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\TwoFactorAuthenticatable;

class BusinessUser extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_premium',
        'trial_ends_at',
        'google_id',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_premium' => 'boolean',
            'trial_ends_at' => 'datetime',
        ];
    }

    /**
     * Get the businesses owned by this user.
     */
    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    /**
     * Whether this account has an active premium subscription.
     */
    public function isPremium(): bool
    {
        return (bool) $this->is_premium;
    }

    /**
     * Whether the free trial window is still open.
     */
    public function isOnTrial(): bool
    {
        if ($this->isPremium()) {
            return false;
        }

        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /**
     * Whether public client pages for this owner's businesses should be reachable.
     */
    public function publicPagesAccessible(): bool
    {
        return $this->isPremium() || $this->isOnTrial();
    }

    /**
     * Whether this user may create another enterprise.
     */
    public function canCreateBusiness(): bool
    {
        if ($this->isPremium()) {
            return true;
        }

        $limit = (int) config('business.free_business_limit', 1);

        return $this->businesses()->count() < $limit;
    }

    /**
     * Remaining trial seconds (0 when expired or premium).
     */
    public function trialSecondsRemaining(): int
    {
        if ($this->isPremium() || $this->trial_ends_at === null) {
            return 0;
        }

        return (int) max(0, $this->trial_ends_at->getTimestamp() - now()->getTimestamp());
    }

    /**
     * Start a free trial from now (used on registration / first Google login).
     */
    public function startFreeTrial(?Carbon $from = null): void
    {
        $minutes = max(1, (int) config('business.free_trial_minutes', 10));
        $this->forceFill([
            'is_premium' => false,
            'trial_ends_at' => ($from ?? now())->copy()->addMinutes($minutes),
        ])->save();
    }

    /**
     * Subscription payload shared with the Inertia frontend.
     *
     * @return array{is_premium: bool, is_on_trial: bool, trial_ends_at: string|null, trial_seconds_remaining: int, public_pages_accessible: bool, can_create_business: bool, free_business_limit: int}
     */
    public function subscriptionPayload(): array
    {
        return [
            'is_premium' => $this->isPremium(),
            'is_on_trial' => $this->isOnTrial(),
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),
            'trial_seconds_remaining' => $this->trialSecondsRemaining(),
            'public_pages_accessible' => $this->publicPagesAccessible(),
            'can_create_business' => $this->canCreateBusiness(),
            'free_business_limit' => (int) config('business.free_business_limit', 1),
        ];
    }
}
