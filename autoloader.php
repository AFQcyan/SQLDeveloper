<?php

function autoloader($c)
{
    require __ROOT . __DS . str_replace('\\', __DS, $c) . '.php';
}

spl_autoload_register('autoloader');
