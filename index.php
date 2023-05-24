<?php

session_start();

use src\App\Route;

date_default_timezone_set('Asia/Seoul');

define('__ROOT', __DIR__);
define('__DS', '/');
define('__SRC', __ROOT . __DS . 'src');
define('__VIEWS', __SRC . __DS . 'View');

require __ROOT . __DS . 'autoloader.php';
require __ROOT . __DS . 'lib.php';
require __ROOT . __DS . 'web.php';

Route::init();
