<?php

namespace App\Console\Commands;


trait PrintToDB {
    
    /**
     * 
     * @param type $time
     * @param type $text
     */
    public function printLine($text) {
        $item = new \App\Http\Models\WebLogsModel();
        $item->DEMON = $this->signature;
        $item->DATA = $text;
        $item->save();
        echo "$text\n";
    }
    
}