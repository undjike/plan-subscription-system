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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Undjike\PlanSubscriptionSystem\Traits\BelongsToFeature;
use Undjike\PlanSubscriptionSystem\Traits\BelongsToSubscription;

/**
 * @property int $id
 * @property float $used
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $subscription_id
 * @property int $feature_id
 * @property-read Feature $feature
 * @property-read Subscription $subscription
 * @method static Builder|Usage newModelQuery()
 * @method static Builder|Usage newQuery()
 * @method static Builder|Usage query()
 * @method static Builder|Usage whereCreatedAt($value)
 * @method static Builder|Usage whereFeatureId($value)
 * @method static Builder|Usage whereId($value)
 * @method static Builder|Usage whereSubscriptionId($value)
 * @method static Builder|Usage whereUpdatedAt($value)
 * @method static Builder|Usage whereUsed($value)
 * @method static self firstWhere(string $string, string $value)
 * @method static self create(array $array)
 */
class Usage extends Model
{
    use BelongsToSubscription, BelongsToFeature;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['used', 'subscription_id', 'feature_id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['subscription_id', 'feature_id'];
}