<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreDeviceEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_device_events', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('event_type');
            $table->bigInteger('device_id')->unsigned()->nullable();
            $table->bigInteger('script_id')->unsigned()->nullable();
            
            $table->foreign('device_id')->references('id')->on('core_devices')->cascadeOnDelete();
            $table->foreign('script_id')->references('id')->on('core_scripts')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_device_events');
    }
}
