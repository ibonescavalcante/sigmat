<?php

namespace App\models;

use App\core\Database;
use PDO;
use PDOException;

class Servidor
{
    public const TIPOS_VALIDOS = ['GUARDA', 'DMTT'];

    public const SITUACOES_VALIDAS = ['ativo', 'inativo', 'afastado'];

    public const TIPOS_LABELS = [
        'GUARDA' => 'Guarda Municipal',
        'DMTT'   => 'Agente Municipal de Trânsito e Transporte (DMTT)',
    ];

    public const SITUACOES_LABELS = [
        'ativo'    => 'Ativo',
        'inativo'  => 'Inativo',
        'afastado' => 'Afastado',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public static function listar(?string $busca = null, ?string $tipo = null): array
    {
        $db = Database::getInstance();
        $sql = 'SELECT id, tipo::text AS tipo, nome, matricula, cpf, rg, naturalidade,
                       data_nascimento, filiacao_pai, filiacao_mae, cargo,
                       data_admissao, data_emissao, data_validade, fe_publica,
                       porte_arma, tipo_sanguineo, foto_url, assinatura_url,
                       situacao::text AS situacao, ativo, criado_em
                FROM sigmat.servidor
                WHERE ativo = TRUE';
        $params = [];

        $busca = $busca !== null ? trim($busca) : '';
        if ($busca !== '') {
            $sql .= ' AND (
                nome ILIKE :busca
                OR matricula ILIKE :busca
                OR cpf ILIKE :busca
            )';
            $params['busca'] = '%' . $busca . '%';
        }

        $tipo = $tipo !== null ? strtoupper(trim($tipo)) : '';
        if ($tipo !== '' && in_array($tipo, self::TIPOS_VALIDOS, true)) {
            $sql .= ' AND tipo = CAST(:tipo AS sigmat.tiposervidor)';
            $params['tipo'] = $tipo;
        }

        $sql .= ' ORDER BY nome ASC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!is_array($rows)) {
            return [];
        }

        return array_map(static fn(array $row): array => self::normalizarRegistro($row), $rows);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function listarParaCarteirinha(?string $busca = null, ?string $tipo = null): array
    {
        return array_map(
            static fn(array $row): array => self::mapearParaCarteirinha($row),
            self::listar($busca, $tipo)
        );
    }

    /**
     * @param array<string, mixed> $servidor
     * @return array<string, mixed>
     */
    public static function mapearParaCarteirinha(array $servidor): array
    {
        $filiacaoPai = trim((string) ($servidor['filiacao_pai'] ?? ''));
        $filiacaoMae = trim((string) ($servidor['filiacao_mae'] ?? ''));

        if ($filiacaoPai !== '' && !str_starts_with(strtoupper($filiacaoPai), 'PAI:')) {
            $filiacaoPai = 'PAI:' . $filiacaoPai;
        }
        if ($filiacaoMae !== '' && !str_starts_with(strtoupper($filiacaoMae), 'MAE:')) {
            $filiacaoMae = 'MAE:' . $filiacaoMae;
        }

        $emissao = self::formatarDataBr($servidor['data_emissao'] ?? null);
        if ($emissao === '') {
            $emissao = date('d/m/Y');
        }

        return [
            'id'             => (int) ($servidor['id'] ?? 0),
            'nome'           => (string) ($servidor['nome'] ?? ''),
            'cargo'          => (string) ($servidor['cargo'] ?? ''),
            'matricula'      => (string) ($servidor['matricula'] ?? ''),
            'cpf'            => (string) ($servidor['cpf'] ?? ''),
            'rg'             => (string) ($servidor['rg'] ?? ''),
            'naturalidade'   => (string) ($servidor['naturalidade'] ?? ''),
            'tipo_sanguineo' => (string) ($servidor['tipo_sanguineo'] ?? ''),
            'emissao'        => $emissao,
            'validade'       => ($servidor['data_validade'] ?? null) === null
                ? 'INDETERMINADO'
                : self::formatarDataBr($servidor['data_validade']),
            'nascimento'     => self::formatarDataBr($servidor['data_nascimento'] ?? null),
            'admissao'       => self::formatarDataBr($servidor['data_admissao'] ?? null),
            'filiacaop'      => $filiacaoPai,
            'filiacaom'      => $filiacaoMae,
            'fepublica'      => (string) ($servidor['fe_publica'] ?? ''),
            'porte'          => strtoupper(trim((string) ($servidor['porte_arma'] ?? ''))),
            'foto'           => self::urlMidia($servidor['foto_url'] ?? null, '/assets/img/1.png'),
            'assinatura'     => self::urlMidia($servidor['assinatura_url'] ?? null, ''),
            'tipo'           => (string) ($servidor['tipo'] ?? ''),
            'situacao'       => (string) ($servidor['situacao'] ?? ''),
        ];
    }

    private static function formatarDataBr(mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }
        $ts = strtotime((string) $valor);
        return $ts !== false ? date('d/m/Y', $ts) : '';
    }

    private static function urlMidia(?string $url, string $fallback): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return $fallback;
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
            return $url;
        }

        return '/' . ltrim($url, '/');
    }

    public static function contar(?string $busca = null): int
    {
        return count(self::listar($busca));
    }

    public static function cpfExiste(string $cpf, ?int $excetoId = null): bool
    {
        $cpfNorm = self::normalizarCpf($cpf);
        $db = Database::getInstance();

        if ($excetoId !== null) {
            $stmt = $db->prepare(
                'SELECT 1 FROM sigmat.servidor
                 WHERE regexp_replace(cpf, \'[^0-9]\', \'\', \'g\') = :cpf AND id <> :id
                 LIMIT 1'
            );
            $stmt->execute(['cpf' => $cpfNorm, 'id' => $excetoId]);
        } else {
            $stmt = $db->prepare(
                'SELECT 1 FROM sigmat.servidor
                 WHERE regexp_replace(cpf, \'[^0-9]\', \'\', \'g\') = :cpf
                 LIMIT 1'
            );
            $stmt->execute(['cpf' => $cpfNorm]);
        }

        return (bool) $stmt->fetchColumn();
    }

    public static function matriculaExiste(string $matricula, ?int $excetoId = null): bool
    {
        $matricula = trim($matricula);
        $db = Database::getInstance();

        if ($excetoId !== null) {
            $stmt = $db->prepare(
                'SELECT 1 FROM sigmat.servidor WHERE lower(trim(matricula)) = lower(trim(:matricula)) AND id <> :id LIMIT 1'
            );
            $stmt->execute(['matricula' => $matricula, 'id' => $excetoId]);
        } else {
            $stmt = $db->prepare(
                'SELECT 1 FROM sigmat.servidor WHERE lower(trim(matricula)) = lower(trim(:matricula)) LIMIT 1'
            );
            $stmt->execute(['matricula' => $matricula]);
        }

        return (bool) $stmt->fetchColumn();
    }

    /**
     * @param array<string, mixed> $dados
     * @return int|false
     */
    public static function criar(array $dados)
    {
        $normalizado = self::normalizarDadosEntrada($dados);
        if ($normalizado === null) {
            return false;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO sigmat.servidor (
                tipo, nome, matricula, cpf, rg, naturalidade, data_nascimento,
                filiacao_pai, filiacao_mae, cargo, data_admissao, data_emissao,
                data_validade, fe_publica, porte_arma, tipo_sanguineo, foto_url, situacao,
                ativo, criado_em, atualizado_em
            ) VALUES (
                CAST(:tipo AS sigmat.tiposervidor), :nome, :matricula, :cpf, :rg, :naturalidade, :data_nascimento,
                :filiacao_pai, :filiacao_mae, :cargo, :data_admissao, :data_emissao,
                :data_validade, :fe_publica, :porte_arma, :tipo_sanguineo, :foto_url, CAST(:situacao AS sigmat.situacao_servidor),
                TRUE, NOW(), NOW()
            ) RETURNING id'
        );

        $ok = $stmt->execute($normalizado);

        if (!$ok) {
            return false;
        }

        $id = $stmt->fetchColumn();
        return $id !== false ? (int) $id : false;
    }

    public static function buscarPorId(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT id, tipo::text AS tipo, nome, matricula, cpf, rg, naturalidade,
                    data_nascimento, filiacao_pai, filiacao_mae, cargo,
                    data_admissao, data_emissao, data_validade, fe_publica,
                    porte_arma, tipo_sanguineo, foto_url, assinatura_url,
                    situacao::text AS situacao, ativo, criado_em
             FROM sigmat.servidor
             WHERE id = :id AND ativo = TRUE'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? self::normalizarRegistro($row) : null;
    }

    /**
     * @param array<string, mixed> $dados
     */
    public static function atualizar(int $id, array $dados): bool
    {
        if ($id < 1 || self::buscarPorId($id) === null) {
            return false;
        }

        $normalizado = self::normalizarDadosEntrada($dados);
        if ($normalizado === null) {
            return false;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare(
            'UPDATE sigmat.servidor SET
                tipo = CAST(:tipo AS sigmat.tiposervidor),
                nome = :nome,
                matricula = :matricula,
                cpf = :cpf,
                rg = :rg,
                naturalidade = :naturalidade,
                data_nascimento = :data_nascimento,
                filiacao_pai = :filiacao_pai,
                filiacao_mae = :filiacao_mae,
                cargo = :cargo,
                data_admissao = :data_admissao,
                data_emissao = :data_emissao,
                data_validade = :data_validade,
                fe_publica = :fe_publica,
                porte_arma = :porte_arma,
                tipo_sanguineo = :tipo_sanguineo,
                foto_url = :foto_url,
                situacao = CAST(:situacao AS sigmat.situacao_servidor),
                atualizado_em = NOW()
             WHERE id = :id AND ativo = TRUE'
        );

        $params = $normalizado;
        $params['id'] = $id;

        return (bool) $stmt->execute($params);
    }

    public static function excluir(int $id): bool
    {
        if ($id < 1) {
            return false;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare(
            'UPDATE sigmat.servidor SET ativo = FALSE, situacao = CAST(:situacao AS sigmat.situacao_servidor), atualizado_em = NOW()
             WHERE id = :id AND ativo = TRUE'
        );

        return (bool) $stmt->execute(['id' => $id, 'situacao' => 'inativo']);
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>|null
     */
    private static function normalizarDadosEntrada(array $dados): ?array
    {
        $tipo = isset($dados['tipo']) ? strtoupper(trim((string) $dados['tipo'])) : '';
        $nome = isset($dados['nome']) ? trim((string) $dados['nome']) : '';
        $matricula = isset($dados['matricula']) ? trim((string) $dados['matricula']) : '';
        $cpf = isset($dados['cpf']) ? trim((string) $dados['cpf']) : '';
        $cargo = self::TIPOS_LABELS[$tipo] ?? '';
        $situacao = isset($dados['situacao']) ? strtolower(trim((string) $dados['situacao'])) : 'ativo';

        if (!in_array($tipo, self::TIPOS_VALIDOS, true)) {
            return null;
        }
        if ($nome === '' || mb_strlen($nome) < 2) {
            return null;
        }
        if ($matricula === '' || $cargo === '') {
            return null;
        }
        if (!isValidaCPF($cpf)) {
            return null;
        }
        if (!in_array($situacao, self::SITUACOES_VALIDAS, true)) {
            return null;
        }

        return [
            'tipo'            => $tipo,
            'nome'            => $nome,
            'matricula'       => $matricula,
            'cpf'             => $cpf,
            'cargo'           => $cargo,
            'situacao'        => $situacao,
            'porte_arma'      => self::nullableString($dados['porte_arma'] ?? null),
            'rg'              => self::nullableString($dados['rg'] ?? null),
            'naturalidade'    => self::nullableString($dados['naturalidade'] ?? null),
            'filiacao_pai'    => self::nullableString($dados['filiacao_pai'] ?? null),
            'filiacao_mae'    => self::nullableString($dados['filiacao_mae'] ?? null),
            'fe_publica'      => self::nullableString($dados['fe_publica'] ?? null),
            'tipo_sanguineo'  => self::nullableString($dados['tipo_sanguineo'] ?? null),
            'data_nascimento' => self::parseData($dados['data_nascimento'] ?? null),
            'data_admissao'   => self::parseData($dados['data_admissao'] ?? null),
            'data_emissao'    => self::parseData($dados['data_emissao'] ?? null),
            'data_validade'   => self::parseDataValidade($dados),
            'foto_url'        => self::nullableString($dados['foto_url'] ?? null),
        ];
    }

    /**
     * Salva foto do servidor em public/uploads/servidores.
     *
     * @param array<string, mixed> $file
     */
    public static function salvarFoto(array $file, string $identificador): string|false
    {
        $erro = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($erro === UPLOAD_ERR_NO_FILE) {
            return false;
        }
        if ($erro !== UPLOAD_ERR_OK) {
            return false;
        }

        $tmp = $file['tmp_name'] ?? '';
        if (!is_string($tmp) || $tmp === '' || !is_uploaded_file($tmp)) {
            return false;
        }

        $maxSize = 2 * 1024 * 1024;
        if ((int) ($file['size'] ?? 0) > $maxSize) {
            return false;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        if (!is_string($mime) || !isset($allowed[$mime])) {
            return false;
        }

        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'servidores';
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return false;
        }

        $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $identificador) ?: 'servidor';
        $nome = $slug . '_' . time() . '.' . $allowed[$mime];
        $destino = $dir . DIRECTORY_SEPARATOR . $nome;

        if (!move_uploaded_file($tmp, $destino)) {
            return false;
        }

        return '/uploads/servidores/' . $nome;
    }

    public static function mensagemErroFoto(): string
    {
        return 'Foto inválida. Envie JPG, PNG ou WEBP com até 2 MB.';
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function normalizarRegistro(array $row): array
    {
        $row['id'] = (int) ($row['id'] ?? 0);
        $row['tipo'] = strtoupper(trim((string) ($row['tipo'] ?? '')));
        $row['situacao'] = strtolower(trim((string) ($row['situacao'] ?? 'ativo')));
        $row['porte_arma'] = trim((string) ($row['porte_arma'] ?? ''));

        foreach (['data_nascimento', 'data_admissao', 'data_emissao', 'data_validade'] as $campo) {
            if (!empty($row[$campo])) {
                $ts = strtotime((string) $row[$campo]);
                $row[$campo] = $ts !== false ? date('Y-m-d', $ts) : null;
            } else {
                $row[$campo] = null;
            }
        }

        return $row;
    }

    public static function mensagemErroDuplicacao(PDOException $e): string
    {
        $msg = strtolower($e->getMessage());
        if (str_contains($msg, 'servidor_cpf_uk') || str_contains($msg, '(cpf)')) {
            return 'Este CPF já está cadastrado.';
        }
        if (str_contains($msg, 'servidor_matricula_uk') || str_contains($msg, '(matricula)')) {
            return 'Esta matrícula já está cadastrada.';
        }

        return 'Não foi possível gravar: valor duplicado na base de dados.';
    }

    private static function normalizarCpf(string $cpf): string
    {
        return preg_replace('/[^0-9]/', '', $cpf) ?? '';
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string) $value);
        return $s === '' ? null : $s;
    }

    private static function parseData(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $s = trim((string) $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return $s;
        }
        $ts = strtotime(str_replace('/', '-', $s));
        if ($ts === false) {
            return null;
        }

        return date('Y-m-d', $ts);
    }

    /**
     * @param array<string, mixed> $dados
     */
    private static function parseDataValidade(array $dados): ?string
    {
        $indeterminada = filter_var($dados['validade_indeterminada'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($indeterminada) {
            return null;
        }

        $valor = $dados['data_validade'] ?? null;
        if ($valor === null || $valor === '') {
            return null;
        }

        if (strtolower(trim((string) $valor)) === 'indeterminado') {
            return null;
        }

        return self::parseData($valor);
    }
}
