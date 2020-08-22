<?php

/*
 * Plan.php
 *
 *  @author    Ulrich Pascal Ndjike Zoa <ndjikezoaulrich@gmail.com>
 *  @project    plan-subscription-system
 *
 *  Copyright 2020
 *  18/08/2020 16:57
 */

namespace Undjike\PlanSubscriptionSystem\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;
use Undjike\PlanSubscriptionSystem\Traits\HasFeature;

/**
 * @property int $id
 * @property string $name
 * @property array|null $description
 * @property float $price
 * @property float $signup_fee
 * @property int $dedicated
 * @property int $trial_period
 * @property string $trial_interval
 * @property int $invoice_period
 * @property string $invoice_interval
 * @property int $grace_period
 * @property string $grace_interval
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection|Subscription[] $activeSubscriptions
 * @property-read int|null $active_subscriptions_count
 * @property-read Collection|Feature[] $features
 * @property-read int|null $features_count
 * @property-read array $translations
 * @property-read Collection|Subscription[] $subscriptions
 * @property-read int|null $subscriptions_count
 * @method static Builder|Plan newModelQuery()
 * @method static Builder|Plan newQuery()
 * @method static \Illuminate\Database\Query\Builder|Plan onlyTrashed()
 * @method static Builder|Plan query()
 * @method static Builder|Plan whereCreatedAt($value)
 * @method static Builder|Plan whereDedicated($value)
 * @method static Builder|Plan whereDeletedAt($value)
 * @method static Builder|Plan whereDescription($value)
 * @method static Builder|Plan whereGraceInterval($value)
 * @method static Builder|Plan whereGracePeriod($value)
 * @method static Builder|Plan whereId($value)
 * @method static Builder|Plan whereInvoiceInterval($value)
 * @method static Builder|Plan whereInvoicePeriod($value)
 * @method static Builder|Plan whereName($value)
 * @method static Builder|Plan wherePrice($value)
 * @method static Builder|Plan whereSignupFee($value)
 * @method static Builder|Plan whereTrialInterval($value)
 * @method static Builder|Plan whereTrialPeriod($value)
 * @method static Builder|Plan whereUpdatedAt($value)
 * @method static Builder|Plan withAnyFeature($featureName)
 * @method static \Illuminate\Database\Query\Builder|Plan withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Plan withoutTrashed()
 * @method static self firstWhere(string $string, string $value)
 * @method static self create(array $array)
 */
class Plan extends Model
{
    use HasTranslations, SoftDeletes, HasFeature;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'price', 'signup_fee', 'dedicated',
        'invoice_period', 'invoice_interval', 'grace_period', 'grace_interval',
        'trial_period', 'trial_interval'];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatable = ['description'];

    /**
     * Get plan by name
     *
     * @param string $planName
     * @return mixed
     */
    public static function byName(string $planName)
    {
        return self::firstWhere('name', $planName);
    }

    /**
     * All features attached to the plan
     *
     * @return BelongsToMany
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
                    ->withPivot(['value', 'resettable_period', 'resettable_interval']);
    }

    /**
     * All subscriptions to the plan
     *
     * @return HasMany
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Active subscriptions to the plan
     *
     * @return HasMany
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()->where(function (Builder $query) {
            $query->where('starts_at', '<=', now())
                  ->where('ends_at', '>', now())
                  ->whereNull('canceled_at');
        })->orWhere(function (Builder $query) {
            $query->whereNotNull('canceled_at');
        });
    }

    /**
     * Check if plan is free.
     *
     * @return bool
     */
    public function isFree(): bool
    {
        return (float) $this->price <= 0.00;
    }

    /**
     * Check if plan has trial.
     *
     * @return bool
     */
    public function hasTrial(): bool
    {
        return $this->trial_period && $this->trial_interval;
    }

    /**
     * Check if plan has grace.
     *
     * @return bool
     */
    public function hasGrace(): bool
    {
        return $this->grace_period && $this->grace_interval;
    }

    /**
     * Plans with any of the specified features
     *
     * @param Builder $builder
     * @param string|array|mixed $featureName
     * @return Builder
     */
    public function scopeWithAnyFeature(Builder $builder, ...$featureName)
    {
        return $builder->whereHas('features', function (Builder $query) use ($featureName) {
            $query->whereIn('name', (array) $featureName);
        });
    }
}