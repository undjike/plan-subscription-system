<?php

/*
 * HasFeature.php
 *
 *  @author    Ulrich Pascal Ndjike Zoa <ndjikezoaulrich@gmail.com>
 *  @project    plan-subscription-system
 *
 *  Copyright 2020
 *  19/08/2020 20:18
 */

namespace Undjike\PlanSubscriptionSystem\Traits;

trait HasFeature
{
    /**
     * Check if plan or subscription has all the specified features
     *
     * @param string|array $featureNames
     * @return bool
     */
    public function hasFeature($featureNames): bool
    {
        $query = $this->features();

        foreach ((array) $featureNames as $feature) {
            $query = $query->where('name', $feature);
        }

        return $query->exists();
    }

    /**
     * Check if plan or subscription has any of the specified features
     *
     * @param string|array|mixed $featureName
     * @return bool
     */
    public function hasAnyFeature(...$featureName): bool
    {
        return $this->features()->whereIn('name', (array) $featureName)->exists();
    }
}