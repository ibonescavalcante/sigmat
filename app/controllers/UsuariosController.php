<?php

namespace App\controllers;

use App\core\Controller;
use App\middleware\SessionSecurity;
use App\models\Usuario;

class UsuariosController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $perfisForm = [
            ['value' => 'comissao', 'label' => 'Comissão avaliadora'],
            ['value' => 'administrador', 'label' => 'Administrador'],
            ['value' => 'visualizador', 'label' => 'Visualizador'],
        ];
        $perfilLabels = [];
        foreach ($perfisForm as $p) {
            $perfilLabels[$p['value']] = $p['label'];
        }
        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        $podeGerirUsuarios = $uid > 0 && Usuario::perfilPorId($uid) === 'administrador';
        if (!$podeGerirUsuarios) {
            $_SESSION['erro'] = 'Acesso negado. Apenas administradores podem aceder a Configurações.';
            header('Location: /');
            exit;
        }

        $this->view('usuarios/page', [
            'usuarios'            => Usuario::listarParaPainel(),
            'perfis_form'         => $perfisForm,
            'perfil_labels'       => $perfilLabels,
            'pode_gerir_usuarios' => $podeGerirUsuarios,
        ]);
    }

    public function criar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->garantirAdministradorPainelApi();

        $raw = file_get_contents('php://input');
        $input = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($input)) {
            $input = $_POST;
        }

        $nome = isset($input['nome']) ? trim((string) $input['nome']) : '';
        $email = isset($input['email']) ? strtolower(trim((string) $input['email'])) : '';
        $telefone = isset($input['telefone']) ? trim((string) $input['telefone']) : '';
        $perfil = isset($input['perfil']) ? strtolower(trim((string) $input['perfil'])) : '';
        $senha = isset($input['senha']) ? (string) $input['senha'] : '';
        $senha2 = isset($input['senha_confirmacao']) ? (string) $input['senha_confirmacao'] : '';
        $ativo = !isset($input['ativo']) || $input['ativo'] === true || $input['ativo'] === '1' || $input['ativo'] === 'on';

        if ($nome === '' || mb_strlen($nome) < 2) {
            http_response_code(422);
            echo json_encode(['erro' => 'Informe o nome completo (mínimo 2 caracteres).']);
            exit;
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['erro' => 'E-mail inválido.']);
            exit;
        }

        if (!in_array($perfil, Usuario::PERFIS_VALIDOS, true)) {
            http_response_code(422);
            echo json_encode(['erro' => 'Perfil inválido.']);
            exit;
        }

        if (strlen($senha) < 8) {
            http_response_code(422);
            echo json_encode(['erro' => 'A senha deve ter no mínimo 8 caracteres.']);
            exit;
        }

        if (!hash_equals($senha, $senha2)) {
            http_response_code(422);
            echo json_encode(['erro' => 'A confirmação da senha não confere.']);
            exit;
        }

        if (Usuario::usuarioJaCadastrado($email)) {
            http_response_code(422);
            echo json_encode(['erro' => 'Este e-mail já está cadastrado.']);
            exit;
        }

        try {
            $id = Usuario::criarUsuarioPainel([
                'nome'     => $nome,
                'email'    => $email,
                'telefone' => $telefone,
                'perfil'   => $perfil,
                'senha'    => $senha,
                'ativo'    => $ativo,
            ]);
        } catch (\PDOException $e) {
            error_log('criar_usuario PDO: ' . $e->getMessage());
            $sqlState = $e->errorInfo[0] ?? '';
            $msg = strtolower($e->getMessage());
            if ($sqlState === '23505' || str_contains($msg, '23505')) {
                http_response_code(422);
                echo json_encode(['erro' => Usuario::mensagemErroDuplicacao($e)]);
                exit;
            }
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao salvar no banco de dados. Verifique os dados ou o tipo perfil no PostgreSQL.']);
            exit;
        } catch (\Throwable $e) {
            error_log('criar_usuario: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno ao criar usuário.']);
            exit;
        }

        if ($id === false) {
            http_response_code(500);
            echo json_encode(['erro' => 'Não foi possível criar o usuário.']);
            exit;
        }

        echo json_encode(['ok' => true, 'id' => $id, 'mensagem' => 'Usuário criado com sucesso.']);
        exit;
    }

    public function atualizar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->garantirAdministradorPainelApi();

        $raw = file_get_contents('php://input');
        $input = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($input)) {
            $input = $_POST;
        }

        $id = isset($input['id']) ? (int) $input['id'] : 0;
        if ($id < 1) {
            http_response_code(422);
            echo json_encode(['erro' => 'Identificador do utilizador inválido.']);
            exit;
        }

        if (Usuario::buscarParaEdicaoPainel($id) === null) {
            http_response_code(404);
            echo json_encode(['erro' => 'Utilizador não encontrado.']);
            exit;
        }

        $nome = isset($input['nome']) ? trim((string) $input['nome']) : '';
        $email = isset($input['email']) ? strtolower(trim((string) $input['email'])) : '';
        $telefone = isset($input['telefone']) ? trim((string) $input['telefone']) : '';
        $perfil = isset($input['perfil']) ? strtolower(trim((string) $input['perfil'])) : '';
        $senha = isset($input['senha']) ? (string) $input['senha'] : '';
        $senha2 = isset($input['senha_confirmacao']) ? (string) $input['senha_confirmacao'] : '';
        if (!array_key_exists('ativo', $input)) {
            $ativo = true;
        } else {
            $av = $input['ativo'];
            $ativo = $av === true || $av === 1 || $av === '1' || $av === 'on' || $av === 'true';
        }

        if ($nome === '' || mb_strlen($nome) < 2) {
            http_response_code(422);
            echo json_encode(['erro' => 'Informe o nome completo (mínimo 2 caracteres).']);
            exit;
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['erro' => 'E-mail inválido.']);
            exit;
        }

        if (!in_array($perfil, Usuario::PERFIS_VALIDOS, true)) {
            http_response_code(422);
            echo json_encode(['erro' => 'Perfil inválido.']);
            exit;
        }

        if ($senha !== '' || $senha2 !== '') {
            if (strlen($senha) < 8) {
                http_response_code(422);
                echo json_encode(['erro' => 'A senha deve ter no mínimo 8 caracteres.']);
                exit;
            }
            if (!hash_equals($senha, $senha2)) {
                http_response_code(422);
                echo json_encode(['erro' => 'A confirmação da senha não confere.']);
                exit;
            }
        }

        if (Usuario::usuarioJaCadastrado($email, $id)) {
            http_response_code(422);
            echo json_encode(['erro' => 'Este e-mail já está cadastrado.']);
            exit;
        }

        try {
            $ok = Usuario::atualizarUsuarioPainel($id, [
                'nome'              => $nome,
                'email'             => $email,
                'telefone'          => $telefone,
                'perfil'            => $perfil,
                'ativo'             => $ativo,
                'senha'             => $senha,
                'senha_confirmacao' => $senha2,
            ]);
        } catch (\PDOException $e) {
            error_log('atualizar_usuario PDO: ' . $e->getMessage());
            $sqlState = $e->errorInfo[0] ?? '';
            $msg = strtolower($e->getMessage());
            if ($sqlState === '23505' || str_contains($msg, '23505')) {
                http_response_code(422);
                echo json_encode(['erro' => Usuario::mensagemErroDuplicacao($e)]);
                exit;
            }
            if ($sqlState === '22P02' || str_contains($msg, 'invalid input value for enum')
                || str_contains($msg, 'invalid input syntax for type')) {
                http_response_code(422);
                echo json_encode(['erro' => 'Valor inválido para um campo na base de dados (ex.: perfil ou formato de dado).']);
                exit;
            }
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao salvar no banco de dados.']);
            exit;
        } catch (\Throwable $e) {
            error_log('atualizar_usuario: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno ao atualizar utilizador.']);
            exit;
        }

        if (!$ok) {
            http_response_code(500);
            echo json_encode(['erro' => 'Não foi possível atualizar o utilizador.']);
            exit;
        }

        echo json_encode(['ok' => true, 'mensagem' => 'Utilizador atualizado com sucesso.']);
        exit;
    }

    private function garantirAdministradorPainelApi(): void
    {
        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        if ($uid < 1 || Usuario::perfilPorId($uid) !== 'administrador') {
            http_response_code(403);
            echo json_encode(['erro' => 'Sem permissão.']);
            exit;
        }
    }
}
