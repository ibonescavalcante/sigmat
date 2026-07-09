<?php

namespace App\helpers;

/**
 * Lista branca em config/branding-documentos.json: só entradas com nome e tipo_documento
 * válidos entram no mapa; o resto da aplicação não exibe o documento.
 */
final class BrandingServidores
{
    /** @var list<array{nome: string, tipo: string}>|null */
    private static ?array $entradas = null;

    /** @var array<string, string>|null nome => titulo|requisito */
    private static ?array $mapa = null;

    private static function carregar()
    {
        if (self::$entradas !== null) {
            return;
        }

        self::$entradas = [];
        self::$mapa = [];

        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'branding-mock-servidores.json';
        if (!is_readable($path)) {
            return;
        }

        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return [];
        }
   return $data;
    
    }
public static function todas(): array
    {
        return self::carregar();

        // return self::$entradas ?? [];
    }


   

}
