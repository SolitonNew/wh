<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WebUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_users', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('login')->unique();
            $table->string('email')->nullable()->default('');
            $table->string('password');
            $table->integer('access')->default(0);
            $table->string('api_token', 60)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_users');
    }
}
