<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;

class JurnalController extends Controller
{
    /**
     * This route redirects to the history page of the journal.
     * 
     * @return redirect
     */
    public function index()
    {
        $page = Property::getLastViewID('JURNAL_PAGE') ?: 'daemons';
        
        return redirect(route('admin.jurnal-'.$page));
    }
}
