<?php

// Ponto de entrada único para a aplicação.

// 1. Incluir e iniciar a segurança da sessão ANTES de qualquer outra coisa.
require_once __DIR__ .
    '/../app/middleware/SessionSecurity.php';
\App\middleware\SessionSecurity::iniciarSessao();

// 2. Incluir o autoloader do Composer.
require __DIR__ .
    '/../vendor/autoload.php';

// 3. Carregar variáveis de ambiente.
use App\helpers\GetEnv;

GetEnv::load();

// 4. Executar o roteador para despachar a requisição.
use App\routes\Router;

Router::execute();
