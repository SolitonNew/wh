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
            $table->bigInteger('device_id')->unsigned()->nullale();
            $table->timestamp('change_date')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->float('value')->nullable();
            $table->bigInteger('from_id')->nullable();
            $table->string('typ', 30);
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
