<?php

namespace App\helpers;

/**
 * Lista branca em config/branding-documentos.json: só entradas com nome e tipo_documento
 * válidos entram no mapa; o resto da aplicação não exibe o documento.
 */
final class BrandingDocumentos
{
    /** @var list<array{nome: string, tipo: string}>|null */
    private static ?array $entradas = null;

    /** @var array<string, string>|null nome => titulo|requisito */
    private static ?array $mapa = null;

    private static function carregar(): void
    {
        if (self::$entradas !== null) {
            return;
        }

        self::$entradas = [];
        self::$mapa = [];

        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'branding-documentos.json';
        if (!is_readable($path)) {
            return;
        }

        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return;
        }

        foreach ($data as $row) {
            if (!is_array($row)) {
                continue;
            }
            if (!isset($row['nome'], $row['tipo_documento'])) {
                continue;
            }
            $nome = trim((string) $row['nome']);
            $tipoRaw = trim((string) $row['tipo_documento']);
            if ($nome === '' || $tipoRaw === '') {
                continue;
            }
            $tipo = strtolower($tipoRaw);
            if ($tipo !== 'titulo' && $tipo !== 'requisito') {
                continue;
            }
            self::$entradas[] = ['nome' => $nome, 'tipo' => $tipo];
            self::$mapa[$nome] = $tipo;
        }
    }

    public static function registrado(string $nome): bool
    {
        self::carregar();

        return isset(self::$mapa[$nome]);
    }

    public static function tipo(string $nome): ?string
    {
        self::carregar();

        return self::$mapa[$nome] ?? null;
    }

    public static function ehRequisito(string $nome): bool
    {
        return self::tipo($nome) === 'requisito';
    }

    public static function ehTitulo(string $nome): bool
    {
        return self::tipo($nome) === 'titulo';
    }

    /**
     * Nomes com o tipo indicado, na ordem do JSON.
     *
     * @return list<string>
     */
    public static function nomesPorTipo(string $tipoAlvo): array
    {
        self::carregar();
        $tipoAlvo = strtolower($tipoAlvo);
        $out = [];
        foreach (self::$entradas ?? [] as $e) {
            if ($e['tipo'] === $tipoAlvo) {
                $out[] = $e['nome'];
            }
        }

        return $out;
    }
}
