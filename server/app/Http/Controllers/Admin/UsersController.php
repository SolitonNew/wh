<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function edit(Request $request, int $id) 
    {
        if ($request->method() == 'POST') {
            try {
                $this->validate($request, [
                    'login' => 'required|string|unique:web_users,login,'.($id > 0 ? $id : ''),
                    'password' => 'string|'.($id > 0 ? 'nullable' : 'required'),
                    'email' => 'nullable|email|string',
                ]);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->validator->errors());
            }

            try {
                if ($id == -1) {
                    $item = new \App\Http\Models\UsersModel();
                } else {
                    $item = \App\Http\Models\UsersModel::find($id);
                }
                $item->login = $request->post('login');
                $item->email = $request->post('email');
                if ($request->post('password')) {
                    $item->password = bcrypt($request->post('password'));
                }
                if ($id != Auth::user()->id) {
                    $item->access = $request->post('access');
                }
                $item->save();
                return 'OK';
            } catch (\Exception $ex) {
                return response()->json([
                    'error' => [$ex->errorInfo],
                ]);
            }
        } else {
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
    }
    
    /**
     * The route to delete the user entries by id.
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) 
    {
        $item = \App\Http\Models\UsersModel::find($id);
        if ($item) {
            try {
                $item->delete();
                return 'OK';
            } catch (\Exception $ex) {
                return 'ERROR';
            }
        }
        return 'NOT FOUND';
    }
}
