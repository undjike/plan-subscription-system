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