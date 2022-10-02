<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\FavoritesService;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    /**
     * @var FavoritesService
     */
    private FavoritesService $service;

    public function __construct(FavoritesService $service)
    {
        $this->service = $service;
    }

    /**
     *
     * @return type
     */
    public function getData(Request $request)
    {
        $data = $this->service->getData($request->getHttpHost(), $request->api_token);
        return response()->json($data);
    }
}
