<?php

namespace App\Services\Admin;

use App\Models\Script;
use App\Models\Schedule;
use App\Library\Script\PhpExecute;
use App\Models\Property;

class Autotest 
{
    /**
     * @return array
     */
    public function runForAllScripts(): array
    {
        $errors = [];
        foreach (Script::get() as $script) {
            try {
                $execute = new PhpExecute($script->data);
                $report = [];
                $execute->run(true, $report);
                if (isset($report['error'])) {
                    $errors[$script->id] = $report['error'];
                }                
            } catch (\Exception $ex) {
                $errors[$script->id] = $ex->getMessage();
            }
        }
        
        // Store errors count
        Property::setScriptAutotestFailure(count($errors));
        // ------------------
        
        return $errors;
    }
    
    /**
     * @return array
     */
    public function runForAllSchedules(): array
    {
        $errors = [];
        foreach (Schedule::get() as $schedule) {
            try {
                $execute = new PhpExecute($schedule->action);
                $report = [];
                $execute->run(true, $report);
                if (isset($report['error'])) {
                    $errors[$schedule->id] = $report['error'];
                }                
            } catch (\Exception $ex) {
                $errors[$schedule->id] = $ex->getMessage();
            }
        }
        
        // Store errors count
        Property::setScheduleAutotestFailure(count($errors));
        // ------------------
        
        return $errors;
    }
}
