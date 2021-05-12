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
            $table->integerIncrements('id');
            $table->integer('device_id');
            $table->timestamp('change_date')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->float('value');
            $table->integer('from_id')->nullable();
            
            $table->index('device_id');
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
