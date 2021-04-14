<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JurnalController extends Controller
{
    public function index()
    {
        return redirect(route('admin.jurnal-history'));
    }
}
