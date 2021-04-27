<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TerminalController extends Controller
{
    /**
     * Index route of the terminal settings module.
     * 
     * @return type
     */
    public function index()
    {
        $levels = [
            1 => '',
            2 => '',
            3 => '',
        ];
        
        $parts = \App\Http\Models\PlanPartsModel::generateTree();
        
        for ($i = 0; $i < 3; $i++) {
            for ($k = count($parts) - 1; $k >= 0; $k--) {
                if ($parts[$k]->level === $i) {
                    $levels[$i + 1] = (isset($levels[$i]) ? $levels[$i].' - ' : '').$parts[$k]->name;
                    break;
                }
            }
        }
        
        return view('admin.terminal.terminal', [
            'levels' => $levels,
            'maxLevel' => \App\Http\Models\PropertysModel::getPlanMaxLevel(),
        ]);
    }
    
    /**
     * This route is used to set the maximum value of the visible level of the 
     * plan_parts structure for the terminal module.
     * 
     * @param type $value
     * @return string
     */
    public function setMaxLevel($value) {
        try {
            \App\Http\Models\PropertysModel::setPlanMaxLevel($value);
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
}
