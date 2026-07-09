<?php

namespace App\helpers;

/**
 * Nome, subtítulo e ícone do painel a partir de config/branding.json.
 */
final class Branding
{
    private static ?array $cache = null;

    public static function get(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $defaults = [
            'nome_curto' => 'SIGLA',
            'subtitulo' => 'Descrição do Sistema',
            'titulo_documento' => 'SIGLA — Descrição do Sistema',
            'icone_classes' => 'fas fa-file-alt',
        ];

        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'branding.json';
        if (!is_readable($path)) {
            self::$cache = $defaults;
            return self::$cache;
        }

        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            self::$cache = $defaults;
            return self::$cache;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            self::$cache = $defaults;
            return self::$cache;
        }

        $merged = array_merge($defaults, array_intersect_key($data, $defaults));
        $merged['icone_classes'] = self::sanitizeIconClasses((string) $merged['icone_classes']);

        self::$cache = $merged;
        return self::$cache;
    }

    private static function sanitizeIconClasses(string $classes): string
    {
        $clean = preg_replace('/[^a-zA-Z0-9 _-]/', '', trim($classes));
        return $clean !== '' ? $clean : 'fas fa-file-alt';
    }
}
