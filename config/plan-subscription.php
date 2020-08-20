<?php

use Undjike\PlanSubscriptionSystem\Models\Feature;
use Undjike\PlanSubscriptionSystem\Models\Plan;
use Undjike\PlanSubscriptionSystem\Models\Subscription;
use Undjike\PlanSubscriptionSystem\Models\Supplement;
use Undjike\PlanSubscriptionSystem\Models\Usage;

return [
    'models' => [
        /*
         * Eloquent model to be used to retrieve your plans. Of course, it
         * is often just the "Plan" model but you may use whatever you like.
         */
        'plan' => Plan::class,

        /*
         * Eloquent model to be used to retrieve your features. Of course, it
         * is often just the "Feature" model but you may use whatever you like.
         */
        'feature' => Feature::class,

        /*
         * Eloquent model to be used to retrieve your subscriptions. Of course, it
         * is often just the "Subscription" model but you may use whatever you like.
         */
        'subscription' => Subscription::class,

        /*
         * Eloquent model to be used to retrieve your usages. Of course, it
         * is often just the "Usage" model but you may use whatever you like.
         */
        'usage' => Usage::class,

        /*
         * Eloquent model to be used to retrieve your supplements. Of course, it
         * is often just the "Supplement" model but you may use whatever you like.
         */
        'supplement' => Supplement::class
    ]
];
