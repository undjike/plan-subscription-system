<?php

/*
 * BelongsToSubscription.php
 *
 *  @author    Ulrich Pascal Ndjike Zoa <ndjikezoaulrich@gmail.com>
 *  @project    plan-subscription-system
 *
 *  Copyright 2020
 *  19/08/2020 20:13
 */

namespace Undjike\PlanSubscriptionSystem\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Undjike\PlanSubscriptionSystem\Models\Subscription;

/**
 * Trait BelongsToSubscription
 * @package Undjike\PlanSubscriptionSystem\Traits
 * @mixin Model
 */
trait BelongsToSubscription
{
    /**
     * Subscription concerned
     *
     * @return BelongsTo
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}