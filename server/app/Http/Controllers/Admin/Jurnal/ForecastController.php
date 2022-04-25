<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Admin\Jurnal;

use App\Http\Controllers\Controller;
use App\Services\Admin\ForecastService;

/**
 * Description of ForecastController
 *
 * @author User
 */
class ForecastController extends Controller
{
    private $_service;
    
    public function __construct(ForecastService $service) 
    {
        $this->_service = $service;
    }
    
    /**
     * 
     * @return type
     */
    public function index()
    {
        return view('admin.jurnal.forecast.forecast', [
            'data' => $this->_service->getData(),
        ]);
    }
}
