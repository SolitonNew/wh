<?php

namespace App\Http\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UsersModel extends Authenticatable
{
    use Notifiable;
    
    protected $table = 'web_users';
    public $timestamps = false;
    protected $primaryKey = 'ID';
}
