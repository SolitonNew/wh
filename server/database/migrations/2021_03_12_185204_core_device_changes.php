<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreDeviceChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_device_changes', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('device_id')->unsigned();
            $table->timestamp('change_date')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->float('value');
            $table->integer('from_id')->nullable();
            
            $table->foreign('device_id', 'core_device_changes_fk_devices')->references('id')->on('core_devices')->cascadeOnDelete();
            $table->index('change_date');
            $table->index('from_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_device_changes');
    }
}
