<?php

use src\App\Route;

// 메인페이지
Route::get("/sql/{sql}", 'SQLController@sql');
