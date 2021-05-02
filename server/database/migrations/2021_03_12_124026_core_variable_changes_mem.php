<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreVariableChangesMem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_variable_changes_mem', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('variable_id');
            $table->timestamp('change_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->float('value');
            $table->integer('from_id')->nullable();
        });
        
        DB::statement('ALTER TABLE core_variable_changes_mem ENGINE = MEMORY');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_variable_changes_mem');
    }
}
