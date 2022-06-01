<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\FavoritesService;

class FavoritesController extends Controller
{
    private $_service;
    
    public function __construct(FavoritesService $service) 
    {
        $this->_service = $service;
    }
    
    /**
     * 
     * @return type
     */
    public function getData()
    {
        $data = $this->_service->getData();
        return response()->json($data);
    }
}
