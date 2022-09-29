<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Http\Request;
use Auth;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $table = 'web_users';
    public $timestamps = false;

    /**
     * @return Collection
     */
    public static function listAll(): Collection
    {
        return User::orderBy('login', 'asc')->get();
    }

    /**
     * @param int $id
     * @return User
     */
    public static function findOrCreate(int $id): User
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
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function storeFromRequest(Request $request, int $id)
    {
        // Validation  ----------------------
        $rules = [
            'login' => 'required|string|unique:web_users,login,'.($id > 0 ? $id : ''),
            'password' => 'string|'.($id > 0 ? 'nullable' : 'required'),
            'email' => 'nullable|email|string',
        ];

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        // Saving -----------------------
        try {
            $item = User::find($id);
            if (!$item) {
                $item = new User();
            }
            $item->login = $request->login;
            $item->email = $request->email;
            if ($request->password) {
                $item->password = app('hash')->make($request->password);
            }
            if ($id != Auth::user()->id) {
                $item->access = $request->access;
            }
            $item->save();
            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'errors' => [$ex->getMessage()],
            ]);
        }
    }

    /**
     * @param int $id
     * @return void
     */
    public static function deleteById(int $id): void
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
