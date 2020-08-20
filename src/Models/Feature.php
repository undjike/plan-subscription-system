<?php

/*
 * Feature.php
 *
 *  @author    Ulrich Pascal Ndjike Zoa <ndjikezoaulrich@gmail.com>
 *  @project    plan-subscription-system
 *
 *  Copyright 2020
 *  18/08/2020 17:34
 */

namespace Undjike\PlanSubscriptionSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * @property int id
 * @property string name
 * @property string description
 * @property float price
 * @property string quantifier
 * @method static firstWhere(string $string, mixed $value = null)
 */
class Feature extends Model
{
    use HasTranslations, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'price', 'quantifier'];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatable = ['description'];

    /**
     * Get feature by name
     *
     * @param string $featureName
     * @return mixed
     */
    public static function byName(string $featureName)
    {
        return self::firstWhere('name', $featureName);
    }

    /**
     * All plans that have the feature
     *
     * @return BelongsToMany
     */
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_features');
    }

    /**
     * Check if the feature exists in the given plan
     *
     * @param Plan $plan
     * @return bool
     */
    public function existsInPlan(Plan $plan): bool
    {
        return $plan->hasFeature($this->name);
    }

    /**
     * Allowed value in a plan for the feature
     *
     * @param Plan $plan
     * @return ?float
     */
    public function valueInPlan(Plan $plan): ?float
    {
        return optional($plan->features()->firstWhere('name', $this->name))->pivot->value;
    }

    /**
     * Resettable period in a plan for the feature
     *
     * @param Plan $plan
     * @return ?int
     */
    public function resettablePeriodInPlan(Plan $plan): ?int
    {
        return optional($plan->features()->firstWhere('name', $this->name))->pivot->resettable_period;
    }

    /**
     * Resettable period in a plan for the feature
     *
     * @param Plan $plan
     * @return ?string
     */
    public function resettableIntervalInPlan(Plan $plan): ?string
    {
        return optional($plan->features()->firstWhere('name', $this->name))->pivot->resettable_interval;
    }

    /**
     * Total usage of feature in the subscription
     *
     * @param Subscription $subscription
     * @return int|mixed
     */
    public function totalUsageInSubscription(Subscription $subscription)
    {
        return $subscription->usages()->where('feature_id', $this->id)->sum('used');
    }

    /**
     * Remaining usage of a feature in the subscription
     *
     * @param Subscription $subscription
     * @return int|mixed|null
     */
    public function remainingUsageInSubscription(Subscription $subscription)
    {
        return $subscription->remainingUsageOfFeature($this);
    }
}