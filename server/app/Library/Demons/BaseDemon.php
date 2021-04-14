<?php

namespace App\Library\Demons;

use Log;

/**
 * Базовый класс для всех демонов системы
 *
 * @author soliton
 */
class BaseDemon {
    
    /**
     * Сигнатура (ИД) демона
     * @var type 
     */
    protected $_signature = '';
    
    public function __construct($signature) 
    {
        $this->_signature = $signature;
    }

    /**
     * Метод делает запись лога демонов в БД.
     * 
     * @param type $text
     */
    public function printLine($text) 
    {
        try {
            $item = new \App\Http\Models\WebLogsModel();
            $item->demon = $this->_signature;
            $item->data = $text;
            $item->save();
            
            echo "$text\n";
        } catch (\Exception $ex) {
            echo $ex->getMessage()."\n";
        }
    }
    
    /**
     * Метод вызывает автоматически при запуске.
     * Каждый наследник этого класса должен переопределить его и разместить 
     * внутри код который должен выполнять демон.
     */
    public function execute() 
    {
        
    }
    
}
