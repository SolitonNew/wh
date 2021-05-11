<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use App\Models\SpeechesModel;
use App\Models\WebQueueMemModel;

/**
 * Description of Speech
 *
 * @author soliton
 */
class Speech 
{
    public function turn($phrase)
    {
        $item = SpeechesModel::wherePhrase($phrase)->first();
        if (!$item) { // If doesn't exist create sample and save to db
            // Save to db
            $item = new SpeechesModel();
            $item->phrase = $phrase;
            $item->save();
            
            // Render phrase to the file
            $path = storage_path('app/speech');
            if (!file_exists($path)) mkdir($path);
            $file = $path.'/speech_'.$item->id.'.wav';
            exec('echo "'.$phrase.'" | RHVoice-test -p Anna -o '.$file);
        }
        
        // Append an record to the queue
        WebQueueMemModel::appendRecord('speech', json_encode([
            'id' => $item->id, 
            'phrase' => $phrase,
        ]));
    }
}
