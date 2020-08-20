<?php

/*
 * NewSubscription.php
 *
 *  @author    Ulrich Pascal Ndjike Zoa <ndjikezoaulrich@gmail.com>
 *  @project    plan-subscription-system
 *
 *  Copyright 2020
 *  20/08/2020 09:54
 */

namespace Undjike\PlanSubscriptionSystem\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Undjike\PlanSubscriptionSystem\Models\Subscription;

class NewSubscription
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * Create a new event instance.
     *
     * @param Subscription $subscription
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }
}