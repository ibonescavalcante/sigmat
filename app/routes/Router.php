<?php

namespace App\routes;

use App\core\Controller;
use App\helpers\Request;
use App\helpers\Uri;
use App\middleware\SessionSecurity;

class Router
{
    private static function appDebug(): bool
    {
        $v = $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG');
        if ($v === false || $v === null || $v === '') {
            return false;
        }
        return filter_var($v, FILTER_VALIDATE_BOOLEAN);
    }

    private static function logDebug(string $message): void
    {
        if (self::appDebug()) {
            error_log($message);
        }
    }

    public static function routes(): array
    {
        return require __DIR__ . '/web.php';
    }

    private static function isApiRequest(string $uri): bool
    {
        return str_starts_with($uri, '/api/');
    }

    private static function responderNaoAutenticado(string $uri): void
    {
        if (self::isApiRequest($uri)) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['erro' => 'Não autenticado.']);
            exit;
        }
        header('Location: /login');
        exit;
    }

    private static function responderSessaoDashboardSubstituida(string $uri): void
    {
        if (self::isApiRequest($uri)) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['erro' => 'Sessão encerrada. A conta foi acessada em outro local.']);
            exit;
        }
        header('Location: /login?sessao=substituida');
        exit;
    }

    private static function responderCsrfInvalido(string $uri): void
    {
        if (self::isApiRequest($uri)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['erro' => 'Token de segurança inválido ou ausente.']);
            exit;
        }
        $_SESSION['erro'] = 'Sessão de segurança expirada. Atualize a página e tente novamente.';
        $back = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $back);
        exit;
    }

    private static function routeToPattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * @return array{route: array, params: array}|null
     */
    private static function matchRoute(string $method, string $uri): ?array
    {
        foreach (self::routes() as $route) {
            if (($route['method'] ?? '') !== $method) {
                continue;
            }

            $path = $route['path'] ?? '';
            if ($path === $uri) {
                return ['route' => $route, 'params' => []];
            }

            if (str_contains($path, '{')) {
                $pattern = self::routeToPattern($path);
                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches);
                    return ['route' => $route, 'params' => $matches];
                }
            }
        }

        return null;
    }

    public static function load(array $handler, array $params = []): void
    {
        try {
            [$controllerClass, $method] = $handler;

            if (!class_exists($controllerClass)) {
                throw new \Exception("O controller {$controllerClass} não existe");
            }

            $controllerInstance = new $controllerClass();

            if (!method_exists($controllerInstance, $method)) {
                throw new \Exception("O method {$method} não existe");
            }

            $controllerInstance->$method(...$params);
        } catch (\Throwable $th) {
            error_log('Router::load ' . $th->getMessage());
            if (self::appDebug()) {
                echo $th->getMessage();
            } else {
                http_response_code(500);
                echo 'Erro interno do servidor.';
            }
        }
    }

    public static function execute(): void
    {
        $uri = '/';

        try {
            $method = Request::method();
            $uri = Uri::get('path');

            self::logDebug("URI recebida: {$uri}");
            self::logDebug("Método da requisição: {$method}");

            $match = self::matchRoute($method, $uri);
            if ($match === null) {
                throw new \Exception('A rota não existe!');
            }

            $route = $match['route'];
            $params = $match['params'];
            $requiresAuth = !empty($route['auth']);
            $requiresCsrf = !empty($route['csrf']);

            if ($requiresAuth && !SessionSecurity::estaLogadoDashboard()) {
                self::responderNaoAutenticado($uri);
            }

            if (
                $requiresAuth
                && SessionSecurity::estaLogadoDashboard()
                && !SessionSecurity::validarVinculoSessaoDashboard()
            ) {
                SessionSecurity::destruirSessao();
                self::responderSessaoDashboardSubstituida($uri);
            }

            if (
                $requiresCsrf
                && SessionSecurity::estaLogadoDashboard()
                && !SessionSecurity::validarDashboardCsrf()
            ) {
                self::responderCsrfInvalido($uri);
            }

            if ($requiresAuth && SessionSecurity::estaLogadoDashboard()) {
                SessionSecurity::ensureDashboardCsrfToken();
            }

            self::load($route['handler'], $params);
        } catch (\Throwable $th) {
            error_log('Erro geral no roteador: ' . $th->getMessage());

            if (self::appDebug()) {
                echo $th->getMessage();
                return;
            }

            $msg = $th->getMessage();
            $is404 = str_contains($msg, 'Arquivo não encontrado')
                || str_contains($msg, 'A rota não existe');

            if ($is404) {
                self::responder404($uri);
            }

            http_response_code(500);
            echo 'Ocorreu um erro ao processar a solicitação.';
        }
    }

    private static function responder404(string $uri): void
    {
        if (self::isApiRequest($uri)) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['erro' => 'Página não encontrada.']);
            exit;
        }

        http_response_code(404);
        $renderer = new class extends Controller {};
        $renderer->view('404/page');
        exit;
    }
}
