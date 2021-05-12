<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PlanVidecams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_videcams', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('name')->default('');
            $table->string('url')->default('');
            $table->string('url_low')->default('');
            $table->string('url_high')->default('');
            $table->integer('order_num')->default(0);
            $table->integer('alert_var_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_videcams');
    }
}