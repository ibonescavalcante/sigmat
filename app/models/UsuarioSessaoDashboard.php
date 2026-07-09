<?php

namespace App\models;

use App\core\Database;
use PDO;

class UsuarioSessaoDashboard
{
    public static function registrarOuAtualizar(int $usuarioId, string $token): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(            
            'INSERT INTO sigmat.usuario_sessao(usuario_id, "token", criado_em, ultimo_acesso)
             VALUES (:usuario_id, :token, NOW(), NOW())
             ON CONFLICT (usuario_id) DO UPDATE SET
                 token = EXCLUDED.token,
                 criado_em = NOW(),
                 ultimo_acesso = NOW()'
        );

  try {
    $stmt->execute([
            'usuario_id' => $usuarioId,
            'token'      => $token,
        ]);
  } catch (\Throwable $th) {
            error_log('UsuarioSessaoDashboard::registrarOuAtualizar: ' . $th->getMessage());
            throw $th;
        }
       
          

    }

    public static function obterTokenPorUsuario(int $usuarioId): ?string
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT token FROM sigmat.usuario_sessao WHERE usuario_id = :id'
        );
        $stmt->execute(['id' => $usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !isset($row['token']) || !is_string($row['token'])) {
            return null;
        }
        return $row['token'];
    }

    public static function removerPorUsuario(int $usuarioId): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'DELETE FROM sigmat.usuario_sessao WHERE usuario_id = :id'
        );
        $stmt->execute(['id' => $usuarioId]);
    }

    public static function tocarUltimoAcesso(int $usuarioId): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'UPDATE sigmat.usuario_sessao SET ultimo_acesso = NOW() WHERE usuario_id = :id'
        );
        $stmt->execute(['id' => $usuarioId]);
    }
}
