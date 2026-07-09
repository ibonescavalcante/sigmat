<?php

namespace App\middleware;

use App\models\UsuarioSessaoDashboard;

class SessionSecurity
{
    /**
     * Inicia uma sessão segura, aplicando configurações de segurança antes de iniciar a sessão.
     */
    public static function iniciarSessao()
    {
        // Aplicar configurações de segurança APENAS se a sessão ainda não foi iniciada
        if (session_status() === PHP_SESSION_NONE) {
            // Detectar se está em HTTPS
            $isHTTPS = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
                || $_SERVER["SERVER_PORT"] == 443
                || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] === "https");

            // Configurações de segurança da sessão
            ini_set("session.cookie_httponly", 1);
            ini_set("session.cookie_secure", $isHTTPS ? 1 : 0); // Só exigir HTTPS se disponível
            ini_set("session.use_strict_mode", 1);

            // SameSite apenas se suportado
            if (version_compare(PHP_VERSION, "7.3.0", ">=")) {
                ini_set("session.cookie_samesite", "Lax"); // Menos restritivo que Strict
            }

            // Tempo de vida da sessão (2 horas para desenvolvimento)
            ini_set("session.gc_maxlifetime", 7200);
            ini_set("session.cookie_lifetime", 7200);

            // Regenerar ID da sessão periodicamente
            ini_set("session.gc_probability", 1);
            ini_set("session.gc_divisor", 1000); // Menos frequente

            // Nome da sessão personalizado
            session_name("PSS_SESSION");

            // Iniciar a sessão
            session_start();
        }

        // Adicionar timestamps se não existirem (ou se a sessão acabou de ser iniciada)
        if (!isset($_SESSION["_created"])) {
            $_SESSION["_created"] = time();
        }

        if (!isset($_SESSION["_last_activity"])) {
            $_SESSION["_last_activity"] = time();
        }

        if (!isset($_SESSION["_last_regenerate"])) {
            $_SESSION["_last_regenerate"] = time();
        }

        // Validar sessão existente
        return self::validarSessao();
    }

    /**
     * Valida se a sessão é válida e segura
     */
    public static function validarSessao()
    {
        if (session_status() === PHP_SESSION_NONE) {
            return false; // Nenhuma sessão ativa para validar
        }

        // Verificar se a sessão tem timestamp de criação
        if (!isset($_SESSION["_created"])) {
            $_SESSION["_created"] = time();
        }

        // Verificar se a sessão tem timestamp de última atividade
        if (!isset($_SESSION["_last_activity"])) {
            $_SESSION["_last_activity"] = time();
        }

        // Verificar timeout de inatividade (2 horas - mais permissivo)
        $timeout = 7200; // 2 horas
        if (time() - $_SESSION["_last_activity"] > $timeout) {
            self::destruirSessao();
            return false;
        }

        // Verificar tempo máximo de vida da sessão (8 horas - mais permissivo)
        $maxLifetime = 28800; // 8 horas
        if (time() - $_SESSION["_created"] > $maxLifetime) {
            self::destruirSessao();
            return false;
        }

        // Verificar se há dados de usuário válidos
        if (isset($_SESSION["usuario"])) {
            if (
                !isset($_SESSION["usuario"]["id"]) ||
                !isset($_SESSION["usuario"]["cpf"]) ||
                !isset($_SESSION["usuario"]["nome"])
            ) {
                self::destruirSessao();
                return false;
            }
        }

        // Atualizar timestamp de última atividade
        $_SESSION["_last_activity"] = time();

        // Regenerar ID da sessão periodicamente (a cada 30 minutos - menos frequente)
        if (!isset($_SESSION["_last_regenerate"])) {
            $_SESSION["_last_regenerate"] = time();
        }

        if (time() - $_SESSION["_last_regenerate"] > 1800) { // 30 minutos
            session_regenerate_id(true);
            $_SESSION["_last_regenerate"] = time();
        }

        return true;
    }

    /**
     * Sessão do painel administrativo (avaliadores) — chave `user`.
     */
    public static function estaLogadoDashboard(): bool
    {
        return isset($_SESSION['user']['id']) && is_numeric($_SESSION['user']['id']);
    }

    /**
     * Confere se o token de sessão do painel ainda é o registrado no banco (sessão única).
     * Sem efeito se não houver login no painel.
     */
    public static function validarVinculoSessaoDashboard(): bool
    {
        if (!self::estaLogadoDashboard()) {
            return true;
        }

        $uid = (int) $_SESSION['user']['id'];
        $sessToken = $_SESSION['user']['dashboard_sessao_token'] ?? null;
        if (!is_string($sessToken) || strlen($sessToken) !== 64) {
            return false;
        }

        try {
            $dbToken = UsuarioSessaoDashboard::obterTokenPorUsuario($uid);
        } catch (\Throwable $e) {
            error_log('validarVinculoSessaoDashboard: ' . $e->getMessage());
            return false;
        }

        if ($dbToken === null || !hash_equals($dbToken, $sessToken)) {
            return false;
        }

        $now = time();
        $last = (int) ($_SESSION['_dashboard_sessao_db_touch'] ?? 0);
        if ($now - $last >= 60) {
            try {
                UsuarioSessaoDashboard::tocarUltimoAcesso($uid);
            } catch (\Throwable $e) {
                error_log('tocarUltimoAcesso dashboard: ' . $e->getMessage());
            }
            $_SESSION['_dashboard_sessao_db_touch'] = $now;
        }

        return true;
    }

    /**
     * Garante token CSRF para requisições do painel (POST / fetch).
     */
    public static function ensureDashboardCsrfToken(): void
    {
        if (!self::estaLogadoDashboard()) {
            return;
        }
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Novo token após login (mitiga fixation).
     */
    public static function regenerateDashboardCsrfToken(): void
    {
        if (!self::estaLogadoDashboard()) {
            return;
        }
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public static function obterTokenCsrfDashboard(): ?string
    {
        self::ensureDashboardCsrfToken();
        $t = $_SESSION['csrf_token'] ?? null;
        return is_string($t) && $t !== '' ? $t : null;
    }

    /**
     * Valida header X-CSRF-Token ou campo POST csrf_token.
     */
    public static function validarDashboardCsrf(): bool
    {
        if (!self::estaLogadoDashboard()) {
            return false;
        }
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $post = $_POST['csrf_token'] ?? '';
        $fromRequest = is_string($header) && $header !== '' ? $header : (is_string($post) ? $post : '');
        $stored = $_SESSION['csrf_token'] ?? '';
        if ($fromRequest === '' || $stored === '' || !is_string($stored)) {
            return false;
        }
        return hash_equals($stored, $fromRequest);
    }

    /**
     * Destrói a sessão de forma segura
     */
    public static function destruirSessao()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Limpar todas as variáveis de sessão
            $_SESSION = array();

            // Deletar o cookie de sessão
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    "",
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            // Destruir a sessão
            session_destroy();
        }
    }

    /**
     * Criar login seguro
     */
    public static function criarLoginSeguro($candidato)
    {
        // Regenerar ID da sessão por segurança
        session_regenerate_id(true);

        $_SESSION["usuario"] = [
            "id" => $candidato["id"],
            "nome" => $candidato["nome"],
            "cpf" => $candidato["cpf"],
        ];

        $_SESSION["_created"] = time();
        $_SESSION["_last_activity"] = time();
        $_SESSION["_last_regenerate"] = time();
        $_SESSION["_login_time"] = time();
        $_SESSION["_user_agent"] = $_SERVER["HTTP_USER_AGENT"] ?? "";
        $_SESSION["_ip_address"] = $_SERVER["REMOTE_ADDR"] ?? "";
    }

    /**
     * Verificar se usuário está logado
     */
    public static function estaLogadoUsuario()
    {
        if (isset($_SESSION["usuario"])) {
            return self::validarSessaoUsuario();
        }
        return false;
    }

    public static function estaLogadoAdmin()
    {
        if (isset($_SESSION["admin_logged_in"]) && $_SESSION["admin_logged_in"] === true) {
            return self::validarSessaoAdmin();
        }
        return false;
    }

    public static function estaLogado()
    {
        return self::estaLogadoUsuario() || self::estaLogadoAdmin();
    }

    /**
     * Verificar integridade da sessão
     */
    public static function verificarIntegridade()
    {
        // Verificar User-Agent (desabilitado para desenvolvimento)
        // if (isset($_SESSION["_user_agent"])) {
        //     $currentUserAgent = $_SERVER["HTTP_USER_AGENT"] ?? "";
        //     if ($_SESSION["_user_agent"] !== $currentUserAgent) {
        //         self::destruirSessao();
        //         return false;
        //     }
        // }

        // Verificar IP (opcional - pode causar problemas com proxies)
        // if (isset($_SESSION["_ip_address"])) {
        //     $currentIP = $_SERVER["REMOTE_ADDR"] ?? "";
        //     if ($_SESSION["_ip_address"] !== $currentIP) {
        //         self::destruirSessao();
        //         return false;
        //     }
        // }

        return true;
    }

    /**
     * Valida se a sessão do ADMIN é válida e segura
     */ public static function validarSessaoAdmin()
    {
        if (session_status() === PHP_SESSION_NONE || !isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
            return false;
        }

        // Lógica de validação de sessão para admin (similar a validarSessao, mas focada em admin)
        if (!isset($_SESSION["_admin_created"])) {
            $_SESSION["_admin_created"] = time();
        }

        if (!isset($_SESSION["_admin_last_activity"])) {
            $_SESSION["_admin_last_activity"] = time();
        }

        $timeout = 7200; // 2 horas
        if (time() - $_SESSION["_admin_last_activity"] > $timeout) {
            self::destruirSessaoAdmin();
            return false;
        }

        $maxLifetime = 28800; // 8 horas
        if (time() - $_SESSION["_admin_created"] > $maxLifetime) {
            self::destruirSessaoAdmin();
            return false;
        }

        $_SESSION["_admin_last_activity"] = time();

        if (!isset($_SESSION["_admin_last_regenerate"])) {
            $_SESSION["_admin_last_regenerate"] = time();
        }

        if (time() - $_SESSION["_admin_last_regenerate"] > 1800) { // 30 minutos
            session_regenerate_id(true);
            $_SESSION["_admin_last_regenerate"] = time();
        }

        return true;
    }

    /**
     * Valida se a sessão do USUÁRIO (candidato) é válida e segura
     */
    public static function validarSessaoUsuario()
    {
        if (session_status() === PHP_SESSION_NONE || !isset($_SESSION["usuario"])) {
            return false;
        }

        // Lógica de validação de sessão para usuário (similar a validarSessao, mas focada em usuário)
        if (!isset($_SESSION["_user_created"])) {
            $_SESSION["_user_created"] = time();
        }

        if (!isset($_SESSION["_user_last_activity"])) {
            $_SESSION["_user_last_activity"] = time();
        }

        $timeout = 7200; // 2 horas
        if (time() - $_SESSION["_user_last_activity"] > $timeout) {
            self::destruirSessaoUsuario();
            return false;
        }

        $maxLifetime = 28800; // 8 horas
        if (time() - $_SESSION["_user_created"] > $maxLifetime) {
            self::destruirSessaoUsuario();
            return false;
        }

        $_SESSION["_user_last_activity"] = time();

        if (!isset($_SESSION["_user_last_regenerate"])) {
            $_SESSION["_user_last_regenerate"] = time();
        }

        if (time() - $_SESSION["_user_last_regenerate"] > 1800) { // 30 minutos
            session_regenerate_id(true);
            $_SESSION["_user_last_regenerate"] = time();
        }

        return true;
    }

    /**
     * Criar login seguro para ADMIN
     */
    public static function criarLoginSeguroAdmin($admin)
    {
        // Garante que a sessão esteja iniciada e configurada
        self::iniciarSessao();

        session_regenerate_id(true);

        $_SESSION["admin_logged_in"] = true;
        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_nome"] = $admin["nome"];
        $_SESSION["admin_email"] = $admin["email"];

        $_SESSION["_admin_created"] = time();
        $_SESSION["_admin_last_activity"] = time();
        $_SESSION["_admin_last_regenerate"] = time();
        $_SESSION["_admin_login_time"] = time();
        $_SESSION["_admin_user_agent"] = $_SERVER["HTTP_USER_AGENT"] ?? "";
        $_SESSION["_admin_ip_address"] = $_SERVER["REMOTE_ADDR"] ?? "";
    }

    /**
     * Criar login seguro para USUÁRIO (candidato)
     */
    public static function criarLoginSeguroUsuario($candidato)
    {
        // Garante que a sessão esteja iniciada e configurada
        self::iniciarSessao();

        session_regenerate_id(true);

        $_SESSION["usuario"] = [
            "id" => $candidato["id"],
            "nome" => $candidato["nome"],
            "cpf" => $candidato["cpf"],
        ];

        $_SESSION["_user_created"] = time();
        $_SESSION["_user_last_activity"] = time();
        $_SESSION["_user_last_regenerate"] = time();
        $_SESSION["_user_login_time"] = time();
        $_SESSION["_user_agent"] = $_SERVER["HTTP_USER_AGENT"] ?? "";
        $_SESSION["_user_ip_address"] = $_SERVER["REMOTE_ADDR"] ?? "";
    }

    /**
     * Destrói a sessão do ADMIN de forma segura
     */
    public static function destruirSessaoAdmin()
    {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION["admin_logged_in"])) {
            unset($_SESSION["admin_logged_in"]);
            unset($_SESSION["admin_id"]);
            unset($_SESSION["admin_nome"]);
            unset($_SESSION["admin_email"]);
            unset($_SESSION["_admin_created"]);
            unset($_SESSION["_admin_last_activity"]);
            unset($_SESSION["_admin_last_regenerate"]);
            unset($_SESSION["_admin_login_time"]);
            unset($_SESSION["_admin_user_agent"]);
            unset($_SESSION["_admin_ip_address"]);

            // Se não houver mais sessões ativas (admin ou usuário), destrói completamente
            if (!isset($_SESSION["usuario"])) {
                self::destruirSessao();
            }
        }
    }

    /**
     * Destrói a sessão do USUÁRIO (candidato) de forma segura
     */
    public static function destruirSessaoUsuario()
    {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION["usuario"])) {
            unset($_SESSION["usuario"]);
            unset($_SESSION["_user_created"]);
            unset($_SESSION["_user_last_activity"]);
            unset($_SESSION["_user_last_regenerate"]);
            unset($_SESSION["_user_login_time"]);
            unset($_SESSION["_user_agent"]);
            unset($_SESSION["_user_ip_address"]);

            // Se não houver mais sessões ativas (admin ou usuário), destrói completamente
            if (!isset($_SESSION["admin_logged_in"])) {
                self::destruirSessao();
            }
        }
    }
}

// function isValidaCPF($cpf)
// {
//     // Remove caracteres não numéricos
//     $cpf = preg_replace("/[^0-9]/is", "", $cpf);

//     // Verifica se foi informado todos os digitos corretamente
//     if (strlen($cpf) != 11) {
//         return false;
//     }

//     // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
//     if (preg_match("/(\d)\1{10}/", $cpf)) {
//         return false;
//     }

//     // Faz o calculo para validar o CPF
//     for ($t = 9; $t < 11; $t++) {
//         for ($d = 0, $c = 0; $c < $t; $c++) {
//             $d += $cpf[$c] * (($t + 1) - $c);
//         }
//         $d = ((10 * $d) % 11) % 10;
//         if ($cpf[$c] != $d) {
//             return false;
//         }
//     }
//     return true;
// }