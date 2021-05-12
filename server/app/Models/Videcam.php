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
     */
    static public function storeFromRequest(Request $request)
    {
        try {
            $id = $request->route('id');
            $item = Videcam::find($id);
            if (!$item) {
                $item = new Videcam();
            }
            $item->name = $request->name;
            $item->url = $request->url;
            $item->url_low = $request->url_low;
            $item->url_high = $request->url_high;
            $item->alert_var_id = $request->alert_var_id;
            $item->save();

            if ($id == -1) {
                $item->order_num = $item->id;
                $item->save();
            }
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
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
