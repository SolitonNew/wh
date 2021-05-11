<?php

namespace App\Http\Services\Terminal;

use App\Models\WebQueueMem;

class QueueService 
{
    /**
     * 
     * @param int $lastID
     * @return type
     */
    public function getData(int $lastID)
    {
        if ($lastID > 0) {
            return $this->getDataList($lastID);
        } else {
            return $this->getDataLastID();
        }
    }
    
    /**
     * 
     * @return type
     */
    public function getDataLastID()
    {
        return 'LAST_ID: '.WebQueueMem::lastQueueID();
    }
    
    /**
     * 
     * @return type
     */
    public function getDataList(int $lastID)
    {
        $data = WebQueueMem::getLastQueueList($lastID)->toArray();
        
        return response()->json($data);
    }
    
    /**
     * 
     * @param int $id
     * @return type
     */
    public function getSpeechSource(int $id)
    {
        $file = storage_path('app/speech').'/speech_'.$id.'.wav';
        if (!file_exists($file)) abort(404);
        
        return response()->file($file);
    }
}
