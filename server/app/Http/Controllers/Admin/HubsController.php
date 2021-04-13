<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class HubsController extends Controller
{
    /**
     * Индексный маршрут.
     * Если есть хотя бы один хаб делает переадресацию на страницу devices
     * 
     * @param int $hubID
     * @return type
     */
    public function index(int $hubID = null) {
        if (!$hubID) {
            $hubID = \App\Http\Models\ControllersModel::orderBy('name', 'asc')->first();
            if ($hubID) {
                $hubID = $hubID->id;
            } else {
                $hubID = null;
            }
        }
        
        if ($hubID) {
            return redirect(route('admin.hub-devices', [$hubID]));
        } else {
            return view('admin/hubs/hubs', [
                'hubID' => $hubID,
            ]);
        }
    }
    
    /**
     * Маршрут создания/редактирования записи хаба.
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function edit(Request $request, int $id) {
        $item = \App\Http\Models\ControllersModel::find($id);

        if ($request->method() == 'POST') {
            try {
                $this->validate($request, [
                    'name' => 'string|required',
                    'comm' => 'string|nullable',
                    'rom' => 'numeric|required|unique:core_controllers,rom,'.($id > 0 ? $id : ''),
                ]);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->errors());
            }
            
            try {
                if (!$item) {
                    $item = new \App\Http\Models\ControllersModel();
                }
                
                $item->name = $request->post('name');
                $item->rom = $request->post('rom');
                $item->comm = $request->post('comm');
                $item->save();
                
            } catch (\Exception $ex) {
                return response()->json([
                    'errors' => $ex->getMessage(),
                ]);
            }
            
            // Перезапускаем rs485-demon
            $this->_restartRs485Demon();
            
            return 'OK';
        } else {
            if (!$item) {
                $item = (object)[
                    'id' => -1,
                    'name' => '',
                    'rom' => null,
                    'comm' => '',
                    'status' => 1,
                ];
            }
            
            return view('admin/hubs/hub-edit', [
                'item' => $item,
            ]);
        }
    }
    
    /**
     * Маршрут удаления хаба по ИД
     * 
     * @param int $id
     * @return type
     */
    public function delete(int $id) {
        try {
            $item = \App\Http\Models\ControllersModel::find($id);
            if (!$item) {
                return abort(404);
            }
            $item->delete();
            
            // Перезапускаем rs485-demon
            $this->_restartRs485Demon();
            
            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage(),
            ]);
        }
    }
    
    /**
     * Маршрут запускает команду сканирования хабами подчиненных хостов.
     * Результатом работы будет вьюха диалога с отчетом по сканированию.
     * 
     * @return type
     */
    public function hubsScan() {
        \App\Http\Models\PropertysModel::setRs485Command('OW SEARCH');
        $i = 0;
        while ($i++ < 500) { // 5 sec
            usleep(100000);
            $text = \App\Http\Models\PropertysModel::getRs485CommandInfo();
            if ($t = strpos($text, 'END_OW_SCAN')) {
                $text = substr($text, 0, $t);
                break;
            }
        }
        
        // Сразу же запускаем генератор устройств
        // Если устройства небыло - он создаст
        $this->_generateDevs();
        // --------------------------------------
        
        return view('admin.hubs.hubs-scan', [
            'data' => $text,
        ]);
    }
    
    /**
     * Создает запись устройства по каждому каналу хоста, если такой записи еще нет.
     * 
     * @return string
     */
    public function _generateDevs() {
        $devs = DB::select('select d.id, d.controller_id, t.channels, t.comm
                              from core_ow_devs d, core_ow_types t
                             where d.rom_1 = t.code');
        
        $vars = DB::select('select ow_id, channel from core_variables where typ = "ow"');
        
        try {
            foreach($devs as $dev) {
                foreach (explode(',', $dev->channels) as $chan) {
                    $find = false;
                    foreach($vars as $var) {
                        if ($var->ow_id == $dev->id && $var->channel && $var->channel == $chan) {
                            $find = true;
                            break;
                        }
                    }

                    if (!$find) {
                        $item = new \App\Http\Models\VariablesModel();
                        $item->controller_id = $dev->controller_id;
                        $item->typ = 'ow';
                        $item->direction = 0;
                        $item->name = 'temp for ow';
                        $item->comm = $dev->comm;
                        $item->ow_id = $dev->id;
                        $item->channel = $chan;
                        $item->save();
                        $item->name = 'ow_'.$item->id.'_'.$chan;
                        $item->save();
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
     * Маршрут выполняет код по генерации прошивки хабов
     * и возвращает вьюху с отчетом по сборке, а также элементами управления 
     * обновлением
     * 
     * @return type
     */
    public function firmware() {
        $makeError = false;
        $text = '';
        try {
            $firmware = new \App\Library\Firmware();
            
            $firmware->generateConfig();
            
            $outs = [];
            if ($firmware->make($outs)) {
                $text = implode("\n", $outs);
            } else {
                $makeError = true;
                $text = implode("\n", $outs);
            }
        } catch (\Exception $ex) {
            $makeError = true;
            $text = $ex->getMessage();
        }
        
        return view('admin.hubs.firmware', [
            'data' => $text,
            'makeError' => $makeError,
        ]);
    }
    
    /**
     * Маршрут посылает команду rs485-demon которая инициализирует процесс 
     * закачки прошивки в контроллеры.
     * 
     * @return string
     */
    public function firmwareStart() {
        \App\Http\Models\PropertysModel::setRs485Command('FIRMWARE');
        \App\Http\Models\PropertysModel::setRs485CommandInfo('', true);
        return 'OK';
    }
    
    /**
     * Маршрут возвращает теекущее состояние процесса прошивки
     * 
     * @return type
     */
    public function firmwareStatus() {
        try {
            $info = \App\Http\Models\PropertysModel::getRs485CommandInfo();
            if ($info == 'COMPLETE') {
                return response()->json([                    
                    'firmware' => 'COMPLETE',
                ]);
            } else 
            if (strpos($info, 'ERROR') !== false) {
                return response()->json([
                    'error' => $info,
                ]);
            } else {
                $a = explode(';', $info);                    
                if (count($a) < 2) {
                    $a = ['', 0];
                }
                return response()->json([
                    'controller' => $a[0],
                    'percent' => $a[1],
                ]);                
            }
        } catch (\Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage(),
            ]);
        }
    }
    
    /**
     * Маршрут посылает команду rs485-demon которая инициализирует перезагрузку 
     * всех хабов.
     * 
     * @return string
     */
    public function hubsReset() {
        try {
            \App\Http\Models\PropertysModel::setRs485Command('RESET');
            return 'OK';            
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * Перезапускает демон rs485-demon
     * 
     * @param \App\Http\Controllers\Admin\DemonManager $demonManager
     * @return string
     */
    private function _restartRs485Demon() {
        $demonManager = new \App\Library\DemonManager();
        $demon = 'rs485-demon';
        try {
            \App\Http\Models\PropertysModel::setAsRunningDemon($demon);
            $demonManager->restart($demon);
            return 'OK';
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
}
