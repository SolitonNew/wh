<?php

return [
    /*
     * The time of the chart update (milliseconds)
     */
    'chart_update_interval' => 60 * 1000,
    
    /*
     * The interval of the query update data (milliseconds)
     */
    'admin_log_update_interval' => 500,
    
    /*
     * Number of lines in the system log
     */
    'admin_log_lines_count' => 40,    
    
    /*
     * ID of the device for info message
     */
    'command_info_temp_id' => env('COMMAND_INFO_TEMP_ID', -1),
    
    /*
     * Number of lines in journal logs
     */
    'admin_daemons_log_lines_count' => 100,
    
    /**
     * Number interval of the query update daemon status (milliseconds)
     */
    'admin_daemins_status_update_interval' => 5 * 1000,

];