<?php

namespace App\Library;

use App\Models\SpeecheCache;
use App\Models\EventMem;
use Illuminate\Support\Facades\File;

/**
 * Description of Speech
 *
 * @author soliton
 */
class Speech
{
    /**
     * @param string $phrase
     * @return void
     */
    public function turn(string $phrase): void
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
        EventMem::addEvent(EventMem::WEB_SPEECH, [
            'mediaID' => $item->id,
            'phrase' => $phrase,
        ]);
    }

    /**
     * @param int $mediaID
     * @return string
     */
    public static function makeMediaFileName(int $mediaID): string
    {
        return storage_path('app/speech').'/speech_'.$mediaID.'.wav';
    }
}
