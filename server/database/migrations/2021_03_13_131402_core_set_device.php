<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreSetDevice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS `CORE_SET_DEVICE`');
        
        DB::unprepared("
CREATE PROCEDURE `CORE_SET_DEVICE` (
    IN VAR_ID int,
    IN VAR_VALUE float,
    IN DEV_ID int
)
BEGIN
    insert into core_device_changes
      (device_id, VALUE, FROM_ID)
    values
      (VAR_ID, VAR_VALUE, DEV_ID);

    update core_devices
       set VALUE = VAR_VALUE
     where ID = VAR_ID;
     
    insert into core_events_mem
      (ID, DEVICE_ID, VALUE, FROM_ID, TYP)
    values
      (LAST_INSERT_ID(), VAR_ID, VAR_VALUE, DEV_ID, 'DEVICE_CHANGE_VALUE');
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
        DB::unprepared('DROP PROCEDURE IF EXISTS `CORE_SET_DEVICE`');
    }
}
