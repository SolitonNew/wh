<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoomsController extends Controller
{
    public function index(int $partID = 1) {
        return view('admin.rooms', [
            'partID' => $partID,
        ]);
    }
}
