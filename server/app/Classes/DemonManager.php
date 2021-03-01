<?php

namespace App\Classes;

use Log;

/**
 * Менеджер процессов DemonManager
 *
 * @author soliton
 */
class DemonManager {
    
    /**
     *     
     * @var type 
     */
    protected $_demons = [];
    
    /**
     * 
     */
    public function __construct() {
        $this->_demons = config('demons.list');
    }
    
    public function demons() {
        return $this->_demons;
    }
    
    /**
     * Проверяет корректность ИД сверя со списком зарегистрированных
     * 
     * @param type $id
     * @return type
     */
    public function exists(string $id) {
        return in_array($id, $this->_demons);
    }
    
    /**
     * Проверяет запущен ли процесс в системе
     * 
     * @param type $id
     */
    public function isStarted(string $id) {
        if ($this->exists($id)) {
            return count($this->findDemonPID($id)) > 0;
        } else {
            throw new \Exception('Несуществуюущий ID процесса');
        }
    }
    
    /**
     * Запускает процесс
     * 
     * @param type $id
     */
    public function start(string $id) {
        if ($this->exists($id)) {
            exec('php '.base_path().'/artisan '.$id.'>/dev/null &');
        } else {
            throw new \Exception('Несуществуюущий ID процесса');
        }
    }
    
    /**
     * Останавливает процесс
     * 
     * @param type $id
     */
    public function stop(string $id) {
        if ($this->exists($id)) {
            foreach($this->findDemonPID($id) as $pid) {
                exec('kill -9 '.$pid);
            }
        } else {
            throw new \Exception('Несуществуюущий ID процесса');
        }
    }
    
    /**
     * Перезапускает процесс. Если процесс был остановлен - запускает его.
     * 
     * @param type $id
     */
    public function restart(string $id) {
        if ($this->exists($id)) {
            if ($this->isStarted($id)) {
                $this->stop($id);
            }
            $this->start($id);
        } else {
            throw new \Exception('Несуществуюущий ID процесса');
        }
    }
    
    /**
     * Выполняет запрос к ОС и вовзращает результат поиска демонов в виде массива
     * 
     * @param string $id
     * @return type
     */
    public function findDemonPID(string $id) {
        $pids = [];
        exec("ps ax | grep $id | grep -v grep | grep -v 'sh -c '", $outs);
        foreach($outs as $out) {
            $a = explode(' ', trim($out));
            if (count($a)) {
                $pids[] = $a[0];
            }
        }
        return $pids;
    }
    
}
