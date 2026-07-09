<?php

namespace App\helpers;

class Request
{
    public static function method(): string
    {
        //RETORNA O METHODO DA REQUISIÇÃO,strtolower:DEIXA O TEXTO EM NINUSCULO 
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
}
