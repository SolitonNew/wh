<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreVariableChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_variable_changes', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('variable_id');
            $table->timestamp('change_date')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->float('value');
            $table->integer('from_id')->nullable();
            
            $table->index('variable_id');
            $table->index('change_date');
            $table->index('from_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_variable_changes');
    }
}
