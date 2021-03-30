<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreSetVariable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
CREATE PROCEDURE `CORE_SET_VARIABLE` (
    IN VAR_ID int,
    IN VAR_VALUE float,
    IN DEV_ID int
)
BEGIN
    insert into core_variable_changes
      (VARIABLE_ID, VALUE, FROM_ID)
    values
      (VAR_ID, VAR_VALUE, DEV_ID);

    insert into core_variable_changes_mem
      (ID, VARIABLE_ID, VALUE, FROM_ID)
    values
      (LAST_INSERT_ID(), VAR_ID, VAR_VALUE, DEV_ID);

    update core_variables
       set VALUE = VAR_VALUE
     where ID = VAR_ID;
END
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS `CORE_SET_VARIABLE`');
        Log::info('DROP!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
    }
}
