<?php

namespace App\controllers;

use App\core\Controller;
use App\models\Servidor;

class CarteirinhaController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $busca = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
        $tipo = isset($_GET['tipo']) ? strtoupper(trim((string) $_GET['tipo'])) : '';
        if ($tipo !== '' && !in_array($tipo, Servidor::TIPOS_VALIDOS, true)) {
            $tipo = '';
        }

        $buscaFiltro = $busca !== '' ? $busca : null;
        $tipoFiltro = $tipo !== '' ? $tipo : null;

        $servidores = Servidor::listar($buscaFiltro, $tipoFiltro);

        $this->view('carteirinha/page', [
            'servidores'             => $servidores,
            'servidores_carteirinha' => Servidor::listarParaCarteirinha($buscaFiltro, $tipoFiltro),
            'busca'                  => $busca,
            'filtro_tipo'            => $tipo,
            'tipos_form'             => Servidor::TIPOS_LABELS,
            'situacoes_form'         => Servidor::SITUACOES_LABELS,
            'total_servidores'       => count($servidores),
        ]);
    }
}
