<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreControllers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_controllers', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('name');
            $table->integer('is_server')->default(0);
            $table->string('comm', 1000)->nullable();
            $table->integer('status')->default(1);
            $table->string('position', 1000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_controllers');
    }
}