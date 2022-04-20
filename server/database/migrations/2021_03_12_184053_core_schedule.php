<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreSchedule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_schedule', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('comm');
            $table->string('action', 2000);
            $table->timestamp('action_datetime')->nullable();
            $table->string('interval_time_of_day');
            $table->string('interval_day_of_type')->nullable();
            $table->integer('interval_type')->nullable();
            $table->bigInteger('temp_device_id')->unsigned()->nullable();
            $table->integer('enable')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_schedule');
    }
}
