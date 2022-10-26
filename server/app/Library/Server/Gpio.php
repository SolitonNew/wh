<?php

namespace App\Library\Server;

class Gpio 
{
    public const APP = '/bin/gpioset';
    
    private int $index = 0;
    
    private array $channels = [];
    
    public function __construct(int $index, array $channels) 
    {
        if (!file_exists(self::APP)) {
            throw new \Exception('GPIO DISABLED');
        }
        
        $this->index = $index;        
        $this->channels = $channels;
    }
    
    /**
     * @return void
     */
    public function init(array $initValues): void
    {
        foreach ($initValues as $channel => $value) {
            $this->set($channel, $value);
        }
        
        foreach ($this->channels as $channel => $pin) {
            if (!isset($initValues[$channel])) {
                $this->set($channel, 0);
            }
        }
    }
    
    /**
     * @param string $channel
     * @return bool
     */
    public function isPinChannel(string $channel): bool
    {
        return (isset($this->channels[$channel]) && ($this->channels[$channel] > -1));
    }
            
    
    /**
     * @param string $channel
     * @return bool
     */
    public function get(string $channel): bool
    {
        return false;
    }
    
    /**
     * @param string $channel
     * @param float $value
     * @return void
     * @throws \Exception
     */
    public function set(string $channel, float $value): void
    {
        if (!$this->isPinChannel($channel)) return ;
        $pin = $this->channels[$channel];
        
        if ($value) {
            $res = shell_exec(self::APP.' '.$this->index.' '.$pin.'=1 2>&1');
        } else {
            $res = shell_exec(self::APP.' '.$this->index.' '.$pin.'=0 2>&1');
        }
        
        if ($res) {
            throw new \Exception($res);
        }
    }
}
