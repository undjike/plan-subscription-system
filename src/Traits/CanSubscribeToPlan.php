<?php

/*
 * CanSubscribeToPlan.php
 *
 *  @author    Ulrich Pascal Ndjike Zoa <ndjikezoaulrich@gmail.com>
 *  @project    plan-subscription-system
 *
 *  Copyright 2020
 *  19/08/2020 23:45
 */

namespace Undjike\PlanSubscriptionSystem\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use LogicException;
use Undjike\PlanSubscriptionSystem\Events\NewSubscription;
use Undjike\PlanSubscriptionSystem\Models\Plan;
use Undjike\PlanSubscriptionSystem\Models\Subscription;

/**
 * Trait CanSubscribeToPlan
 * @package Undjike\PlanSubscriptionSystem\Traits
 * @mixin Model
 */
trait CanSubscribeToPlan
{
    /**
     * All subscriber's subscriptions.
     *
     * @return MorphMany
     */
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'subscriber');
    }

    /**
     * Last subscription.
     *
     * @return Model|MorphMany|Subscription|null
     */
    public function lastSubscription()
    {
        return $this->subscriptions()->latest()->first();
    }

    /**
     * Subscribe to a new plan.
     *
     * @param Plan $plan
     *
     * @param ?string $timezone
     * @param callable|null $action
     * @param int $tries
     * @return Subscription
     */
    public function subscribe(Plan $plan, ?string $timezone = null, ?callable $action = null, int $tries = 2): Model
    {
        if ($this->lastSubscription()->isActive())
            throw new LogicException(__('Unable to perform the action because you have an active subscription.'));

        return DB::transaction(function () use ($timezone, $plan, $action) {
            $newSubscription = $this->subscriptions()->create([
                'plan_id' => $plan->id,
                'price' => $plan->price,
                'timezone' => $timezone
            ]);

            if ($action) $action($newSubscription);
            if ($newSubscription instanceof Subscription) event(new NewSubscription($newSubscription));

            return $newSubscription;
        }, $tries);
    }
}