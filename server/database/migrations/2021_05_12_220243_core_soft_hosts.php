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
            $table->integerIncrements('id');
            $table->integer('hub_id');
            $table->string('typ', 20);
            $table->string('name');
            $table->string('comm', 1000)->nullable();
            $table->string('token', 1000);
            $table->integer('lost')->default(0);
            
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
        Schema::dropIfExists('core_soft_hosts');
    }
}
