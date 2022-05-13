<?php

namespace App\Http\Controllers\Terminal;

use App\Http\Controllers\Controller;
use App\Library\Speech;

class MediaController extends Controller
{
    /**
     * 
     * @param string $typ
     * @param int $id
     * @return string
     */
    public function getData(string $typ, int $id)
    {
        switch ($typ) {
            case 'speech':
                return response()->file(Speech::makeMediaFileName($mediaID));
            case 'play':
                return '';
        }
    }
}
