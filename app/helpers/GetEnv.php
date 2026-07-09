<?php

namespace App\helpers;

class getenv
{
    public static function load()
    {
        $path = dirname(__FILE__, 3);
        $dotenv = \Dotenv\Dotenv::createImmutable($path);
        $dotenv->load();
    }
}
