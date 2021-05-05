<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Http\Request;

class PlanVideoModel extends Model
{
    protected $table = 'plan_video';
    public $timestamps = false;
 
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
                $item = new \App\Http\Models\PlanVideoModel();
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
            $item = \App\Http\Models\PlanVideoModel::find($id);
            $item->delete();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
