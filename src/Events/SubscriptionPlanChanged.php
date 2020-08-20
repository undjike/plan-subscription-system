<?php

/*
 * SubscriptionPlanChanged.php
 *
 *  @author    Ulrich Pascal Ndjike Zoa <ndjikezoaulrich@gmail.com>
 *  @project    plan-subscription-system
 *
 *  Copyright 2020
 *  20/08/2020 09:51
 */

namespace Undjike\PlanSubscriptionSystem\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Undjike\PlanSubscriptionSystem\Models\Subscription;

class SubscriptionPlanChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Subscription
     */
    private $oldSubscription;

    /**
     * @var Subscription
     */
    private $newSubscription;

    /**
     * Create a new event instance.
     *
     * @param Subscription $oldSubscription
     * @param Subscription $newSubscription
     */
    public function __construct(Subscription $oldSubscription, Subscription $newSubscription)
    {
        $this->oldSubscription = $oldSubscription;
        $this->newSubscription = $newSubscription;
    }
}