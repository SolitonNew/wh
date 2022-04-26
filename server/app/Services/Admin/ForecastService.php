<?php

namespace App\Services\Admin;

use App\Models\Property;
use App\Models\Device;
use App\Models\SoftHost;

class ForecastService 
{
    public function getData()
    {
        $settings = Property::getForecastSettings();
        
        $fields = [
            'TEMP' => $settings->TEMP,
            'P' => $settings->P,
            'CC' => $settings->CC,
            'G' => $settings->G,
            'H' => $settings->H,
            'V' => $settings->V,
            'WD' => $settings->WD,
            'WS' => $settings->WS,
            'MP' => $settings->MP,
        ];
        
        $ids = [];
        foreach ($fields as $key => $val) {
            if ($val) {
                $ids[] = $val;
            }
        }
        
        $devices = Device::whereIn('id', $ids)
            ->whereTyp('software')
            ->get();
        
        $hostIds = [];
        foreach ($devices as $dev) {
            if (!in_array($dev->host_id, $hostIds)) {
                $hostIds[] = $dev->host_id;
            }
        }
        
        $hosts = SoftHost::whereIn('id', $hostIds)
            ->get();
        
        $storages = [];
        foreach($hosts as $host) {
            $storages[$host->id] = $host->driver()->getLastForecastData();
        }
        
        $result = [];
        $recTime = \Carbon\Carbon::now('UTC')->startOfHour();
        for ($i = 0; $i < 120; $i++) {
            $time = $recTime->timestamp;
            
            $values = [
                'localtime' => parse_datetime($recTime),
            ];
            
            foreach ($fields as $key => $val) {
                $values[$key] = '-//-';
            } 
            
            foreach ($devices as $dev) {
                $storage = $storages[$dev->host_id];
                if (isset($storage[$time])) {
                    $values[$dev->channel] = $storage[$time]->{$dev->channel};
                } else {
                    $tp = $recTime->copy()->addHours(-1)->timestamp;
                    if (isset($storage[$tp])) {
                        $values[$dev->channel] = $storage[$tp]->{$dev->channel};
                    } else {
                        $tn = $recTime->copy()->addHours(1)->timestamp;
                        if (isset($storage[$tn])) {
                            $values[$dev->channel] = $storage[$tn]->{$dev->channel};
                        } else {
                            $values[$dev->channel] = '-//-';
                        }
                    }
                }
            }
            $result[] = (object)$values;
            $recTime->addHours(1);
        }
        
        return $result;
    }
}
