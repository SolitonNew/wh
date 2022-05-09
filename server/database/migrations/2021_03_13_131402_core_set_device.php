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
    IN DEV_ID int,
    IN DEV_VALUE float
)
BEGIN
    insert into core_device_changes
       (DEVICE_ID, VALUE)
    values
       (DEV_ID, DEV_VALUE);
      
    update core_devices
       set VALUE = DEV_VALUE
     where ID = DEV_ID;
     
    insert into core_events_mem
       (TYP, DEVICE_CHANGES_ID, DEVICE_ID, VALUE)
    values
       ('DEVICE_CHANGE_VALUE', LAST_INSERT_ID(), DEV_ID, DEV_VALUE);
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
