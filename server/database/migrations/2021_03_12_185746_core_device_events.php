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
            $table->integerIncrements('id');
            $table->integer('event_type');
            $table->integer('device_id');
            $table->integer('script_id');
            
            $table->index('device_id');
            $table->index('script_id');
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
