<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreExtapiHostStorages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_extapi_host_storages', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('extapi_host_id')->unsigned();
            $table->longText('data')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            $table->index('extapi_host_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_extapi_host_storages');
    }
}
