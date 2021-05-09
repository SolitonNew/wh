<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MediaSpeech extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_speeches', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('phrase', 250);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            $table->unique('phrase');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_speeches');
    }
}
