<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->{$this->jsonable()}('description')->nullable();
            $table->unsignedFloat('price');
            $table->unsignedFloat('signup_fee')->default(0);
            $table->boolean('dedicated')->default(false);
            $table->unsignedSmallInteger('trial_period')->default(0);
            $table->string('trial_interval')->default('day');
            $table->unsignedSmallInteger('invoice_period')->default(1);
            $table->string('invoice_interval')->default('month');
            $table->unsignedSmallInteger('grace_period')->default(0);
            $table->string('grace_interval')->default('day');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('features', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->{$this->jsonable()}('description')->nullable();
            $table->unsignedFloat('price');
            $table->string('quantifier')->nullable();
            $table->timestamps();
        });

        Schema::create('plan_features', function (Blueprint $table) {
            $table->unsignedMediumInteger('value');
            $table->unsignedSmallInteger('resettable_period')->default(0);
            $table->string('resettable_interval')->default('month');

            $table->primary(['permission_id', 'feature_id']);

            $table->foreignId('plan_id')->constrained();
            $table->foreignId('feature_id')->constrained();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('subscriber');
            $table->unsignedFloat('price');
            $table->timestamp('stars_at');
            $table->timestamp('ends_at');
            $table->timestamp('canceled_at')->nullable();
            $table->string('timezone')->nullable();
            $table->timestamps();

            $table->foreignId('plan_id')->constrained();
        });

        Schema::create('usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('used');
            $table->timestamps();

            $table->foreignId('subscription_id')->constrained();
            $table->foreignId('feature_id')->constrained();
        });

        Schema::create('supplements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedFloat('price');
            $table->unsignedMediumInteger('value');
            $table->timestamps();

            $table->foreignId('subscription_id')->constrained();
            $table->foreignId('feature_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('supplements');
        Schema::drop('usages');
        Schema::drop('subscriptions');
        Schema::drop('plan_features');
        Schema::drop('features');
        Schema::drop('plans');
    }

    /**
     * Get jsonable column data type.
     *
     * @return string
     */
    protected function jsonable(): string
    {
        $driverName = DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
        $dbVersion = DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
        $isOldVersion = version_compare($dbVersion, '5.7.8', 'lt');

        return $driverName === 'mysql' && $isOldVersion ? 'text' : 'json';
    }
}
