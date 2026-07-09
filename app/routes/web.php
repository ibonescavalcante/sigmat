<?php

use App\controllers\ApiController;
use App\controllers\AuthController;
use App\controllers\CarteirinhaController;
use App\controllers\ServidoresController;
use App\controllers\UsuariosController;

return [
    [
        'method'  => 'get',
        'path'    => '/',
        'handler' => [AuthController::class, 'index'],
        'auth'    => false,
        'csrf'    => false,
    ],
    [
        'method'  => 'get',
        'path'    => '/login',
        'handler' => [AuthController::class, 'index'],
        'auth'    => false,
        'csrf'    => false,
    ],
    [
        'method'  => 'post',
        'path'    => '/login',
        'handler' => [AuthController::class, 'logar'],
        'auth'    => false,
        'csrf'    => false,
    ],
    [
        'method'  => 'post',
        'path'    => '/',
        'handler' => [AuthController::class, 'logar'],
        'auth'    => false,
        'csrf'    => false,
    ],
    [
        'method'  => 'get',
        'path'    => '/logout',
        'handler' => [AuthController::class, 'logout'],
        'auth'    => true,
        'csrf'    => false,
    ],
    [
        'method'  => 'get',
        'path'    => '/servidores',
        'handler' => [ServidoresController::class, 'index'],
        'auth'    => true,
        'csrf'    => false,
    ],
    [
        'method'  => 'post',
        'path'    => '/servidores',
        'handler' => [ServidoresController::class, 'criar'],
        'auth'    => true,
        'csrf'    => true,
    ],
    [
        'method'  => 'get',
        'path'    => '/servidores/{id}',
        'handler' => [ServidoresController::class, 'obter'],
        'auth'    => true,
        'csrf'    => false,
    ],
    [
        'method'  => 'post',
        'path'    => '/servidores/atualizar',
        'handler' => [ServidoresController::class, 'atualizar'],
        'auth'    => true,
        'csrf'    => true,
    ],
    [
        'method'  => 'post',
        'path'    => '/servidores/excluir',
        'handler' => [ServidoresController::class, 'excluir'],
        'auth'    => true,
        'csrf'    => true,
    ],
    [
        'method'  => 'get',
        'path'    => '/carteirinha',
        'handler' => [CarteirinhaController::class, 'index'],
        'auth'    => true,
        'csrf'    => false,
    ],
    [
        'method'  => 'get',
        'path'    => '/usuarios',
        'handler' => [UsuariosController::class, 'index'],
        'auth'    => true,
        'csrf'    => false,
    ],
    [
        'method'  => 'post',
        'path'    => '/usuarios/usuario',
        'handler' => [UsuariosController::class, 'criar'],
        'auth'    => true,
        'csrf'    => true,
    ],
    [
        'method'  => 'post',
        'path'    => '/usuarios/usuario/atualizar',
        'handler' => [UsuariosController::class, 'atualizar'],
        'auth'    => true,
        'csrf'    => true,
    ],
    [
        'method'  => 'post',
        'path'    => '/api/alterar-senha',
        'handler' => [ApiController::class, 'alterarSenha'],
        'auth'    => true,
        'csrf'    => true,
    ],
];
