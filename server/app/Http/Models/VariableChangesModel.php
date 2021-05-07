<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Facades\DB;
use Lang;

class VariableChangesModel extends Model
{
    protected $table = 'core_variable_changes';
    public $timestamps = false;

    /**
     * 
     * @param int $id
     */
    static public function deleteById(int $id)
    {
        try {
            $item = VariableChangesModel::find($id);
            if (!$item) abort(404);
            
            $item->delete();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
