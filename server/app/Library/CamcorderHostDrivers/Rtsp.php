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
        return $this->_getRecordingPID() > 0;
    }
    
    /**
     * 
     * @param int $key
     * @return string
     */
    public function startRecording(int $key)
    {
        $result = 'START RECORDING';
        
        $url = $this->getDataValue('url');
        if (!$url) {
            return $result.': Bad source path.';
        }
        
        $folder = base_path('storage/app/camcorder/videos/'.$key);
        if (file_exists($folder)) {
            return $result.': Bad out folder.';
        }
        
        mkdir($folder);
        exec('ffmpeg -i "'.$url.'&camcorder" -vf fps=1 -to 15 '.$folder.'/%03d.jpg >/dev/null &');
        
        return $result;
    }
    
    /**
     * 
     * @return string
     */
    public function stopRecording()
    {
        $result = 'STOP RECORDING';
        
        $pid = $this->_getRecordingPID();
        if ($pid) {
            exec('kill -9 '.$pid);
        }
        
        return $result;
    }
    
    /**
     * 
     * @return boolean|int
     */
    private function _getRecordingPID()
    {
        $url = $this->getDataValue('url');
        if (!$url) return false;
        
        $id = 'ffmpeg -i';
        
        $out = shell_exec("ps axww | grep '$id' | grep -v grep");
        
        foreach (explode("\n", $out) as $line) {
            if (strpos($out, $url.'&camcorder') !== false) {
                $a = explode(' ', $line);
                return $a[0];
            }
        }
        
        return 0;
    }
}
