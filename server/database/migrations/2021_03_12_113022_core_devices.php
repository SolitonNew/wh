<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreDevices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_devices', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('hub_id');
            $table->string('typ', 20);
            $table->string('name');
            $table->string('comm')->nullable();
            $table->float('value')->default(0);
            $table->timestamp('last_update')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->integer('ow_id')->nullable();
            $table->string('channel', 20)->default('');
            $table->integer('app_control')->default(0);
            $table->integer('room_id')->nullable();
            $table->string('position', 255)->nullable();
            
            $table->index('hub_id');
            $table->index('ow_id');
            $table->index('room_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_devices');
    }
}