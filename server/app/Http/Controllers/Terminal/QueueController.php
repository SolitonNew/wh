<?php

namespace App\Http\Controllers\Terminal;

use App\Http\Controllers\Controller;
use App\Services\Terminal\QueueService;

class QueueController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_queueService;
    
    /**
     * 
     * @param QueueService $queueService
     */
    public function __construct(QueueService $queueService) 
    {
        $this->_queueService = $queueService;
    }
    
    /**
     * 
     * @param int $lastID
     * @return type
     */
    public function changes(int $lastID) 
    {
        return $this->_queueService->getData($lastID);
    }
    
    public function speechSource(int $id)
    {
        return $this->_queueService->getSpeechSource($id);
    }
}
