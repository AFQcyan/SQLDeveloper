<?php

namespace src\Controller;

use src\App\DB;

class ViewController
{
    public function sql()
    {
        $datas['title'] = 'SQL 결과';
        view('sql', $datas);
    }
}
