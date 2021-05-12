<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreDeviceChangesMem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_device_changes_mem', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('device_id');
            $table->timestamp('change_date')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->float('value');
            $table->integer('from_id')->nullable();
        });
        
        DB::statement('ALTER TABLE core_device_changes_mem ENGINE = MEMORY');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_device_changes_mem');
    }
}