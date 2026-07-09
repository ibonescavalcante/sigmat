<?php

namespace App\Services;

use App\Models\Pessoa;
use App\Repositories\PessoaRepository;

class PessoaService
{
    private PessoaRepository $repo;

    public function __construct()
    {
        $this->repo = new PessoaRepository();
    }

    public function criar(array $data): array
    {
        //  validação de CPF duplicado
        if ($this->repo->findByCPF($data['cpf'])) {
            return ['erro' => 'CPF já cadastrado'];
        }

        // 🔢 gerar número CMPCD
        $data['cmcpd_numero'] = $this->gerarNumeroCMPCD();

        $pessoa = new Pessoa($data);

        $this->repo->create($pessoa);

        return ['sucesso' => true];
    }

    private function gerarNumeroCMPCD(): string
    {
        $ano = date('Y');
        $numero = rand(100000, 999999);

        return "CMPCD-{$ano}-{$numero}";
    }
}