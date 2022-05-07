<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;
use App\Models\DeviceChange;
use \Carbon\Carbon;

class HistoryService 
{
    const FILTER_DATE = 'STATISTICS-TABLE-DATE';
    const FILTER_SQL = 'STATISTICS-TABLE-SQL';
    
    /**
     * 
     * @param Request $request
     */
    public function storeFilterDataFromRequest(Request $request)
    {
        if ($request->method() == 'POST') {
            //Session::put(self::FILTER_DATE, $request->post('date'));
            //Session::put(self::FILTER_SQL, $request->post('sql'));
        }
    }
    
    /**
     * 
     * @param int $deviceID
     * @return type
     */
    public function getFilteringData(int $deviceID = null)
    {
        $date = isset($_COOKIE[self::FILTER_DATE]) ? $_COOKIE[self::FILTER_DATE] : '';
        $sql = isset($_COOKIE[self::FILTER_SQL]) ? $_COOKIE[self::FILTER_SQL] : '';
        $errors = [];
        $data = [];
        
        if ($date) {
            $query = DeviceChange::whereDeviceId($deviceID);

            $d = Carbon::parse($date)->startOfDay();
            $query->whereBetween('change_date', [$d, $d->copy()->addDay()]);

            if ($sql) {
                $query->whereRaw('value '.$sql);
            }

            try {
                $data = $query->orderBy('id', 'asc')->get();
            } catch (\Exception $ex) {
                $errors['sql'] = $ex->getMessage();
                $data = [];
            }
        }
        
        return [$data, $errors];
    }
    
    /**
     * 
     * @param int $deviceID
     * @return int
     * @throws \Exception
     */
    public function deleteAllVisibleValues(int $deviceID)
    {
        try {
            $date = isset($_COOKIE[self::FILTER_DATE]) ? $_COOKIE[self::FILTER_DATE] : '';
            $sql = isset($_COOKIE[self::FILTER_SQL]) ? $_COOKIE[self::FILTER_SQL] : '';
            
            if (!$date) {
                throw new \Exception('Field date is required');
            }
            
            $d = Carbon::parse($date)->startOfDay();
            $query = DeviceChange::whereDeviceId($deviceID)
                        ->whereBetween('change_date', [$d, $d->copy()->addDay()]);
            if ($sql) {
                $query->whereRaw('value '.$sql);
            }
            
            return $query->delete();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
        
        return 0;
    }
}
