<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ScriptsController extends Controller
{
    public function index(int $scriptID = 0) {
        $data = \App\Http\Models\ScriptsModel::find($scriptID);
        
        if ($data) {
            
            $words = [
                'elif' => '@KEY_2@',
                'if' => '@KEY_1@',
                'else' => '@KEY_3@',
                'for' => '@KEY_4@',
                'import' => '@KEY_5@',
                'pass' => '@KEY_6@',
                'not' => '@KEY_7@',
            ];
            
            $sourceCode = $data->DATA;
            $sourceCode = str_replace(' ', '&nbsp;', $sourceCode);
            
            foreach($words as $key => $val) {
                $sourceCode = str_replace($key, $val, $sourceCode);
            }
            
            foreach($words as $key => $val) {
                $sourceCode = str_replace($val, '<span class="code-keyword">'.$key.'</span>', $sourceCode);
            }
            
            $sourceCode = nl2br($sourceCode);
        } else {
            $sourceCode = '';
        }
        
        return view('admin.scripts', [
            'scriptID' => $scriptID,
            'data' => $data,
            'sourceCode' => $sourceCode,
        ]);
    }
}
