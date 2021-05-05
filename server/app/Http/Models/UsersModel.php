<?php

namespace App\Http\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use \Illuminate\Http\Request;
use Auth;

class UsersModel extends Authenticatable
{
    use Notifiable;
    
    protected $table = 'web_users';
    public $timestamps = false;
    
    /**
     * 
     * @param Request $request
     * @param int $id
     */
    static public function storeFromRequest(Request $request, int $id)
    {
        try {
            $item = UsersModel::find($id);
            if (!$item) {
                $item = new UsersModel();
            }
            $item->login = $request->login;
            $item->email = $request->email;
            if ($request->password) {
                $item->password = bcrypt($request->password);
            }
            if ($id != Auth::user()->id) {
                $item->access = $request->access;
            }
            $item->save();
        } catch (\Exception $ex) {
            abord(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @param int $id
     */
    static public function deleteById(int $id) 
    {
        $item = \App\Http\Models\UsersModel::find($id);
        if ($item) {
            try {
                $item->delete();
            } catch (\Exception $ex) {
                abort(response()->json([
                    'errors' => [$ex->getMessage()],
                ]), 422);
            }
        }
    }
}
