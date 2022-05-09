<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreEventsMem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_events_mem', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('typ', 30);
            $table->bigInteger('device_id')->unsigned()->nullable();
            $table->float('value')->nullable();
            $table->string('data', 255)->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
        
        DB::statement('ALTER TABLE core_events_mem ENGINE = MEMORY');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_events_mem');
    }
}
