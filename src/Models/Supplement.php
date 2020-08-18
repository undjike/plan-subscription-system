<?php

/*
 * Supplement.php
 *
 *  @author    Ulrich Pascal Ndjike Zoa <ndjikezoaulrich@gmail.com>
 *  @project    plan-subscription-system
 *
 *  Copyright 2020
 *  18/08/2020 20:50
 */

namespace Undjike\PlanSubscriptionSystem\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property float $price
 * @property float $value
 */
class Supplement extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['price', 'value'];
}