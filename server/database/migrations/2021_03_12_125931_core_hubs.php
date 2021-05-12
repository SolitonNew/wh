<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreHubs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_hubs', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('name');
            $table->string('typ', 20)->default('virtual'); // virtual, din, onewire
            $table->integer('rom')->nullable();
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
        Schema::dropIfExists('core_hubs');
    }
}
