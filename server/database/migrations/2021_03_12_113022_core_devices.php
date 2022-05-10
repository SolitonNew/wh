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
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('hub_id')->unsigned()->nullable();
            $table->string('typ', 20);
            $table->string('name');
            $table->string('comm')->nullable();
            $table->float('value')->default(0);
            $table->timestamp('last_update')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->bigInteger('host_id')->unsigned()->nullable();
            $table->string('channel', 20)->default('');
            $table->integer('app_control')->default(0);
            $table->bigInteger('room_id')->unsigned()->nullable();
            $table->string('position', 255)->nullable();
            
            $table->foreign('hub_id')->references('id')->on('core_hubs')->onDelete('cascade');
            $table->index('host_id');
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
