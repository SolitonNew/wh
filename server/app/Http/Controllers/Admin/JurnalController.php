<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JurnalController extends Controller
{
    /**
     * This route redirects to the history page of the journal.
     * 
     * @return redirect
     */
    public function index()
    {
        return redirect(route('admin.jurnal-history'));
    }
}
