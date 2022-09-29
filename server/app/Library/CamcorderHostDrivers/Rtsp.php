<?php

namespace App\Library\CamcorderHostDrivers;

class Rtsp extends CamcorderDriverBase
{
    /**
     * @var string
     */
    public string $name = 'rtsp';

    /**
     * @var array|string[]
     */
    public array $channels = [
        'REC', // Recording channel
    ];

    /**
     * @var array|string[]
     */
    public array $properties = [
        'url' => 'large',
    ];

    /**
     * @var string
     */
    protected string $thumbnailCronExpression = '*/10 * * * *';

    /**
     * @return string
     */
    public function requestThumbnail(): string
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
     * @return bool
     */
    public function checkRecording(): bool
    {
        return $this->getRecordingPID() > 0;
    }

    /**
     * @param int $key
     * @return string
     */
    public function startRecording(int $key): string
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
     * @return string
     */
    public function stopRecording(): string
    {
        $result = 'STOP RECORDING';

        $pid = $this->getRecordingPID();
        if ($pid) {
            exec('kill -9 '.$pid);
        }

        return $result;
    }

    /**
     * @return int|bool
     */
    private function getRecordingPID(): int|bool
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
