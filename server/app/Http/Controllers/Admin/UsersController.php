<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class UsersController extends Controller
{
    /**
     * 
     * @return type
     */
    public function index() {
        $data = \App\Http\Models\UsersModel::orderBy('LOGIN', 'asc')->get();
        
        return view('admin.users', [
            'data' => $data,
        ]);
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function edit(Request $request, int $id) {
        if ($request->method() == 'POST') {
            try {
                $this->validate($request, [
                    'LOGIN' => 'required|string|unique:web_users,LOGIN,'.($id > 0 ? $id : ''),
                    'password' => 'string|'.($id > 0 ? 'nullable' : 'required'),
                    'EMAIL' => 'nullable|email|string',
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
                $item->LOGIN = $request->post('LOGIN');
                $item->EMAIL = $request->post('EMAIL');
                if ($request->post('password')) {
                    $item->password = bcrypt($request->post('password'));
                }
                if ($id != Auth::user()->ID) {
                    $item->ACCESS = $request->post('ACCESS');
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
                    'ID' => -1,
                    'LOGIN' => '',
                    'EMAIL' => '',
                    'ACCESS' => 1,
                ];
            }
            return view('admin.user-edit', [
                'item' => $item,
            ]);
        }
    }
    
    /**
     * 
     * @param int $id
     * @return string
     */
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
