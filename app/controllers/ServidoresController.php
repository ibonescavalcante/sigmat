<?php

namespace App\controllers;

use App\core\Controller;
use App\models\Servidor;
use PDOException;

class ServidoresController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $busca = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
        $servidores = Servidor::listar($busca !== '' ? $busca : null);

        $this->view('servidores/page', [
            'servidores'        => $servidores,
            'busca'             => $busca,
            'tipos_form'        => Servidor::TIPOS_LABELS,
            'situacoes_form'    => Servidor::SITUACOES_LABELS,
            'total_servidores'  => count($servidores),
        ]);
    }

    public function obter($id)
    {
        header('Content-Type: application/json; charset=utf-8');

        $servidor = Servidor::buscarPorId((int) $id);
        if ($servidor === null) {
            http_response_code(404);
            echo json_encode(['erro' => 'Servidor não encontrado.']);
            exit;
        }

        echo json_encode(['ok' => true, 'servidor' => $servidor]);
        exit;
    }

    public function criar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = $this->lerInput();
        $erro = $this->validarDadosServidor($input);
        if ($erro !== null) {
            http_response_code(422);
            echo json_encode(['erro' => $erro]);
            exit;
        }

        if (Servidor::cpfExiste($input['cpf'])) {
            http_response_code(422);
            echo json_encode(['erro' => 'Este CPF já está cadastrado.']);
            exit;
        }

        if (Servidor::matriculaExiste($input['matricula'])) {
            http_response_code(422);
            echo json_encode(['erro' => 'Esta matrícula já está cadastrada.']);
            exit;
        }

        $erroFoto = $this->aplicarUploadFoto($input, (string) $input['matricula']);
        if ($erroFoto !== null) {
            http_response_code(422);
            echo json_encode(['erro' => $erroFoto]);
            exit;
        }

        try {
            $id = Servidor::criar($input);
        } catch (PDOException $e) {
            $this->responderErroPdo($e, 'criar');
        }

        if ($id === false) {
            http_response_code(422);
            echo json_encode(['erro' => 'Não foi possível validar os dados do servidor.']);
            exit;
        }

        echo json_encode(['ok' => true, 'id' => $id, 'mensagem' => 'Servidor cadastrado com sucesso.']);
        exit;
    }

    public function atualizar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = $this->lerInput();

        $id = isset($input['id']) ? (int) $input['id'] : 0;
        if ($id < 1) {
            http_response_code(422);
            echo json_encode(['erro' => 'Identificador do servidor inválido.']);
            exit;
        }

        $servidorAtual = Servidor::buscarPorId($id);
        if ($servidorAtual === null) {
            http_response_code(404);
            echo json_encode(['erro' => 'Servidor não encontrado.']);
            exit;
        }

        $erro = $this->validarDadosServidor($input);
        if ($erro !== null) {
            http_response_code(422);
            echo json_encode(['erro' => $erro]);
            exit;
        }

        if (Servidor::cpfExiste($input['cpf'], $id)) {
            http_response_code(422);
            echo json_encode(['erro' => 'Este CPF já está cadastrado.']);
            exit;
        }

        if (Servidor::matriculaExiste($input['matricula'], $id)) {
            http_response_code(422);
            echo json_encode(['erro' => 'Esta matrícula já está cadastrada.']);
            exit;
        }

        $input['foto_url'] = $servidorAtual['foto_url'] ?? null;
        $erroFoto = $this->aplicarUploadFoto($input, (string) ($input['matricula'] ?? $id));
        if ($erroFoto !== null) {
            http_response_code(422);
            echo json_encode(['erro' => $erroFoto]);
            exit;
        }

        try {
            $ok = Servidor::atualizar($id, $input);
        } catch (PDOException $e) {
            $this->responderErroPdo($e, 'atualizar');
        }

        if (!$ok) {
            http_response_code(422);
            echo json_encode(['erro' => 'Não foi possível atualizar o servidor.']);
            exit;
        }

        echo json_encode(['ok' => true, 'mensagem' => 'Servidor atualizado com sucesso.']);
        exit;
    }

    public function excluir()
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = $this->lerInputJson();

        $id = isset($input['id']) ? (int) $input['id'] : 0;
        if ($id < 1) {
            http_response_code(422);
            echo json_encode(['erro' => 'Identificador do servidor inválido.']);
            exit;
        }

        if (Servidor::buscarPorId($id) === null) {
            http_response_code(404);
            echo json_encode(['erro' => 'Servidor não encontrado.']);
            exit;
        }

        if (!Servidor::excluir($id)) {
            http_response_code(500);
            echo json_encode(['erro' => 'Não foi possível excluir o servidor.']);
            exit;
        }

        echo json_encode(['ok' => true, 'mensagem' => 'Servidor excluído com sucesso.']);
        exit;
    }

    /**
     * @return array<string, mixed>
     */
    private function lerInput(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        return $this->lerInputJson();
    }

    /**
     * @return array<string, mixed>
     */
    private function lerInputJson(): array
    {
        $raw = file_get_contents('php://input');
        $input = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($input)) {
            $input = $_POST;
        }

        return is_array($input) ? $input : [];
    }

    /**
     * @param array<string, mixed> $input
     */
    private function aplicarUploadFoto(array &$input, string $identificador): ?string
    {
        if (!isset($_FILES['foto']) || !is_array($_FILES['foto'])) {
            return null;
        }

        $erro = (int) ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($erro === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $fotoUrl = Servidor::salvarFoto($_FILES['foto'], $identificador);
        if ($fotoUrl === false) {
            return Servidor::mensagemErroFoto();
        }

        $input['foto_url'] = $fotoUrl;
        return null;
    }

    /**
     * @param array<string, mixed> $input
     */
    private function validarDadosServidor(array $input): ?string
    {
        $nome = isset($input['nome']) ? trim((string) $input['nome']) : '';
        $matricula = isset($input['matricula']) ? trim((string) $input['matricula']) : '';
        $cpf = isset($input['cpf']) ? trim((string) $input['cpf']) : '';
        $tipo = isset($input['tipo']) ? strtoupper(trim((string) $input['tipo'])) : '';

        if ($nome === '' || mb_strlen($nome) < 2) {
            return 'Informe o nome completo (mínimo 2 caracteres).';
        }
        if ($matricula === '') {
            return 'Informe a matrícula.';
        }
        if ($cpf === '' || !isValidaCPF($cpf)) {
            return 'CPF inválido.';
        }
        if (!in_array($tipo, Servidor::TIPOS_VALIDOS, true)) {
            return 'Tipo de servidor inválido.';
        }

        return null;
    }

    private function responderErroPdo(PDOException $e, string $acao): void
    {
        error_log($acao . ' servidor PDO: ' . $e->getMessage());
        $sqlState = $e->errorInfo[0] ?? '';
        $msg = strtolower($e->getMessage());
        if ($sqlState === '23505' || str_contains($msg, '23505')) {
            http_response_code(422);
            echo json_encode(['erro' => Servidor::mensagemErroDuplicacao($e)]);
            exit;
        }
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao salvar no banco de dados.']);
        exit;
    }
}
