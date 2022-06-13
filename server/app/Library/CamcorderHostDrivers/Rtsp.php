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
    
    public function requestThumbnail() 
    {
        $url = $this->getDataValue('url');
        
        if ($url) {
            try {
                shell_exec('ffmpeg -i "'.$url.'" -frames:v 1 /var/www/server/storage/app/camcorder/thumbnails/'.$this->key.'.jpg -y');
            } catch (\Exception $ex) {
                $this->printLine($ex->getMessage());
            }
        }
    }
}
