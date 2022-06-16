<?php

namespace App\Library\CamcorderHostDrivers;

class Rtsp extends CamcorderDriverBase
{
    public $name = 'rtsp';
    public $channels = [
        'REC', // Recording channel
    ];
    public $properties = [
        'url' => 'large',
    ];
    
    protected $thumbnailCronExpression = '*/10 * * * *';
    
    /**
     * 
     * @return type
     */
    public function requestThumbnail() 
    {
        $url = $this->getDataValue('url');
        $thumbnailPath = base_path('storage/app/camcorder/thumbnails/'.$this->key.'.jpg');
        
        $error = '';
        if ($url) {
            try {
                shell_exec('ffmpeg -i "'.$url.'" -frames:v 1 '.$thumbnailPath.' -y');
            } catch (\Exception $ex) {
                $error = $ex->getMessage();
            }
        }
        return $error;
    }
    
    /**
     * 
     * @return boolean
     */
    public function checkRecording()
    {
        return false;
    }
    
    /**
     * 
     * @return string
     */
    public function startRecording()
    {
        $result = 'START RECORDING';
        
        return $result;
    }
    
    /**
     * 
     * @return string
     */
    public function stopRecording()
    {
        $result = 'STOP RECORDING';
        
        return $result;
    }
}
