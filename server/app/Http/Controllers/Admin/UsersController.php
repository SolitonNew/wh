<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UsersRequest;
use Auth;

class UsersController extends Controller
{
    /**
     * The index route for working with the list of the system users.
     * 
     * @return type
     */
    public function index() 
    {
        $data = \App\Http\Models\UsersModel::orderBy('login', 'asc')->get();
        
        return view('admin.users.users', [
            'data' => $data,
        ]);
    }
    
    /**
     * The route to create or update the user entries.
     * 
     * @param int $id
     * @return string
     */
    public function editShow(int $id) 
    {
        $item = \App\Http\Models\UsersModel::find($id);
        if (!$item) {
            $item = (object)[
                'id' => -1,
                'login' => '',
                'email' => '',
                'access' => 1,
            ];
        }
        return view('admin.users.user-edit', [
            'item' => $item,
        ]);
    }
    
    /**
     * The route to create or update the user entries.
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function editPost(UsersRequest $request, int $id)
    {
        \App\Http\Models\UsersModel::storeFromRequest($request, $id);
        return 'OK';
    }
    
    /**
     * The route to delete the user entries by id.
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) 
    {
        \App\Http\Models\UsersModel::deleteById($id);
        return 'OK';
    }
}
