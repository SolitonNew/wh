<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PlanParts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_parts', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('name')->default('');
            $table->integer('parent_id')->nullable();
            $table->integer('order_num')->default(0);
            $table->string('bounds', 1000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_parts');
    }
}
