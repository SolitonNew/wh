<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use DB;

class PlanVideoModel extends Model
{
    protected $table = 'plan_video';
    public $timestamps = false;
    
    /**
     * 
     * @return type
     */
    static public function listAll()
    {
        $sql = 'select c.*,
                       v.name var_name
                  from plan_video c
                left join core_variables v on c.alert_var_id = v.id
                order by c.name';
        
        return DB::select($sql);
    }
    
    /**
     * 
     * @param int $id
     */
    static public function findOrCreate(int $id)
    {
        $item = PlanVideoModel::find($id);
        if (!$item) {
            $item = new PlanVideoModel();
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
            $item = PlanVideoModel::find($id);
            if (!$item) {
                $item = new PlanVideoModel();
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
            $item = PlanVideoModel::find($id);
            $item->delete();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
