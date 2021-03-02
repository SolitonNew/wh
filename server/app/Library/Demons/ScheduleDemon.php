<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Demons;

use \Carbon\Carbon;
use Lang;
use DB;

/**
 * Description of CommandDemon
 *
 * @author soliton
 */
class ScheduleDemon extends BaseDemon {
    /**
     * 
     */
    public function execute() {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        DB::update('update core_scheduler set ACTION_DATETIME = null');

        $this->printLine('');
        $this->printLine('');
        $this->printLine('');
        $this->printLine(Lang::get('admin/demons.schedule-demon-title'));
        foreach(\App\Http\Models\SchedulerModel::orderBy('COMM', 'asc')->get() as $row) {
            $row->ACTION_DATETIME = $row->makeDateTime();
            $row->save();
            $time = '--//--';
            if ($row->ACTION_DATETIME) {
                $time = Carbon::parse($row->ACTION_DATETIME)->format('Y-m-d H:i:s');
            }
            $this->printLine("[$time] $row->COMM       ".($row->ENABLE ? '' : Lang::get('admin/demons.schedule-demon-disabled')));
        }
        $this->printLine("---------------------------------");

        while(1) {
            foreach(\App\Http\Models\SchedulerModel::orderBy('COMM', 'asc')->get() as $row) {
                $next_time = null;
                if (!$row->ACTION_DATETIME) {
                    $next_time = $row->makeDateTime();
                } elseif (Carbon::parse($row->ACTION_DATETIME)->lte(now())) {
                    $next_time = $row->makeDateTime();
                    if ($row->ENABLE) {
                        // Выполняем
                        \App\Http\Models\ExecuteModel::command($row->ACTION);
                        $this->printLine(Lang::get('admin/demons.schedule-demon-line', [
                            'datetime' => Carbon::parse($row->ACTION_DATETIME),
                            'comm' => $row->COMM,
                            'action' => str_replace("\n", ' ', $row->ACTION),
                        ]));
                    }

                    if ($row->INTERVAL_TYPE == 4) { // Это одноразовая задача
                        $row->delete();
                        $next_time = null;
                    }
                }

                if ($next_time) {
                    $row->ACTION_DATETIME = $next_time;
                    $row->save();
                }
            }
            sleep(1);
        }
    }
}