<?php

namespace App\controllers;


use App\models\Usuario;

use App\core\Controller;
class ApiController extends Controller
{
  

    public function alterarSenha()
    {
        header("Content-Type: application/json");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Método não permitido
            echo json_encode(["sucesso" => false, "mensagem" => "Método não permitido. Use POST."]);
            return;
        }

        $senhaAtual = $_POST['senha_atual'] ?? null;
        $novaSenha = $_POST['nova_senha'] ?? null;
        $usuarioId = $_SESSION['user']['id'] ?? null;


        if (!$senhaAtual || !$novaSenha || !$usuarioId) {
            http_response_code(400);
            echo json_encode(["sucesso" => false, "mensagem" => "Todos os campos são obrigatórios."]);
            return;
        }

        // Verificar se a senha atual está correta
        $resultado = Usuario::verificarSenha($usuarioId, $senhaAtual);

        if (!$resultado) {
            http_response_code(401);
            echo json_encode(["sucesso" => false, "mensagem" => "A senha atual está incorreta."]);
            return;
        }

        // Criptografar a nova senha
        $novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
  
        if (Usuario::atualizarSenha($usuarioId, $novaSenhaHash)) {
            echo json_encode(["sucesso" => true, "mensagem" => "Senha alterada com sucesso!"]);
        } else {
            http_response_code(500);
            echo json_encode(["sucesso" => false, "mensagem" => "Ocorreu um erro ao alterar a senha. Tente novamente."]);
        }
    }




}
