<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use App\Models\SpeecheCache;
use App\Models\WebQueueMem;

/**
 * Description of Speech
 *
 * @author soliton
 */
class Speech 
{
    public function turn($phrase)
    {
        $item = SpeecheCache::wherePhrase($phrase)->first();
        if (!$item) { // If doesn't exist create sample and save to db
            // Save to db
            $item = new SpeecheCache();
            $item->phrase = $phrase;
            $item->save();
            
            // Render phrase to the file
            $path = storage_path('app/speech');
            if (!file_exists($path)) mkdir($path);
            $file = $path.'/speech_'.$item->id.'.wav';
            exec('echo "'.$phrase.'" | RHVoice-test -p Anna -o '.$file);
        }
        
        // Append an record to the queue
        WebQueueMem::appendRecord('speech', json_encode([
            'id' => $item->id, 
            'phrase' => $phrase,
        ]));
    }
}
