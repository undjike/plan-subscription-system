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

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use LogicException;
use Undjike\PlanSubscriptionSystem\Events\SubscriptionCancelled;
use Undjike\PlanSubscriptionSystem\Events\SubscriptionPlanChanged;
use Undjike\PlanSubscriptionSystem\Events\SubscriptionRenewed;
use Undjike\PlanSubscriptionSystem\Services\Period;
use Undjike\PlanSubscriptionSystem\Traits\HasFeature;

/**
 * @property int $id
 * @property string $subscriber_type
 * @property int $subscriber_id
 * @property float $price
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property Carbon|null $canceled_at
 * @property string|null $timezone
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $plan_id
 * @property-read Plan $plan
 * @property-read Carbon $trial_ends_at
 * @property-read Carbon $grace_ends_at
 * @property-read Model|Builder $subscriber
 * @property-read Collection|Supplement[] $supplements
 * @property-read int|null $supplements_count
 * @property-read Collection|Usage[] $usages
 * @property-read int|null $usages_count
 * @property-read Collection|Feature[] $features
 * @property-read int|null $features_count
 * @method static Builder|Subscription endedYet()
 * @method static Builder|Subscription endingPeriod($dayRange = 3)
 * @method static Builder|Subscription newModelQuery()
 * @method static Builder|Subscription newQuery()
 * @method static Builder|Subscription query()
 * @method static Builder|Subscription whereCanceledAt($value)
 * @method static Builder|Subscription whereCreatedAt($value)
 * @method static Builder|Subscription whereEndsAt($value)
 * @method static Builder|Subscription whereId($value)
 * @method static Builder|Subscription wherePlanId($value)
 * @method static Builder|Subscription wherePrice($value)
 * @method static Builder|Subscription whereStarsAt($value)
 * @method static Builder|Subscription whereSubscriberId($value)
 * @method static Builder|Subscription whereSubscriberType($value)
 * @method static Builder|Subscription whereTimezone($value)
 * @method static Builder|Subscription whereUpdatedAt($value)
 * @method static self firstWhere(string $string, string $value)
 * @method static self create(array $array)
 */
class Subscription extends Model
{
    use HasFeature;

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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['grace_ends_at', 'trial_ends_at'];

    /**
     * Auto-initialize subscription's start and end date
     *
     * @param Subscription $subscription
     * @throws Exception
     */
    protected static function periodInitializer(Subscription $subscription): void
    {
        if (!$subscription->starts_at || !$subscription->ends_at) $subscription->setNewPeriod();
    }

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function (self $subscription) {
            self::periodInitializer($subscription);
        });

        static::updating(function (self $subscription) {
            self::periodInitializer($subscription);
        });

        static::saving(function (self $subscription) {
            self::periodInitializer($subscription);
        });
    }

    /**
     * Set new subscription period.
     *
     * @param ?string|Carbon $start
     *
     * @param ?string $invoice_interval
     * @param ?int $invoice_period
     * @return $this
     * @throws Exception
     */
    protected function setNewPeriod($start = null, ?string $invoice_interval = null, ?int $invoice_period = null)
    {
        if (empty($invoice_interval)) $invoice_interval = $this->plan->invoice_interval;

        if (empty($invoice_period)) $invoice_period = $this->plan->invoice_period;

        $period = new Period($start, $invoice_interval, $invoice_period);

        $this->starts_at = $period->getStartDate();
        $this->ends_at = $period->getEndDate();

        return $this;
    }

    /**
     * Check if subscription is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return !$this->ended() && !$this->canceled();
    }

    /**
     * Check if subscription is canceled.
     *
     * @return bool
     */
    public function canceled(): bool
    {
        return $this->canceled_at ? Carbon::now()->gte($this->canceled_at) : false;
    }

    /**
     * Subscription trial period end date.
     *
     * @return ?Carbon
     */
    public function getTrialEndsAtAttribute(): ?Carbon
    {
        if ($this->plan->hasTrial()) {
            $method = 'add'.ucfirst($this->plan->trial_interval).'s';
            return $this->starts_at->{$method}($this->plan->trial_period);
        }

        return null;
    }

    /**
     * Subscription grace period end date.
     *
     * @return ?Carbon
     */
    public function getGraceEndsAtAttribute(): ?Carbon
    {
        if ($this->plan->hasGrace()) {
            $method = 'add'.ucfirst($this->plan->grace_interval).'s';
            return $this->ends_at->{$method}($this->plan->grace_period);
        }

        return null;
    }

    /**
     * Check if subscription is currently on trial.
     *
     * @return bool
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at ? Carbon::now()->lt($this->trial_ends_at) : false;
    }

    /**
     * Check if subscription is on grace period
     *
     * @return bool
     */
    public function isOnGrace(): bool
    {
        return $this->grace_ends_at ? $this->grace_ends_at->gte(now()) : false;
    }

    /**
     * Check if subscription period has ended.
     *
     * @return bool
     */
    public function ended(): bool
    {
        return $this->ends_at ? Carbon::now()->gte($this->ends_at) : false;
    }

    /**
     * Cancel subscription
     *
     * @return $this
     */
    public function cancel()
    {
        $this->canceled_at = Carbon::now();
        $this->save();
        event(new SubscriptionCancelled($this));

        return $this;
    }

    /**
     * Cancel subscription without raising event
     *
     * @return $this
     */
    public function cancelWithoutEvent()
    {
        $this->canceled_at = Carbon::now();
        $this->save();

        return $this;
    }

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
     * Check if subscription has supplements of a feature
     *
     * @param string $featureName
     * @return bool
     */
    public function hasSupplementsOfFeature($featureName): bool
    {
        return $this->supplements()->whereHas('feature', function (Builder $builder) use ($featureName) {
            $builder->where('name', $featureName);
        })->exists();
    }

    /**
     * Total usage of feature in the subscription
     *
     * @param Feature $feature
     * @return float
     */
    public function totalUsageOfFeature(Feature $feature)
    {
        return (float) $this->usages()->where('feature_id', $feature->id)->sum('used');
    }

    /**
     * Total supplement of feature in the subscription
     *
     * @param Feature $feature
     * @return float
     */
    public function totalSupplementOfFeature(Feature $feature)
    {
        return (float) $this->supplements()->where('feature_id', $feature->id)->sum('value');
    }

    /**
     * Remaining usage of a feature in the subscription
     *
     * @param Feature $feature
     * @return float
     */
    public function remainingUsageOfFeature(Feature $feature)
    {
        return (float) $feature->valueInPlan($this->plan) + $this->totalSupplementOfFeature($feature) - $this->totalUsageOfFeature($feature);
    }

    /**
     * Scope subscriptions with ending periods.
     *
     * @param Builder $builder
     * @param int $dayRange
     *
     * @return Builder
     */
    public function scopeEndingPeriod(Builder $builder, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $builder->whereBetween('ends_at', [$from, $to]);
    }

    /**
     * Scope ended subscriptions.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeEnded(Builder $builder): Builder
    {
        return $builder->where('ends_at', '<=', now());
    }

    /**
     * Check if subscription has enough usage remaining on a feature
     *
     * @param Feature $feature
     * @param float $uses
     * @return bool
     */
    public function remainsEnoughUsageForFeature(Feature $feature, float $uses): bool
    {
        return $this->remainingUsageOfFeature($feature) >= $uses;
    }

    /**
     * Check if feature has validity
     *
     * @param Feature $feature
     * @return bool
     */
    public function featureHasValidity(Feature $feature): bool
    {
        $featureResettablePeriod = $this->resettablePeriodOfFeature($feature);
        $featureResettableInterval = $this->resettableIntervalOfFeature($feature);
        $method = 'add'.ucfirst($this->$featureResettableInterval).'s';

        return ($featureResettablePeriod && $featureResettableInterval
            && ($this->starts_at->{$method}($featureResettablePeriod)->gte(now())));
    }

    /**
     * Resettable period in a subscription for the feature
     *
     * @param Feature $feature
     * @return ?int
     */
    public function resettablePeriodOfFeature(Feature $feature): ?int
    {
        return $feature->resettablePeriodInPlan($this->plan);
    }

    /**
     * Resettable period in a subscription for the feature
     *
     * @param Feature $feature
     * @return ?string
     */
    public function resettableIntervalOfFeature(Feature $feature): ?string
    {
        return $feature->resettableIntervalInPlan($this->plan);
    }

    /**
     * Add feature usage.
     *
     * @param Feature $feature
     * @param float $uses
     *
     * @return Subscription
     */
    public function incrementFeatureUsage(Feature $feature, float $uses = 1)
    {
        $uses = abs($uses);

        if (!$this->featureHasValidity($feature))
            throw new LogicException(__('We can\'t perform the action due to feature usage expiration.'));

        if (!$this->remainsEnoughUsageForFeature($feature, $uses))
            throw new LogicException(__('We can\'t perform the action due to feature usage limitation.'));

        $this->usages()->create([
            'feature_id' => $feature->id,
            'used' => $uses
        ]);

        return $this;
    }

    /**
     * Remove feature usage.
     *
     * @param Feature $feature
     * @param float $uses
     *
     * @return Subscription
     */
    public function decrementFeatureUsage(Feature $feature, float $uses = 1)
    {
        $uses = abs($uses);

        $this->usages()->create([
            'feature_id' => $feature->id,
            'used' => -$uses
        ]);

        return $this;
    }

    /**
     * Renew subscription.
     *
     * @param ?string $timezone
     * @param ?callable $action Action to perform in the same transaction after the subscription is renewed
     * @param int $tries Number of attempts on failure
     * @return self
     */
    public function renew(string $timezone = null, callable $action = null, int $tries = 2)
    {
        if (($this->ended() && !$this->isOnGrace()) || $this->canceled())
            throw new LogicException(__('Unable to renew canceled or ended subscription.'));

        return DB::transaction(function () use ($timezone, $action) {
            $newSubscription = $this->replicate(['stars_at', 'ends_at', 'canceled_at']);

            $newSubscription->price = $this->plan->price;
            $newSubscription->timezone = $timezone;

            $newSubscription->push();

            if ($action) $action($newSubscription);
            event(new SubscriptionRenewed($newSubscription));

            return $newSubscription;
        }, $tries);
    }

    /**
     * Change subscription plan.
     *
     * @param Plan $plan
     * @param string|null $timezone
     * @param ?callable $action
     * @param int $tries
     * @return self
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function changePlan(Plan $plan, ?string $timezone = null, ?callable $action = null, int $tries = 2)
    {
        return DB::transaction(function () use ($action, $timezone, $plan) {
            $this->cancelWithoutEvent();

            $newSubscription = $this->subscriber->subscriptions()->create([
                'plan_id' => $plan->id,
                'price' => $plan->price,
                'timezone' => $timezone
            ]);

            if ($action) $action($this, $newSubscription);
            event(new SubscriptionPlanChanged($this, $newSubscription));

            return $newSubscription;
        }, $tries);
    }
}