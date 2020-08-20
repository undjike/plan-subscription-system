<?php

return [
    'models' => [
        /*
         * Eloquent model to be used to retrieve your plans. Of course, it
         * is often just the "Plan" model but you may use whatever you like.
         */
        'plan' => \Undjike\PlanSubscriptionSystem\Models\Plan::class,

        /*
         * Eloquent model to be used to retrieve your features. Of course, it
         * is often just the "Feature" model but you may use whatever you like.
         */
        'feature' => \Undjike\PlanSubscriptionSystem\Models\Feature::class,

        /*
         * Eloquent model to be used to retrieve your subscriptions. Of course, it
         * is often just the "Subscription" model but you may use whatever you like.
         */
        'subscription' => \Undjike\PlanSubscriptionSystem\Models\Subscription::class,

        /*
         * Eloquent model to be used to retrieve your usages. Of course, it
         * is often just the "Usage" model but you may use whatever you like.
         */
        'usage' => \Undjike\PlanSubscriptionSystem\Models\Usage::class,

        /*
         * Eloquent model to be used to retrieve your supplements. Of course, it
         * is often just the "Supplement" model but you may use whatever you like.
         */
        'supplement' => \Undjike\PlanSubscriptionSystem\Models\Supplement::class
    ]
];
