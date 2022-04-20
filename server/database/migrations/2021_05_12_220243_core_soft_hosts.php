<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreSoftHosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_soft_hosts', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('hub_id')->unsigned();
            $table->string('typ', 20);
            $table->string('name');
            $table->string('comm', 1000)->nullable();
            $table->text('data')->nullable();
            
            $table->foreign('hub_id')->references('id')->on('core_hubs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_soft_hosts');
    }
}
