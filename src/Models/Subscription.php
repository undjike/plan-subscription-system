<?php

/*
 * Subscription.php
 *
 *  @author    Ulrich Pascal Ndjike Zoa <ndjikezoaulrich@gmail.com>
 *  @project    plan-subscription-system
 *
 *  Copyright 2020
 *  18/08/2020 18:02
 */

namespace Undjike\PlanSubscriptionSystem\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read Plan plan
 * @property-read Collection|Feature[] features
 * @property-read Model
 */
class Subscription extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['price', 'stars_at', 'ends_at', 'canceled_at', 'timezone', 'plan_id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['plan_id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'stars_at' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime'
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['plan', 'subscriber'];

    /**
     * Get the subscriber.
     *
     * @return MorphTo
     */
    public function subscriber(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Subscription's plan
     *
     * @return BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Subscription's features
     * 
     * @return BelongsToMany
     */
    public function features()
    {
        return $this->plan->features();
    }

    /**
     * Subscription's features
     *
     * @return Collection|Feature[]
     */
    public function getFeaturesAttribute()
    {
        return $this->plan->features;
    }

    /**
     * Subscription's usages
     *
     * @return HasMany
     */
    public function usages()
    {
        return $this->hasMany(Usage::class);
    }

    /**
     * Subscription's usages
     *
     * @return HasMany
     */
    public function supplements()
    {
        return $this->hasMany(Supplement::class);
    }

    /**
     * Total usage of feature in the subscription
     *
     * @param Feature $feature
     * @return int|mixed
     */
    public function totalUsageOf(Feature $feature)
    {
        return $this->usages()->where('feature_id', $feature->id)->sum('used');
    }

    /**
     * Total supplement of feature in the subscription
     *
     * @param Feature $feature
     * @return int|mixed
     */
    public function totalSupplementOf(Feature $feature)
    {
        return $this->supplements()->where('feature_id', $feature->id)->sum('value');
    }

    /**
     * Remaining usage of a feature in the subscription
     *
     * @param Feature $feature
     * @return int|mixed|null
     */
    public function remainingUsageOf(Feature $feature)
    {
        return $feature->valueIn($this->plan) + $this->totalSupplementOf($feature) - $this->totalUsageOf($feature);
    }

    /**
     * Only subscription ending in a short period
     *
     * @param Builder $query
     * @param int $dayRange
     * @return Builder
     */
    public function scopeFindEndingPeriod(Builder $query, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $query->whereBetween('ends_at', [$from, $to]);
    }
}