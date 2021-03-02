<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Demons\Commands;

use Lang;

/**
 * Description of InfoCommand
 *
 * @author soliton
 */
class Info extends CommandBase {
    
    /**
     * 
     * @param string $command
     * @param string $output
     * @return boolean
     */
    public function execute(string $command, &$output) {
        if (strpos(strtoupper($command), 'INFO()') !== false) {
            $output = '';

            // Формируем строку текущего времени  ------------------------------
            $h = now()->hour;
            $m = now()->minute;
            $minute_speech = '';
            if ($m > 0) {
                $minute = [];
                if ($m < 10) {
                    $minute[] = Lang::get('admin/demons.command-demon-minutes-2.'.$m).' '.Lang::get('admin/demons.command-demon-minutes.'.$m);
                } elseif ($m < 20) {
                    $minute[] = Lang::get('admin/demons.command-demon-minutes.9');
                } else {
                    $n = $m.'';
                    
                    $minute[] = Lang::get('admin/demons.command-demon-minutes-1.'.$n[0]);
                    $minute[] = Lang::get('admin/demons.command-demon-minutes-2.'.$n[1]);
                    $minute[] = Lang::get('admin/demons.command-demon-minutes.'.$n[1]);
                }
                
                $minute_speech = ', '.implode(' ', $minute);
            }
            
            $text = Lang::get('admin/demons.command-demon-hours.'.$h).$minute_speech;
            \App\Http\Models\ExecuteModel::command("speech('$text')");
            
            // Формируем строку текущей температуры на улице  ------------------
            $temp_item = \App\Http\Models\VariablesModel::find(config('app.command_info_temp_id'));
            if ($temp_item) {
                $t_out = $temp_item->VALUE;
                $t_round = round($t_out);
                $t_speach = abs($t_round);
                $t_str = ' '.$t_speach;

                $text_arr = [];
                $text_arr[] = Lang::get('admin/demons.command-demon-info-temp', [
                    'temp' => $t_speach,
                ]);

                $text_arr[] = Lang::get('admin/demons.command-demon-temps.'.$t_str[strlen($t_str) - 1]);

                if ($t_round < 0) {
                    $text_arr[] = Lang::get('admin/demons.command-demon-info-temp-znak.0');
                } elseif ($t_round > 0) {
                    $text_arr[] = Lang::get('admin/demons.command-demon-info-temp-znak.1');
                }

                $text = implode(' ', $text_arr);
                \App\Http\Models\ExecuteModel::command("speech('$text')");
            }

            return true;
        }
         
        return false;
    }
    
}
