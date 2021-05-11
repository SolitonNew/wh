<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UsersRequest;
use \App\Models\UsersModel;

class UsersController extends Controller
{
    /**
     * The index route for working with the list of the system users.
     * 
     * @return type
     */
    public function index() 
    {
        $data = UsersModel::listAll();
        
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
        $item = UsersModel::findOrCreate($id);
        
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
        UsersModel::storeFromRequest($request, $id);
        
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
        UsersModel::deleteById($id);
        
        return 'OK';
    }
}
