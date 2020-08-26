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

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $name
 * @property array|null $description
 * @property float $price
 * @property bool $countable
 * @property bool $resettable
 * @property bool $extendable
 * @property string|null $quantifier
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read array $translations
 * @property-read Collection|Plan[] $plans
 * @property-read int|null $plans_count
 * @method static \Illuminate\Database\Eloquent\Builder|Feature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature newQuery()
 * @method static Builder|Feature onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature query()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereQuantifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereUpdatedAt($value)
 * @method static Builder|Feature withTrashed()
 * @method static Builder|Feature withoutTrashed()
 * @method static self firstWhere(string $string, string $value)
 * @method static self create(array $array)
 */
class Feature extends Model
{
    use HasTranslations, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'price', 'quantifier', 'countable', 'resettable', 'extendable'];

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
     * @return Feature
     */
    public static function byName(string $featureName): Feature
    {
        return self::firstWhere('name', $featureName);
    }

    /**
     * Scope countable features
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCountable(\Illuminate\Database\Eloquent\Builder $builder)
    {
        return $builder->where('countable', "=", 1);
    }

    /**
     * Scope resettable features
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeResettable(\Illuminate\Database\Eloquent\Builder $builder)
    {
        return $builder->where('resettable', "=", 1);
    }

    /**
     * Scope not resettable features
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotResettable(\Illuminate\Database\Eloquent\Builder $builder)
    {
        return $builder->where('resettable', "=", 0);
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
        return data_get($plan->features()->firstWhere('name', $this->name), 'pivot.value', 0);
    }

    /**
     * Resettable period in a plan for the feature
     *
     * @param Plan $plan
     * @return ?int
     */
    public function resettablePeriodInPlan(Plan $plan): ?int
    {
        return data_get($plan->features()->firstWhere('name', $this->name), 'pivot.resettable_period', 0);
    }

    /**
     * Resettable period in a plan for the feature
     *
     * @param Plan $plan
     * @return ?string
     */
    public function resettableIntervalInPlan(Plan $plan): ?string
    {
        return data_get($plan->features()->firstWhere('name', $this->name), 'pivot.resettable_interval');
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