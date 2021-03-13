<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreVariableEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_variable_events', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('event_type');
            $table->integer('variable_id');
            $table->integer('script_id');
            
            $table->index('variable_id');
            $table->index('script_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_variable_events');
    }
}
