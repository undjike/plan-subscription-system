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

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Translatable\HasTranslations;
use Undjike\PlanSubscriptionSystem\Traits\HasFeature;

/**
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property float $signup_fee
 * @property bool $dedicated
 * @property integer $trial_period
 * @property string $trial_interval
 * @property integer $invoice_period
 * @property string $invoice_interval
 * @property integer $grace_period
 * @property string $grace_interval
 * @property-read Collection|Feature[] $features
 * @property-read Collection|Subscription[] $subscriptions
 * @property-read Collection|Subscription[] $activeSubscriptions
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 * @method Builder withAnyFeature()
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
     * @param Builder $query
     * @param string|array|mixed $featureName
     * @return Builder
     */
    public function scopeWithAnyFeature(Builder $query, ...$featureName)
    {
        return $query->whereHas('features', function (Builder $query) use ($featureName) {
            $query->whereIn('name', (array) $featureName);
        });
    }
}