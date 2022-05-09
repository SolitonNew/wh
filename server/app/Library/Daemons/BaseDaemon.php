<?php

namespace App\Library\Daemons;

use App\Models\WebLogMem;
use App\Models\Property;
use App\Models\Hub;
use App\Models\Device;
use App\Models\EventMem;
use Illuminate\Support\Facades\DB;
use App\Library\Script\PhpExecute;

/**
 * This is the base class for all daemons.
 *
 * @author soliton
 */
class BaseDaemon
{    
    /**
     * Signature (id) of the daemon
     * @var type 
     */
    protected $_signature = '';
    
    public function __construct($signature) 
    {
        $this->_signature = $signature;
    }
    
    /**
     *
     * @var type 
     */
    protected $_hubs = [];
    
    /**
     *
     * @var type 
     */
    protected $_hubIds = [];
    
    /**
     *
     * @var type 
     */
    protected $_devices = [];
    
    /**
     *
     * @var type 
     */
    private $_lastEventID = -1;
    
    /**
     *
     * @var type 
     */
    private $_daemonHubTyp = false;
    
    /**
     * 
     * @param type $typ
     * @return type
     */
    protected function initialization($typ = '')
    {
        $this->_daemonHubTyp = $typ;
        
        if (!$this->initializationHubs()) return false;
        $this->initializationHosts();
        $this->initializationDevices();
        
        $this->_lastEventID = EventMem::max('id') ?? -1;
        
        return true;
    }
    
    /**
     * 
     * @return boolean
     */
    protected function initializationHubs()
    {
        if ($this->_daemonHubTyp === '') return true;
        
        $this->_hubs = Hub::where('id', '>', 0)
            ->whereTyp($this->_daemonHubTyp)
            ->orderBy('rom', 'asc')
            ->get();
        
        if (count($this->_hubs) == 0) {
            $this->printLine("[".parse_datetime(now())."] WARNING! Hubs not found. The demon stopped.");
            $this->disableAutorun();
            return false;
        }
        
        $this->_hubIds = $this->_hubs
            ->pluck('id')
            ->toArray();
        
        return true;
    }
    
    protected function initializationHosts()
    {
        //
    }
    
    /**
     * 
     */
    protected function initializationDevices()
    {
        $this->_devices = Device::orderBy('id')
            ->get();
    }
    
    /**
     * The launch of this method is automated.
     * Each inheritor of this class must override it and place it inside 
     * the code that the daemon should execute.
     */
    public function execute() 
    {
        while (1) {
            if (!$this->checkEvents()) break;
            
            usleep(200000);
        }
    }
    
    /**
     * Must be called in the main daemon loop
     * 
     * @param type $withScripts
     * @return boolean
     */
    protected function checkEvents($withScripts = true)
    {
        $changes = EventMem::where('id', '>', $this->_lastEventID)
                    ->orderBy('id', 'asc')
                    ->get();
        
        foreach ($changes as $change) {
            $this->_lastEventID = $change->id;
            if ($change->typ == EventMem::DEVICE_CHANGE_VALUE) {
                foreach ($this->_devices as $device) {
                    if ($device->id == $change->device_id) {
                        if ($device->value != $change->value) {
                            // Store new device value
                            $device->value = $change->value;
                            
                            // Call change value handler
                            $this->deviceChangeValue($device);
                            
                            // Run event script if it's attached
                            if ($withScripts) {
                                $this->executeEvents($device);
                            }
                        }
                        break;
                    }
                }
            } else {
                switch ($change->typ) {
                    case EventMem::HUB_LIST_CHANGE:
                        if (!$this->initializationHubs()) return false;
                        break;
                    case EventMem::HOST_LIST_CHANGE:
                        $this->initializationHosts();
                        break;
                    case EventMem::DEVICE_LIST_CHANGE:
                        $this->initializationDevices();
                        break;
                }
            }            
        }
        
        return true;
    }
    
    /**
     * 
     * @param type $device
     * @return type
     */
    protected function executeEvents(&$device)
    {
        if (!in_array($device->hub_id, $this->_hubIds)) return;
        
        $sql = "select s.comm, s.data
                  from core_device_events de, core_scripts s
                 where de.device_id = ".$device->id."
                   and de.script_id = s.id";
        
        foreach (DB::select($sql) as $script) {
            try {
                $execute = new PhpExecute($script->data);
                $execute->run();
                $s = "[".parse_datetime(now())."] RUN SCRIPT '".$script->comm."' \n";
                $this->printLine($s); 
            } catch (\Exception $ex) {
                $s = "[".parse_datetime(now())."] ERROR\n";
                $s .= $ex->getMessage();
                $this->printLine($s); 
            }
        }
    }
    
    /**
     * Can be overloaded to track device value changes.
     * 
     * @param type $device
     */
    protected function deviceChangeValue(&$device)
    {
        // For inheriting
    }

    /**
     * This method of adding a log entry into DB.
     * 
     * @param type $text
     */
    public function printLine($text) 
    {
        try {
            $item = new WebLogMem();
            $item->daemon = $this->_signature;
            $item->data = $text;
            $item->save();
            
            echo "$text\n";
        } catch (\Exception $ex) {
            echo $ex->getMessage()."\n";
        }
    }
    
    /**
     * 
     * @param type $text
     */
    public function printLineToLast($text)
    {
        try {
            $item = WebLogMem::whereDaemon($this->_signature)
                ->orderBy('id', 'desc')
                ->first();
            if ($item) {
                $item->data = $text;
                $item->save();
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage()."\n";
        }
    }
    
    /**
     * 
     * @param type $percent
     */
    public function printProgress(int $percent = 0)
    {
        if ($percent == 0) {
            $this->printLine('PROGRESS:0');
        } else {
            $this->printLineToLast('PROGRESS:'.$percent);
        }
    }
    
    /**
     * Disabling autorun of this daemon
     */
    public function disableAutorun()
    {
        Property::setAsStoppedDaemon($this->_signature);
    }
}
