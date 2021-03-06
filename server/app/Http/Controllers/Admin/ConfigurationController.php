<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class ConfigurationController extends Controller
{
    /**
     * 
     * @param int $id
     * @return type
     */
    public function index(int $id = null) {
        if (!$id) {
            $item = \App\Http\Models\ControllersModel::orderBy('ID', 'asc')->first();
            if ($item) {
                return redirect(route('configuration', $item->ID));
            }
        }
        
        $sql = 'select d.ID, 
                       c.NAME CONTROLLER_NAME, 
                       "" ROM,
                       d.ROM_1, d.ROM_2, d.ROM_3, d.ROM_4, d.ROM_5, d.ROM_6, d.ROM_7,
                       t.CHANNELS,
                       t.COMM,
                       "" VARIABLES
                  from core_ow_devs d, core_ow_types t, core_controllers c
                 where d.CONTROLLER_ID = c.ID
                   and d.ROM_1 = t.CODE
                   and d.CONTROLLER_ID = '.$id.' 
                order by c.NAME, d.ROM_1, d.ROM_2, d.ROM_3, d.ROM_4, d.ROM_5, d.ROM_6, d.ROM_7';
        $data = DB::select($sql);
        
        foreach($data as &$row) {
            $row->ROM = sprintf("x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X", 
                $row->ROM_1, 
                $row->ROM_2, 
                $row->ROM_3, 
                $row->ROM_4, 
                $row->ROM_5, 
                $row->ROM_6, 
                $row->ROM_7
            );
            
            $row->VARIABLES = DB::select('select v.ID, v.NAME, v.CHANNEL
                                            from core_variables v 
                                           where v.ROM = "ow" 
                                             and v.OW_ID = '.$row->ID.'
                                          order by v.CHANNEL');
        }
        
        return view('admin.configuration.configuration', [
            'id' => $id,
            'data' => $data,
        ]);
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return type
     */
    public function edit(Request $request, int $id) {
        if ($request->method() == 'POST') {
            try {
                $this->validate($request, [
                    'NAME' => 'string|required',
                    'COMM' => 'string|required',
                ]);          
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->validator->errors());
            }

            try {
                $item = \App\Http\Models\ControllersModel::find($id);
                if (!$item) {
                    $item = new \App\Http\Models\ControllersModel();
                }
                $item->NAME = $request->post('NAME');
                $item->COMM = $request->post('COMM');
                $item->STATUS = 0; 
                $item->POSITION = '';
                $item->save();
                return 'OK';
            } catch (\Exception $ex) {
                return response()->json([
                    'error' => [$ex->errorInfo],
                ]);
            }
        } else {
            $item = \App\Http\Models\ControllersModel::find($id);
            if (!$item) {
                $item = (object)[
                    'ID' => -1,
                    'NAME' => '',
                    'COMM' => '',
                    'STATUS' => 0,
                ];
            }
            return view('admin.configuration.configuration-edit', [
                'item' => $item,
            ]);
        }
    }
    
    /**
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) {
        try {
            $item = \App\Http\Models\ControllersModel::find($id);
            $item->delete();            
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * 
     * @param int $id
     * @return type
     */
    public function owInfo(int $id) {
        $sql = 'select d.ID, 
                       c.NAME CONTROLLER_NAME, 
                       "" ROM,
                       d.ROM_1, d.ROM_2, d.ROM_3, d.ROM_4, d.ROM_5, d.ROM_6, d.ROM_7,
                       t.CHANNELS,
                       t.COMM,
                       "" VARIABLES
                  from core_ow_devs d, core_ow_types t, core_controllers c
                 where d.CONTROLLER_ID = c.ID
                   and d.ROM_1 = t.CODE
                   and d.ID = '.$id.'
                order by c.NAME, d.ROM_1, d.ROM_2, d.ROM_3, d.ROM_4, d.ROM_5, d.ROM_6, d.ROM_7';
        $data = DB::select($sql);
        if (count($data)) {
            $item = $data[0];
        } else {
            abort(404);
        }
        
        $item->ROM = sprintf("x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X", 
            $item->ROM_1, 
            $item->ROM_2, 
            $item->ROM_3, 
            $item->ROM_4, 
            $item->ROM_5, 
            $item->ROM_6, 
            $item->ROM_7
        );
        
        $sql = 'select v.ID, v.NAME, v.CHANNEL
                  from core_variables v 
                 where v.ROM = "ow" 
                   and v.OW_ID = '.$item->ID.'
                order by v.CHANNEL';
                
        $item->VARIABLES = DB::select($sql);
        
        return view('admin.configuration.configuration-ow-info', [
            'item' => $item,
        ]);
    }
    
    /**
     * 
     * @param int $id
     * @return string
     */
    public function owDelete(int $id) {
        try {
            $item = \App\Http\Models\OwDevsModel::find($id);
            $item->delete();            
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * 
     * @return string
     */
    public function generateVarsForFreeDevs() {
        $devs = DB::select('select d.ID, d.CONTROLLER_ID, t.CHANNELS, t.COMM
                              from core_ow_devs d, core_ow_types t
                             where d.ROM_1 = t.CODE');
        
        $vars = DB::select('select OW_ID, CHANNEL from core_variables where ROM = "ow"');
        
        try {
            foreach($devs as $dev) {
                foreach (explode(',', $dev->CHANNELS) as $chan) {
                    $find = false;
                    foreach($vars as $var) {
                        if ($var->OW_ID == $dev->ID && $var->CHANNEL && $var->CHANNEL == $chan) {
                            $find = true;
                            break;
                        }
                    }

                    if (!$find) {
                        $item = new \App\Http\Models\VariablesModel();
                        $item->CONTROLLER_ID = $dev->CONTROLLER_ID;
                        $item->ROM = 'ow';
                        $item->DIRECTION = 0;
                        $item->NAME = 'TEMP FOR OW';
                        $item->COMM = $dev->COMM;
                        $item->OW_ID = $dev->ID;
                        $item->CHANNEL = $chan;
                        $item->save();
                        $item->NAME = 'OW_'.$item->ID.'_'.$chan;
                        $item->save();
                        
                        Log::info($item->OW_ID);
                    }
                }
            }
            return 'OK';
        } catch (\Exception $ex) {
            Log::info($ex);
            return 'ERROR';
        }
    }
    
    /**
     * 
     * @param int $id
     * @return string
     */
    public function configurationApply(int $id = null) {
        try {
            $mmcu = 'atmega8a';
            
            $path = explode('/', base_path());
            array_pop($path);
            $path[] = 'devices/din_master/firmware';
            $path = implode('/', $path);
            
            $path_c = $path.'/din_master.c';
            
            if (!file_exists($path.'/Release')) {
                mkdir($path.'/Release');
            }
            
            $path_o = $path.'/Release/din_master.o';
            $path_elf = $path.'/Release/din_master.elf';
            $path_hex = $path.'/Release/din_master.hex';
            
            // Компилируем в объектный файл
            
            $command = "avr-gcc -funsigned-char -funsigned-bitfields -Os -fpack-struct "
                     . "-fshort-enums -Wall -c -std=gnu99 -MD -MP -mmcu=$mmcu "
                     . "-o $path_o $path_c";

            exec($command.' 2>&1', $outs);
            
            if (count($outs)) {
                $outs = implode("\n", $outs);
                $outs = str_replace($path, '', $outs);
                return response()->json($outs);
            }
            
            // Получаем бинарный файл
            
            $command = "avr-gcc -o $path_elf $path_o -Wl,-Map=\"din_master.map\" -Wl,-lm -mmcu=$mmcu ";
            exec($command.' 2>&1', $outs);
            
            if (count($outs)) {
                $outs = implode("\n", $outs);
                $outs = str_replace($path, '', $outs);
                return response()->json($outs);
            }
            
            // Получаем прошивку в формате IntelHEX
            
            $command = "avr-objcopy -O ihex -R .eeprom -R .fuse -R .lock -R .signature  $path_elf $path_hex";
            exec($command.' 2>&1', $outs);
            
            if (count($outs)) {
                $outs = implode("\n", $outs);
                $outs = str_replace($path, '', $outs);
                return response()->json($outs);
            }
            
            // Получаем параметры новой прошивки
            $command = "avr-size -C --mcu=$mmcu $path_elf";
            exec($command.' 2>&1', $outs);
            
            if (count($outs)) {
                $outs = implode("\n", $outs);
                $outs = str_replace($path, '', $outs);
                return response()->json($outs);
            }
            
            return 'OK';
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
    
}
