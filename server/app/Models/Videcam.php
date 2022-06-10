<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Videcam extends Model
{
    protected $table = 'plan_videcams';
    public $timestamps = false;
    
    public function device()
    {
        return $this->belongsTo(Device::class, 'alert_var_id');
    }
    
    /**
     * 
     * @return type
     */
    static public function listAll()
    {
        $data = Videcam::with('device')
                    ->orderBy('name', 'asc')
                    ->get();
        return $data;
    }
    
    /**
     * 
     * @param int $id
     */
    static public function findOrCreate(int $id)
    {
        $item = Videcam::find($id);
        if (!$item) {
            $item = new Videcam();
            $item->id = $id;
            $item->alert_var_id = -1;
        }
        return $item;
    }
 
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    static public function storeFromRequest(Request $request, int $id)
    {
        // Validation  ----------------------
        $rules = [
            'name' => 'required|string|unique:plan_videcams,name,'.($id > 0 ? $id : ''),
            'url' => 'required|string',
        ];
        
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        // Saving -----------------------
        try {
            $id = $request->route('id');
            $item = Videcam::find($id);
            if (!$item) {
                $item = new Videcam();
            }
            $item->name = $request->name;
            $item->url = $request->url;
            $item->url_low = '';
            $item->url_high = '';
            $item->alert_var_id = $request->alert_var_id ?: null;
            $item->save();

            if ($id == -1) {
                $item->order_num = $item->id;
                $item->save();
            }
            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'errors' => [$ex->getMessage()],
            ]);
        }
    }
    
    /**
     * 
     * @param type $id
     */
    static public function deleteById($id)
    {
        try {
            $item = Videcam::find($id);
            $item->delete();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
