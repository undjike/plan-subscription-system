<?php

/*
 * Usage.php
 *
 *  @author    Ulrich Pascal Ndjike Zoa <ndjikezoaulrich@gmail.com>
 *  @project    plan-subscription-system
 *
 *  Copyright 2020
 *  18/08/2020 20:18
 */

namespace Undjike\PlanSubscriptionSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Undjike\PlanSubscriptionSystem\Traits\BelongsToFeature;
use Undjike\PlanSubscriptionSystem\Traits\BelongsToSubscription;

/**
 * @property float $used
 */
class Usage extends Model
{
    use BelongsToSubscription, BelongsToFeature;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['used'];
}