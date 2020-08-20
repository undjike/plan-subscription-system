<?php

/*
 * PlanSubscriptionServiceProvider.php
 *
 *  @author    Ulrich Pascal Ndjike Zoa <ndjikezoaulrich@gmail.com>
 *  @project    plan-subscription-system
 *
 *  Copyright 2020
 *  18/08/2020 15:21
 */

namespace Undjike\PlanSubscriptionSystem;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Undjike\PlanSubscriptionSystem\Models\Feature;
use Undjike\PlanSubscriptionSystem\Models\Plan;
use Undjike\PlanSubscriptionSystem\Models\Subscription;
use Undjike\PlanSubscriptionSystem\Models\Supplement;
use Undjike\PlanSubscriptionSystem\Models\Usage;

class PlanSubscriptionServiceProvider extends ServiceProvider
{
    public function boot(Filesystem $filesystem)
    {
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__.'/../config/plan-subscription.php' => config_path('plan-subscription.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations/create_subscription_system_tables.php.stub' => $this->getMigrationFileName($filesystem),
            ], 'migrations');
        }

        // Bind eloquent models to IoC container
        $this->app->singleton('plan-subscription.plan', $planModel = $this->app['config']['plan-subscription.models.plan']);
        $planModel === Plan::class || $this->app->alias('plan-subscription.plan', Plan::class);

        $this->app->singleton('plan-subscription.feature', $featureModel = $this->app['config']['plan-subscription.models.feature']);
        $featureModel === Feature::class || $this->app->alias('plan-subscription.feature', Feature::class);

        $this->app->singleton('plan-subscription.subscription', $subscriptionModel = $this->app['config']['plan-subscription.models.subscription']);
        $subscriptionModel === Subscription::class || $this->app->alias('plan-subscription.subscription', Subscription::class);

        $this->app->singleton('plan-subscription.usage', $usageModel = $this->app['config']['plan-subscription.models.usage']);
        $usageModel === Usage::class || $this->app->alias('plan-subscription.usage', Usage::class);

        $this->app->singleton('plan-subscription.supplement', $supplementModel = $this->app['config']['plan-subscription.models.supplement']);
        $supplementModel === Supplement::class || $this->app->alias('plan-subscription.supplement', Supplement::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/plan-subscription.php',
            'plan-subscription'
        );
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem): string
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path.'*_create_subscription_system_tables.php');
            })->push($this->app->databasePath()."/migrations/{$timestamp}_create_subscription_system_tables.php")
            ->first();
    }
}