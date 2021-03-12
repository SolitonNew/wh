<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreExecute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_execute', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('command', 4000);
        });
        
        DB::statement('ALTER TABLE core_execute ENGINE = MEMORY');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_execute');
    }
}
