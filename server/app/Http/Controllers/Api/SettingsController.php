<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\SettingsService;
use Illuminate\Http\Request;
use App\Models\Property;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    private $_service;
    
    public function __construct(SettingsService $service) 
    {
        $this->_service = $service;
    }
    
    /**
     * 
     * @return type
     */
    public function getFavoritesDeviceList()
    {
        return response()->json([
            'devices' => $this->_service->getAllDevices(),
            'checkeds' => explode(',', Property::getWebChecks()),
        ]);
    }
    
    /**
     * 
     * @param int $deviceID
     * @return string
     */
    public function addDeviceToFavorites(int $deviceID)
    {
        try {
            $this->_service->addDeviceToFavorites($deviceID);
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * 
     * @param int $deviceID
     * @return string
     */
    public function delDeviceFromFavorites(int $deviceID)
    {
        try {
            $this->_service->delDeviceFromFavorites($deviceID);
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * 
     * @return type
     */
    public function getAppControlList()
    {
        return response()->json(config('devices.app_controls'));
    }
    
    /**
     * 
     * @return type
     */
    public function getFavoritesOrderList()
    {
        $data = $this->_service->getOrderList();
        
        return response()->json($data);
    }
    
    /**
     * 
     * @param Request $request
     * @return string
     */
    public function setFavoritesOrders(Request $request)
    {
        try {
            Property::setWebChecks($request->ids);
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * 
     * @return type
     */
    public function getDeviceColors()
    {
        return response()->json(Property::getWebColors());
    }
    
    /**
     * 
     * @param Request $request
     * @param int $index
     * @return string
     */
    public function setDeviceColor(Request $request, int $index)
    {
        try {
            $colors = Property::getWebColors();
            if (!isset($colors[$index])) {
                $colors[] = (object)[
                    'keyword' => $request->keyword,
                    'color' => $request->color,
                ];
            } else {
                $colors[$index] = (object)[
                    'keyword' => $request->keyword,
                    'color' => $request->color,
                ];
            }
            Property::setWebColors($colors);
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * 
     * @param int $index
     * @return string
     */
    public function delDeviceColor(int $index)
    {
        try {
            $colors = Property::getWebColors();
            if (isset($colors[$index])) {
                unset($colors[$index]);
                Property::setWebColors(array_values($colors));
            }
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
}
