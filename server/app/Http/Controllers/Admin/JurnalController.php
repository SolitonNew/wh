<?php

namespace App\Http\Controllers\Admin;

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
        return redirect(route('admin.jurnal-daemons'));
    }
}
