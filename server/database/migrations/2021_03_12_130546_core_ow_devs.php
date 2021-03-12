<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreOwDevs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_ow_devs', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('controller_id');
            $table->string('name');
            $table->string('comm', 1000)->nullable();
            $table->integer('rom_1');
            $table->integer('rom_2');
            $table->integer('rom_3');
            $table->integer('rom_4');
            $table->integer('rom_5');
            $table->integer('rom_6');
            $table->integer('rom_7');
            $table->integer('rom_8');
            $table->string('position', 1000)->nullable();
            
            $table->index('controller_id');
            $table->index('rom_1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_ow_devs');
    }
}
