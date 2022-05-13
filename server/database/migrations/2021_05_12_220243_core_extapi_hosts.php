<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreExtapiHosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_extapi_hosts', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('hub_id')->unsigned();
            $table->string('typ', 20);
            $table->string('name');
            $table->string('comm', 1000)->nullable();
            $table->text('data')->nullable();
            
            $table->index('hub_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_extapi_hosts');
    }
}
