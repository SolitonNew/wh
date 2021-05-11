<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\PhpFunctions;

use App\Models\VariablesModel;
use Lang;

trait FunctionInfo 
{
    /**
     * 
     */
    public function function_info() 
    {
        // Формируем строку текущего времени  ------------------------------
        $h = now()->hour;
        $m = now()->minute;
        $minute_speech = '';
        if ($m > 0) {
            $minute = [];
            if ($m < 10) {
                $minute[] = Lang::get('admin/daemons/command-daemon.minutes-2.'.$m).' '.Lang::get('admin/daemons/command-daemon.minutes.'.$m);
            } elseif ($m < 20) {
                $minute[] = Lang::get('admin/daemons/command-daemon.minutes-2.'.$m);
                $minute[] = Lang::get('admin/daemons/command-daemon.minutes.9');
            } else {
                $n = $m.'';

                $minute[] = Lang::get('admin/daemons/command-daemon.minutes-1.'.$n[0]);
                $minute[] = Lang::get('admin/daemons/command-daemon.minutes-2.'.$n[1]);
                $minute[] = Lang::get('admin/daemons/command-daemon.minutes.'.$n[1]);
            }

            $minute_speech = ', '.implode(' ', $minute);
        }

        $text = Lang::get('admin/daemons/command-daemon.hours.'.$h).$minute_speech;
        $this->function_speech($text);

        // Формируем строку текущей температуры на улице  ------------------
        $temp_item = VariablesModel::find(config('app.command_info_temp_id'));
        if ($temp_item) {
            $t_out = $temp_item->value;
            $t_round = round($t_out);
            $t_speach = abs($t_round);
            $t_str = ' '.$t_speach;

            $text_arr = [];
            $text_arr[] = Lang::get('admin/daemons/command-daemon.info-temp', [
                'temp' => $t_speach,
            ]);

            $text_arr[] = Lang::get('admin/daemons/command-daemon.temps.'.$t_str[strlen($t_str) - 1]);

            if ($t_round < 0) {
                $text_arr[] = Lang::get('admin/daemons/command-daemon.info-temp-znak.0');
            } elseif ($t_round > 0) {
                $text_arr[] = Lang::get('admin/daemons/command-daemon.info-temp-znak.1');
            }

            $text = implode(' ', $text_arr);
            $this->function_speech($text);
        }
    }
}