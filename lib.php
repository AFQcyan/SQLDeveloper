<?php

use src\App\{DB, Session};

function view($path, $datas)
{
    extract($datas);
    require_once __VIEWS . __DS . $path . '.php';
}
