<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WebQueueMem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_queue_mem', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('action', 20);
            $table->string('data', 250);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
        
        DB::statement('ALTER TABLE web_queue_mem ENGINE = MEMORY');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_queue_mem');
    }
}
