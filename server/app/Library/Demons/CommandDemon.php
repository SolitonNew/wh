<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Demons;

use \Carbon\Carbon;
use DB;
use Lang;
use Log;

/**
 * Description of CommandDemon
 *
 * @author soliton
 */
class CommandDemon extends BaseDemon {
    
    use CommandFunctions\FunctionGet,
        CommandFunctions\FunctionSet,
        CommandFunctions\FunctionOn,
        CommandFunctions\FunctionOff,
        CommandFunctions\FunctionToggle,
        CommandFunctions\FunctionPlay,
        CommandFunctions\FunctionSpeech,
        CommandFunctions\FunctionInfo;
    
    /**
     * Зарезервированные короткие команды.
     * В тексте скрипта команды будут заменены на аналогичные присоединенные 
     * методы спрефиксом $this->function_[command].
     * 
     * @var type 
     */
    protected $_functions = [
        'get', 
        'set',
        'on',
        'off',
        'toggle',
        'speech',
        'play',
        'info',
    ];
    
    /**
     * 
     */
    public function execute() {        
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        DB::delete('delete from core_execute');
        
        $lastProcessedID = -1;

        $this->printLine(str_repeat('-', 100));
        $this->printLine(Lang::get('admin/demons.command-demon-title'));
        $this->printLine(str_repeat('-', 100));
        
        while(1) {
            $sql = "select *
                      from core_execute 
                     where ID > $lastProcessedID
                    order by ID";

            foreach(DB::select($sql) as $row) {
                $this->printLine(Lang::get('admin/demons.command-demon-line', [
                    'datetime' => Carbon::now(),
                    'command' => $row->COMMAND,
                ]));
                $this->_execute($row->COMMAND);
                $lastProcessedID = $row->ID;
            }
            
            usleep(100000);
        }
    }
    
    /**
     * 
     * @param string $command
     */
    private function _execute(string $command) {        
        // Готовим команду для использования внутри нашего класса
        $parser = new \App\Library\ScriptParser($command, $this->_functions);
        $command = $parser->convertToPhp('$this->function_');
        // ---------------------

        try {
            eval($command);
        } catch (\ParseError $ex) {
            $this->printLine($ex->getMessage());
        } catch (\Throwable $ex) {
            $this->printLine($ex->getMessage());
        }
    }
}
