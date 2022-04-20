<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreSoftHostStorages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_soft_host_storages', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('soft_host_id')->unsigned();
            $table->longText('data')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            $table->foreign('soft_host_id')->references('id')->on('core_soft_hosts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_soft_host_storages');
    }
}
