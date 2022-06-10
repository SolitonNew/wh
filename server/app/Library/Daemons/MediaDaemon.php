<?php

namespace App\Library\Daemons;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Models\Videcam;
use \Cron\CronExpression;

/**
 * Description of MediaDaemon
 *
 * @author soliton
 */
class MediaDaemon extends BaseDaemon 
{    
    private $_prevExecutePostersTime = false;
    
    /**
     * The overridden method.
     */
    public function execute() 
    {        
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');

        $this->printLine('');
        $this->printLine('');
        $this->printLine(str_repeat('-', 100));
        $this->printLine(Lang::get('admin/daemons/media-daemon.description'));
        $this->printLine(str_repeat('-', 100));
        $this->printLine('');
        
        while(1) {
            $this->_makeVideocameraPosters();
            
            sleep(1);
        }
    }
    
    /**
     * 
     * @return type
     */
    private function _makeVideocameraPosters()
    {
        $now = floor(\Carbon\Carbon::now()->timestamp / 60);
        
        // Checking for execute after daemon restart.
        if ($this->_prevExecutePostersTime === false) {
            $this->_prevExecutePostersTime = $now;
            return ;
        }
        
        // Checking for execute at ever minutes.
        if ($now == $this->_prevExecutePostersTime) {
            return ;
        }
        
        // Storing the previous time value
        $this->_prevExecutePostersTime = $now;
        
        if (!CronExpression::factory('*/5 * * * *')->isDue()) return ;
        
        $cams = Videcam::orderBy('id')->get();
        foreach ($cams as $cam) {
            if ($cam->url) {
                try {
                    shell_exec('ffmpeg -i "'.$cam->url.'" -frames:v 1 /var/www/server/storage/app/cam_posters/'.$cam->id.'.jpg -y');
                } catch (\Exception $ex) {
                    $this->printLine($ex->getMessage());
                }
            }
        }
    }
}
