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
        DB::update('update core_schedule set action_datetime = null');

        $this->printLine('');
        $this->printLine('');
        $this->printLine(str_repeat('-', 100));
        $this->printLine(Lang::get('admin/demons.schedule-demon-title'));
        $this->printLine(str_repeat('-', 100));
        foreach(\App\Http\Models\ScheduleModel::orderBy('comm', 'asc')->get() as $row) {
            $row->action_datetime = $row->makeDateTime();
            $row->save();
            $time = '--//--';
            if ($row->action_datetime) {
                $time = Carbon::parse($row->action_datetime)->format('Y-m-d H:i:s');
            }
            $this->printLine("[$time] $row->comm       ".($row->enable ? '' : Lang::get('admin/demons.schedule-demon-disabled')));
        }
        $this->printLine(str_repeat('-', 100));
        $this->printLine('');

        while(1) {
            foreach(\App\Http\Models\ScheduleModel::orderBy('comm', 'asc')->get() as $row) {
                $next_time = null;
                if (!$row->action_datetime) {
                    $next_time = $row->makeDateTime();
                } elseif (Carbon::parse($row->action_datetime)->lte(now())) {
                    $next_time = $row->makeDateTime();
                    if ($row->enable) {
                        // Выполняем
                        \App\Http\Models\ExecuteModel::command($row->action);
                        $this->printLine(Lang::get('admin/demons.schedule-demon-line', [
                            'datetime' => Carbon::parse($row->action_datetime),
                            'comm' => $row->comm,
                            'action' => str_replace("\n", ' ', $row->action),
                        ]));
                    }

                    if ($row->interval_type == 4) { // Это одноразовая задача
                        $row->delete();
                        $next_time = null;
                    }
                }

                if ($next_time) {
                    $row->action_datetime = $next_time;
                    $row->save();
                }
            }
            sleep(1);
        }
    }
}