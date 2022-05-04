<?php

namespace App\Library\Daemons;

use App\Models\Hub;
use App\Models\Property;
use App\Models\DeviceChangeMem;
use App\Models\OwHost;
use App\Models\Device;
use DB;
use Illuminate\Support\Facades\Lang;
use Log;

/**
 * Description of CommandDaemon
 *
 * @author soliton
 */
class DinDaemon extends BaseDaemon 
{
    /**
     *
     * @var type 
     */
    private $_port;
    
    /**
     *
     * @var type 
     */
    private $_waitCount = 0;
    
    /**
     *
     * @var type 
     */
    private $_inPackCount = 0;
    
    /**
     *
     * @var type 
     */
    private $_inBuffer = '';
    
    /**
     *
     * @var type 
     */
    private $_inVariables = [];
    
    /**
     *
     * @var type 
     */
    private $_inRooms = [];
    
    /**
     *
     * @var type 
     */
    private $_lastSyncDeviceChangesID = -1;
    
    /**
     *
     * @var type 
     */
    private $_firmwareHex = [];
    
    /**
     *
     * @var type 
     */
    private $_firmwareSpmPageSize = 0;
    
    /**
     * 
     */
    const SLEEP_TIME = 50;
    
    /**
     * 
     */
    public function execute() 
    {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        
        $settings = Property::getDinSettings();

        $this->printLine('');
        $this->printLine('');
        $this->printLine(str_repeat('-', 100));
        $this->printLine(Lang::get('admin/daemons/din-daemon.description'));
        $this->printLine('--    PORT: '.$settings->port); 
        $this->printLine('--    BAUD: '.config('din.'.$settings->mmcu.'.baud')); 
        $this->printLine(str_repeat('-', 100));
        $this->printLine('');
        
        // Init hubs  -------------
        if (!$this->initHubs('din')) return ;
        // ------------------------
        
        $this->_lastSyncDeviceChangesID = DeviceChangeMem::max('id') ?? -1;
        
        try {
            $settings = Property::getDinSettings();
            $port = $settings->port;
            $baud = config('din.'.$settings->mmcu.'.baud');
            exec("stty -F $port $baud cs8 cstopb -icrnl ignbrk -brkint -imaxbel -opost -onlcr -isig -icanon -iexten -echo -echoe -echok -echoctl -echoke noflsh -ixon -crtscts");
            $this->_port = fopen($port, 'r+b');
            stream_set_blocking($this->_port, false);
            while (!feof($this->_port)) {
                $loopErrors = false;
                $command = Property::getDinCommand(true);
                
                // Performing the initial preparation of the iteration
                // It is the same for all controllers.
                $variables = [];
                switch ($command) {
                    case 'RESET':
                        Property::setDinCommandInfo('', true);
                        break;
                    case 'OW SEARCH':
                        Property::setDinCommandInfo('', true);
                        break;
                    case 'FIRMWARE':
                        Property::setDinCommandInfo('', true);
                        $this->_firmwareHex = false;
                        break;
                    default:
                        $variables = DeviceChangeMem::where('id', '>', $this->_lastSyncDeviceChangesID)
                                        ->orderBy('id', 'asc')
                                        ->get();
                        if (count($variables)) {
                            $this->_lastSyncDeviceChangesID = $variables[count($variables) - 1]->id;
                        }
                }
                                
                foreach($this->_hubs as $controller) {
                    switch ($command) {
                        case 'RESET':
                            $this->_commandReset($controller);
                            break;
                        case 'OW SEARCH':
                            $this->_commandOwSearch($controller);
                            break;
                        case 'FIRMWARE':
                            if (!$this->_commandFirmware($controller)) {
                                $loopErrors = true;
                            }
                            break;
                        default:
                            $this->_syncVariables($controller, $variables);
                    }
                }
                
                switch ($command) {
                    case 'RESET':
                        // not records
                        break;
                    case 'OW SEARCH':
                        Property::setDinCommandInfo('END_OW_SCAN');
                        break;
                    case 'FIRMWARE':
                        if (!$loopErrors) {
                            Property::setDinCommandInfo('COMPLETE', true);
                            // Reset the firmware change counter
                            Property::setFirmwareChanges(0);
                        } else {
                            Property::setDinCommandInfo('ERROR', true);
                        }
                        $this->_firmwareHex = false;
                        break;
                    default:
                        
                }
                
                usleep(100000);
            }
        } catch (\Exception $ex) {
            $s = "[".parse_datetime(now())."] ERROR\n";
            $s .= $ex->getMessage();
            $this->printLine($s); 
        } finally {
            if ($this->_port) {
                fclose($this->_port);    
            }
        }
    }
    
    /**
     * Controller reboot processing command.
     * 
     * @param type $conrollerROM
     */
    private function _commandReset($controller) 
    {
        $this->_readPacks(250);
        $this->_transmitCMD($controller->rom, 1, 0);
        usleep(250000);
    }
    
    /**
     * One Wire scan processing command.
     * 
     * @param type $conrollerROM
     */
    private function _commandOwSearch($controller) 
    {
        $this->_readPacks(250);
        $this->_inRooms = [];
        $this->_transmitCMD($controller->rom, 7, 0);
        $this->_readPacks(500);
        
        // We got the data. You need to combine them with what is already in 
        // the system and issue a report on the operation.
        
        $new = 0;
        $lost = 0;
        
        $owOldList = OwHost::whereHubId($controller->id)->get();
        // Finding a lost entries
        foreach ($owOldList as $owOld) {
            $find = false;
            foreach ($this->_inRooms as $rom) {
                if ($owOld->rom_1 === $rom[0] &&
                    $owOld->rom_2 === $rom[1] &&
                    $owOld->rom_3 === $rom[2] &&
                    $owOld->rom_4 === $rom[3] &&
                    $owOld->rom_5 === $rom[4] &&
                    $owOld->rom_6 === $rom[5] &&
                    $owOld->rom_7 === $rom[6] &&
                    $owOld->rom_8 === $rom[7]) {
                    $find = true;
                    break;
                }
            }
            if (!$find) {
                $lost++;
                
                $owOld->lost = 1;
            } else {
                $owOld->lost = 0;
            }
            $owOld->save();
        }
        
        // Check found entries.
        foreach ($this->_inRooms as $rom) {
            $find = false;
            foreach ($owOldList as $owOld) {
                if ($owOld->rom_1 === $rom[0] &&
                    $owOld->rom_2 === $rom[1] &&
                    $owOld->rom_3 === $rom[2] &&
                    $owOld->rom_4 === $rom[3] &&
                    $owOld->rom_5 === $rom[4] &&
                    $owOld->rom_6 === $rom[5] &&
                    $owOld->rom_7 === $rom[6] &&
                    $owOld->rom_8 === $rom[7]) {
                    $find = true;
                    break;
                }
            }
            if (!$find) {
                $new++;
                // Add to the list immediately.
                $ow = new OwHost();
                $ow->hub_id = $controller->id;
                $ow->name = '';
                $ow->comm = '';
                $ow->rom_1 = $rom[0];
                $ow->rom_2 = $rom[1];
                $ow->rom_3 = $rom[2];
                $ow->rom_4 = $rom[3];
                $ow->rom_5 = $rom[4];
                $ow->rom_6 = $rom[5];
                $ow->rom_7 = $rom[6];
                $ow->rom_8 = $rom[7];
                $ow->save();
            }
        }
        
        $report = [];
        $s = "OW SEARCH. '$controller->name' [TOTAL: ".count($this->_inRooms).", NEW: ".$new.", LOST: ".$lost."] ";
        $this->printLine($s);
        $report[] = $s;
        $report[] = str_repeat('-', 35);
        
        foreach ($this->_inRooms as $rom) {
            $a = [];
            foreach($rom as $b) {
                $a[] = sprintf("x%'02X", $b);
            }
            $s = implode(' ', $a);
            $this->printLine($s);
            $report[] = $s;
        }
        
        $report[] = str_repeat('-', 35);
        $report[] = '';

        Property::setDinCommandInfo(implode("\n", $report));
    }
    
    /**
     * Firmware processing command.
     * 
     * @param type $controller
     */
    public function _commandFirmware($controller) 
    {
        // ------------------------------
        $this->printProgress();
        // ------------------------------
        if (!$this->_firmwareHex) {
            $firmware = new \App\Library\Firmware\Din();
            $this->_firmwareHex = $firmware->getHex();
            $this->_firmwareSpmPageSize = $firmware->spmPageSize();
        }
        
        $PAGE_STORE_PAUSE = 100000;
        
        $count = count($this->_hubs);
        $index = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($this->_hubs[$i]->id == $controller->id) {
                $index = $i;
                break;
            }
        }
        
        $this->_readPacks(250);
        $this->_transmitCMD($controller->rom, 1, 0);
        
        usleep(1000000);
        
        $this->_transmitCMD($controller->rom, 24, count($this->_firmwareHex));
        
        $hexPackStep = ceil($this->_firmwareSpmPageSize / 8);
        
        $dp = 100 / count($this->_firmwareHex);
        $packs = 0;
        $p = 0;
        foreach ($this->_firmwareHex as $hex) {
            $this->_transmitHEX($controller->rom, $hex);
            $packs++;
            if ($packs % $hexPackStep == 0) {
                $a = [
                    $controller->name,
                    round((($index * 100) + $p) / $count),
                ];
                Property::setDinCommandInfo(implode(';', $a), true);
                // ------------------------------
                $this->printProgress(round($p));
                // ------------------------------
                usleep($PAGE_STORE_PAUSE);
            }
            $p += $dp;
        }
        $a = [
            $controller->name,
            round((($index * 100) + $p) / $count),
        ];
        Property::setDinCommandInfo(implode(';', $a), true);
        
        usleep($PAGE_STORE_PAUSE);
        
        $this->_transmitCMD($controller->rom, 25, count($this->_firmwareHex));
        $this->_readPacks(250);
        
        return ($this->_inPackCount == count($this->_firmwareHex));
    }
    
    /**
     * Devices sync processing and initializing controllers.
     * 
     * @param type $conrollerROM
     */
    private function _syncVariables($controller, &$variables) 
    {
        $stat = 'OK';        
        $vars_out = [];
        $errorText = '';
        try {
            // Send command "prepare to receive"
            $this->_transmitCMD($controller->rom, 2, count($variables));

            // Send devace values
            foreach ($variables as $variable) {
                $this->_transmitVAR($controller->rom, $variable->device_id, $variable->value);
                $vars_out[] = "$variable->device_id: $variable->value";
            }

            // Send command "prepare to give your changes"
            $this->_transmitCMD($controller->rom, 3, 0);
            
            $this->_inVariables = [];
            // Waiting for a controller's response.
            switch ($this->_readPacks(150)) {
                case 5: // Controller request of the initialization data
                    $stat = 'INIT';
                    $vars_out = [];
                    $variablesInit = Device::orderBy('id', 'asc')->get();
                    $this->_transmitCMD($controller->rom, 6, count($variablesInit));
                    $counter = 0;
                    foreach ($variablesInit as $variable) {
                        $this->_transmitVAR($controller->rom, $variable->id, $variable->value);
                        $vars_out[] = "$variable->id: $variable->value";
                        if ($counter++ > 5) {
                            usleep(75000); // We slow down periodically. The controller on the other end is not powerful.
                            $counter = 0;
                        }
                    }
                    $this->_readPacks(250);        
                    break;
                case 26: // The controller asked for the firmware
                    $stat = 'FIRMWARE QUERY';
                    break;
                case 27: // Controller in boot (probably overloaded)
                    $stat = 'BOOTLOADER';
                    break;
                case -1:
                    $this->_inBuffer = '';
                    throw new \Exception('Controller did not respond');
                default:
                    foreach ($this->_inVariables as $variable) {
                        Device::setValue($variable->id, $variable->value);
                    }
            }            
        } catch (\Exception $ex) {
            $stat = 'ERROR';
            $errorText = $ex->getMessage();
        }
        
        $this->printLine("[".parse_datetime(now())."] SYNC. '$controller->name': $stat");
        if ($stat == 'OK') {
            foreach (array_chunk($vars_out, 15) as $key => $chunk) {
                if ($key == 0) {
                    $s = '   >>   ';
                } else {
                    $s = '        ';
                }
                $this->printLine($s.'['.implode(', ', $chunk).']');
            }
            if (!count($vars_out)) {
                $this->printLine('   >>   []');
            }
            
            $vars_in = [];
            foreach ($this->_inVariables as $variable) {
                $vars_in[] = "$variable->id: $variable->value";
            }
            
            $this->printLine("   <<   [".implode(', ', $vars_in)."]");
        } elseif ($stat == 'INIT') {
            foreach (array_chunk($vars_out, 15) ?? [] as $key => $chunk) {
                if ($key == 0) {
                    $s = '   >>   ';
                } else {
                    $s = '        ';
                }
                $this->printLine($s.'['.implode(', ', $chunk).']');
            }
            if (!count($vars_out)) {
                $this->printLine('   >>   []');
            }
        } elseif ($stat == 'ERROR') {
            $this->printLine($errorText);
        } elseif ($stat == 'IN BOOTLOADER') {
            //
        } elseif ($stat == 'FIRMWARE QUERY') {
            if ($this->_commandFirmware($controller)) {
                $this->printLine('FIRMWARE OK');
            } else {
                $this->printLine('FIRMWARE ERROR');
            }
        }
    }
    
    /**
     * Reading the queue of the incoming packet.
     * An individual timeout value for receiving data is set.
     * 
     * @param integer $utimeout  Allowable waiting time for new data
     * @return int    -1 - no data received. >= 0 - received something
     */
    private function _readPacks($utimeout = 250) 
    {
        $returnCmd = -1;
        $this->_waitCount = 0;
        while ($this->_waitCount < ($utimeout / self::SLEEP_TIME)) {            
            $c = fgetc($this->_port);
            if ($c !== false) {
                $this->_waitCount = 0;        
                $this->_inBuffer .= $c;
                $cmd = 0;
                if ($this->_processedInBuffer($cmd)) {        
                    $this->_waitCount = 0; // Resets the timeout counter
                    if ($cmd > 0) {
                        $returnCmd = $cmd;
                    } else {
                        $returnCmd = 0;
                    }
                    
                    if ($this->_inPackCount <= 0) break; // Не будем ждать таймаут. Мы начитали все что нужно было.
                }
            } else {
                usleep(self::SLEEP_TIME * 1000);
                $this->_waitCount++;
            }
        }
        return $returnCmd;
    }
    
    /**
     * The main handler for all incoming packets.
     * 
     * @param integer $returnCmd  the code of the last command processed in this 
     *                            iteration. If there were no commands, there 
     *                            will be 0.
     * @return boolean  true -    at least one packet was processed. false - no 
     *                            package was found.
     */
    private function _processedInBuffer(&$returnCmd) 
    {
        $returnCmd = 0;
        $result = false;
        
        start_loop:
        
        if (strlen($this->_inBuffer) < 8) return $result;
        
        $sign = unpack('a*', $this->_inBuffer[0].$this->_inBuffer[1].$this->_inBuffer[2])[1];
        $size = 0;
        switch ($sign) {
            case 'CMD':
                if (strlen($this->_inBuffer) < 8) return $result;
                $size = 8;
                $crc = 0;
                for ($i = 0; $i < $size; $i++) {
                    $crc = $this->_crc_table($crc ^ ord($this->_inBuffer[$i]));
                }
                if ($crc === 0) {
                    $this->_inPackCount = 0;
                    $controller = unpack('C', $this->_inBuffer[3])[1];
                    $cmd = unpack('C', $this->_inBuffer[4])[1];
                    $tag = unpack('s', $this->_inBuffer[5].$this->_inBuffer[6])[1];
                    if ($cmd === 4) { // This is a package that indicates how much to read after
                        $this->_inPackCount = $tag;
                    }
                    $returnCmd = $cmd;
                } else {
                    $size = 0;
                }
                break;
            case 'VAR':
                if (strlen($this->_inBuffer) < 9) return $result;
                $size = 9;
                $crc = 0;
                for ($i = 0; $i < $size; $i++) {
                    $crc = $this->_crc_table($crc ^ ord($this->_inBuffer[$i]));
                }
                if ($crc === 0) {
                    $returnCmd = 0;
                    $controller = unpack('C', $this->_inBuffer[3])[1];
                    $id = unpack('s', $this->_inBuffer[4].$this->_inBuffer[5])[1];
                    $value = unpack('s', $this->_inBuffer[6].$this->_inBuffer[7])[1];
                    $value = $value / 10;
                    $this->_inVariables[] = (object)[
                        'id' => $id,
                        'value' => $value,
                    ];
                    $this->_inPackCount--;                    
                } else {
                    $size = 0;
                    Log::info('DIN CRC');
                }
                break;
            case 'ROM':
                if (strlen($this->_inBuffer) < 13) return $result;
                $size = 13;
                $controller = unpack('C', $this->_inBuffer[3])[1];
                $crc = 0;
                for ($i = 0; $i < $size; $i++) {
                    $crc = $this->_crc_table($crc ^ ord($this->_inBuffer[$i]));
                }
                if ($crc === 0) {
                    $returnCmd = 0;
                    $rom = [];
                    for ($i = 0; $i < 8; $i++) {
                        $rom[] = unpack('C', $this->_inBuffer[4 + $i])[1];
                    }
                    $this->_inRooms[] = $rom;
                    $this->_inPackCount--;
                } else {
                    $size = 0;
                }
                break;
            default:
                $sign = '';
        }
        
        if ($sign == '' || $size === 0) {
            for ($i = 1; $i < strlen($this->_inBuffer) - 2; $i++) {
                if ($this->_inBuffer[$i] >= 'A' &&
                    $this->_inBuffer[$i + 1] >= 'A' &&
                    $this->_inBuffer[$i + 2] >= 'A') {
                    $size = $i;
                    break;
                }
            }
        }
        
        if ($size === 0) {
            $this->_inBuffer = '';
            return $result;
        } elseif ($size === strlen($this->_inBuffer)) {
            $this->_inBuffer = '';
            return true;
        } else {
            $this->_inBuffer = substr($this->_inBuffer, $size);
            $result = true;
            goto start_loop;
        }
    }
    
    /**
     * CRC calculating.
     * 
     * @param int $data
     * @return type
     */
    private function _crc_table($data) 
    {
        $crc = 0x0;
        $fb_bit = 0;
        for ($b = 0; $b < 8; $b++) { 
            $fb_bit = ($crc ^ $data) & 0x01;
            if ($fb_bit == 0x01) {
                $crc = $crc ^ 0x18;
            }
            $crc = ($crc >> 1) & 0x7F;
            if ($fb_bit == 0x01) {
                $crc = $crc | 0x80;
            }
            $data >>= 1;
        }
        return $crc;
    }
    
    /**
     * 
     * @param type $controllerROM
     * @param type $cmd
     * @param type $tag
     */
    private function _transmitCMD($controllerROM, $cmd, $tag) 
    {
        $pack = pack('a*', 'CMD');
        $pack .= pack('C', $controllerROM);
        $pack .= pack('C', $cmd);
        $pack .= pack('s', $tag);        
        $crc = 0x0;
        for ($i = 0; $i < strlen($pack); $i++) {
            $crc = $this->_crc_table($crc ^ ord($pack[$i]));
        }
        $pack .= pack('C', $crc);        
        fwrite($this->_port, $pack);
        fflush($this->_port);
    }
    
    /**
     * 
     * @param type $controllerROM
     * @param type $id
     * @param type $value
     */
    private function _transmitVAR($controllerROM, $id, $value) 
    {
        $pack = pack('a*', 'VAR');
        $pack .= pack('C', $controllerROM);
        $pack .= pack('s', $id);
        $pack .= pack('s', ceil($value * 10));
        $crc = 0x0;
        for ($i = 0; $i < strlen($pack); $i++) {
            $crc = $this->_crc_table($crc ^ ord($pack[$i]));
        }
        $pack .= pack('C', $crc);
        fwrite($this->_port, $pack);
        fflush($this->_port);
    }
    
    /**
     * 
     * @param type $controllerROM
     * @param type $data
     */
    private function _transmitHEX($controllerROM, $data) 
    {
        $pack = pack('a*', 'HEX');
        $pack .= pack('C', $controllerROM);
        for ($i = 0; $i < 8; $i++) {
            $pack .= pack('C', isset($data[$i]) ? $data[$i] : 0xff);
        }
        $crc = 0x0;
        for ($i = 0; $i < strlen($pack); $i++) {
            $crc = $this->_crc_table($crc ^ ord($pack[$i]));
        }
        $pack .= pack('C', $crc);
        fwrite($this->_port, $pack);
        fflush($this->_port);
        
        usleep(10000); // Otherwise, the controller does not have time to process.
    }
}