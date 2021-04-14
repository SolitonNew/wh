<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    /**
     * Индексный маршрут. 
     * Выполняет переадресацию на страницу хабов.
     * 
     * @return type
     */
    public function index() 
    {
        return redirect(route('admin.hubs'));
    }
        
    /**
     * Маршрут для запроса последних изменений значений устройств.
     * Результат выводится в главном окне в виде лога.
     * 
     * @param int $lastID
     * @return type
     */
    public function variableChanges(int $lastID) 
    {
        \App\Http\Models\VariableChangesMemModel::setLastVariableID($lastID);
        return view('admin.log');
    }
}
