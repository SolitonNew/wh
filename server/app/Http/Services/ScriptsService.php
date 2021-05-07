<?php

namespace App\Http\Services;

use Session;

class ScriptsService 
{
    const LAST_VIEW_ID = 'SCRIPT_INDEX_ID';
    
    /**
     * 
     * @param int $id
     */
    public function storeLastViewID(int $id = null)
    {
        Session::put(self::LAST_VIEW_ID, $id);
    }
    
    /**
     * 
     * @return type
     */
    public function getIdForView()
    {
        $id = Session::get(self::LAST_VIEW_ID);
        if (!$id) {
            $item = \App\Http\Models\ScriptsModel::orderBy('comm', 'asc')->first();
            if ($item) {
                $id = $item->id;
            }
        }
        
        return $id;
    }
}
