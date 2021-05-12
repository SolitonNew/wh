<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Auth;

class User extends Authenticatable
{
    use Notifiable;
    
    protected $table = 'web_users';
    public $timestamps = false;
    
    /**
     * 
     * @return type
     */
    static public function listAll()
    {
        return User::orderBy('login', 'asc')->get();
    }
    
    /**
     * 
     * @param int $id
     * @return \App\Models\User
     */
    static public function findOrCreate(int $id)
    {
        $item = User::find($id);
        if (!$item) {
            $item = new User();
            $item->id = -1;
            $item->access = 1;
        }
        
        return $item;
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     */
    static public function storeFromRequest(Request $request, int $id)
    {
        try {
            $item = User::find($id);
            if (!$item) {
                $item = new User();
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
        $item = User::find($id);
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
