<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function test()
    {
        //dd(\App\Models\SoftHostStorage::get());
        
        $file = file_get_contents(storage_path('logs').'/log.txt');
        $data = json_decode($file);
        
        $start = \Carbon\Carbon::now()->startOfHour();
        $end = $start->clone()->addDays(7);
        
        $table = [];
        foreach ($data->hours as $hour) {
            $time = \Carbon\Carbon::parse($hour->time);
            $time->timezone = 'Europe/Kiev';
            
            if ($time->gte($start) && $time->lte($end)) {
                $table[] = (object)[
                    'time' => $time->format('H:i:s d-m-Y'),
                    'airTemperature' => $hour->airTemperature->sg,
                    'cloudCover' => $hour->cloudCover->sg,
                    'gust' => $hour->gust->sg,
                    'humidity' => $hour->humidity->sg,
                    'pressure' => $hour->pressure->sg,
                    'visibility' => $hour->visibility->sg,
                    'windDirection' => $hour->windDirection->sg,
                    'windSpeed' => $hour->windSpeed->sg,
                ];
            }
        }
        
        $html = '<style> body {font-family: Arial} td {padding: 0.5rem;font-size:13px;} </style>';
        
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<td>time</td>';
        $html .= '<td>airTemperature</td>';
        $html .= '<td>cloudCover</td>';
        $html .= '<td>gust</td>';
        $html .= '<td>humidity</td>';
        $html .= '<td>pressure</td>';
        $html .= '<td>visibility</td>';
        $html .= '<td>windDirection</td>';
        $html .= '<td>windSpeed</td>';
        $html .= '</tr>';
        
        foreach ($table as $row) {
            $html .= '<tr>';
            $html .= '<td>'.$row->time.'</td>';
            $html .= '<td>'.$row->airTemperature.'</td>';
            $html .= '<td>'.$row->cloudCover.'</td>';
            $html .= '<td>'.$row->gust.'</td>';
            $html .= '<td>'.$row->humidity.'</td>';
            $html .= '<td>'.round($row->pressure / 1.333).'</td>';
            $html .= '<td>'.$row->visibility.'</td>';
            $html .= '<td>'.$row->windDirection.'</td>';
            $html .= '<td>'.$row->windSpeed.'</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        return $html;
    }
}
