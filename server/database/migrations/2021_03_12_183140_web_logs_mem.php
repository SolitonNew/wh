<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WebLogsMem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_logs_mem', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('daemon', 32);
            $table->string('data', 255);
            
            DB::statement('ALTER TABLE web_logs_mem ENGINE = MEMORY');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_logs_mem');
    }
}
