<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    /**
     * 
     * @return type
     */
    public function index() {
        $data = \App\Http\Models\UsersModel::orderBy('login', 'asc')->get();
        
        return view('admin.users', [
            'data' => $data,
        ]);
    }
        
    public function edit(Request $request, int $id) {
        if ($request->method() == 'POST') {
            try {
                if ($id == -1) {
                    $this->validate($request, [
                        'login' => 'required|string',
                        'password' => 'required|string',
                        'email' => 'nullable|email|string',
                    ]);
                } else {
                    $this->validate($request, [
                        'login' => 'required|string',
                        'email' => 'nullable|string',
                    ]);
                }
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
                $item->access = $request->post('access');
                $item->save();
                return 'OK';
            } catch (\Exception $ex) {
                return $ex;
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
            return view('admin.user-edit', [
                'item' => $item,
            ]);
        }
    }
    
    public function delete(int $id) {
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
