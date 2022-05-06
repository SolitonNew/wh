<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_i2c_hosts', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('hub_id')->unsigned()->nullable();
            $table->string('name');
            $table->string('comm', 1000)->nullable();
            $table->string('typ')->nullable();
            $table->integer('address')->nullable();
            $table->integer('lost')->default(0);
            
            $table->foreign('hub_id')->references('id')->on('core_hubs')->onDelete('cascade');
            $table->index('address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_i2c_hosts');
    }
};
