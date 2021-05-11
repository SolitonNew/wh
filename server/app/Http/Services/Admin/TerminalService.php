<?php

namespace App\Http\Services\Admin;

use App\Models\Room;
use App\Models\Property;

class TerminalService 
{
    /**
     * 
     * @return string
     */
    public function levels()
    {
        $levels = [
            1 => '',
            2 => '',
            3 => '',
        ];
        
        $parts = Room::generateTree();
        
        for ($i = 0; $i < 3; $i++) {
            for ($k = count($parts) - 1; $k >= 0; $k--) {
                if ($parts[$k]->level === $i) {
                    $levels[$i + 1] = (isset($levels[$i]) ? $levels[$i].' - ' : '').$parts[$k]->name;
                    break;
                }
            }
        }
        
        return $levels;
    }
    
    /**
     * 
     * @return type
     */
    public function getCurrentLevel()
    {
        return Property::getPlanMaxLevel();
    }
    
    /**
     * 
     * @param type $level
     */
    public function setCurrentLevel($level)
    {
        try {
            Property::setPlanMaxLevel($level);
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
