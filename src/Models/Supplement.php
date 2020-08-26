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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Undjike\PlanSubscriptionSystem\Traits\BelongsToFeature;
use Undjike\PlanSubscriptionSystem\Traits\BelongsToSubscription;

/**
 * @property int $id
 * @property float $price
 * @property int $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $subscription_id
 * @property int $feature_id
 * @property-read Feature $feature
 * @property-read Subscription $subscription
 * @method static Builder|Supplement newModelQuery()
 * @method static Builder|Supplement newQuery()
 * @method static Builder|Supplement query()
 * @method static Builder|Supplement whereCreatedAt($value)
 * @method static Builder|Supplement whereFeatureId($value)
 * @method static Builder|Supplement whereId($value)
 * @method static Builder|Supplement wherePrice($value)
 * @method static Builder|Supplement whereSubscriptionId($value)
 * @method static Builder|Supplement whereUpdatedAt($value)
 * @method static Builder|Supplement whereValue($value)
 * @method static self firstWhere(string $string, string $value)
 * @method static self create(array $array)
 */
class Supplement extends Model
{
    use BelongsToSubscription, BelongsToFeature;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['price', 'value', 'subscription_id', 'feature_id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['subscription_id', 'feature_id'];
}