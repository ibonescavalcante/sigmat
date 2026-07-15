<?php $this->layout('dashboard/template'); ?>

<?php
$servidores = $servidores ?? [];
$servidores_carteirinha = $servidores_carteirinha ?? [];
$busca = $busca ?? '';
$filtro_tipo = $filtro_tipo ?? '';
$tipos_form = $tipos_form ?? [];
$situacoes_form = $situacoes_form ?? [];
$total_servidores = (int) ($total_servidores ?? count($servidores));

// Posicionamento dos campos — modelo DMTT
$camposCarteirinha = [
    ['key' => 'foto', 'type' => 'image', 'left' => 12.30, 'top' => 27.50, 'width' => 10.80, 'height' => 40.00],
    ['key' => 'nome', 'type' => 'text', 'left' => 21.80, 'top' => 30.00, 'width' => 28.72, 'upper' => true],
    ['key' => 'cargo', 'type' => 'text', 'left' => 21.80, 'top' => 42.00, 'width' => 23.65, 'upper' => false],
    ['key' => 'emissao', 'type' => 'text', 'left' => 21.80, 'top' => 53.29, 'width' => 10.14, 'upper' => true],
    ['key' => 'validade', 'type' => 'text', 'left' => 33.40, 'top' => 53.29, 'width' => 8.45, 'upper' => false],
    ['key' => 'matricula', 'type' => 'text', 'left' => 8.88, 'top' => 75.60, 'width' => 9.29],
    ['key' => 'cpf', 'type' => 'text', 'left' => 54.30, 'top' => 16.60, 'width' => 10.98],
    ['key' => 'rg', 'type' => 'text', 'left' => 69.00, 'top' =>16.60, 'width' => 10.98],
    ['key' => 'nascimento', 'type' => 'text', 'left' => 54.30, 'top' => 27.00, 'width' => 10.98],
    ['key' => 'naturalidade', 'type' => 'text', 'left' => 69.00, 'top' => 27.00, 'width' => 10.98, 'upper' => true],
    ['key' => 'tipo_sanguineo', 'type' => 'text', 'left' => 85.00, 'top' => 23.47, 'width' => 10.76, 'height' => 40.00],
    ['key' => 'filiacaop', 'type' => 'text', 'left' => 54.30, 'top' => 39.10, 'width' => 23.65, 'upper' => true],
    ['key' => 'filiacaom', 'type' => 'text', 'left' => 54.30, 'top' => 47.00, 'width' => 23.65, 'upper' => true],
    ['key' => 'fepublica', 'type' => 'text', 'left' => 54.30, 'top' => 56.20, 'width' => 23.65, 'upper' => true],
    ['key' => 'admissao', 'type' => 'text', 'left' => 54.30, 'top' => 67.90, 'width' => 10.98],
];

// Posicionamento dos campos — modelo Guarda Municipal
// left reduzido (~2%) — textos estavam muito à direita na matriz
$camposCarteirinhaGuarda = [
    ['key' => 'foto', 'type' => 'image', 'left' => 8.00, 'top' => 24.50, 'width' => 10.80, 'height' => 40.00],
    ['key' => 'nome', 'type' => 'text', 'left' => 22.50, 'top' => 32.50, 'width' => 28.72, 'upper' => true],
    ['key' => 'cargo', 'type' => 'text', 'left' => 22.50, 'top' => 46.20, 'width' => 23.65, 'upper' => true, 'font-size' => 10],
    ['key' => 'emissao', 'type' => 'text', 'left' => 22.50, 'top' => 59.50, 'width' => 10.14, 'upper' => true],
    ['key' => 'validade', 'type' => 'text', 'left' => 34.50, 'top' => 59.50, 'width' => 8.45, 'upper' => true],
    ['key' => 'matricula', 'type' => 'text', 'left' => 10.50, 'top' => 85.80, 'width' => 9.29],
    ['key' => 'cpf', 'type' => 'text', 'left' => 58.50, 'top' => 14.80, 'width' => 10.98],
    ['key' => 'rg', 'type' => 'text', 'left' => 72.80, 'top' => 14.80, 'width' => 10.98],
    ['key' => 'nascimento', 'type' => 'text', 'left' => 58.50, 'top' => 27.40, 'width' => 10.98],
    ['key' => 'naturalidade', 'type' => 'text', 'left' => 72.80, 'top' => 27.40, 'width' => 10.98, 'upper' => true],
    ['key' => 'tipo_sanguineo', 'type' => 'text', 'left' => 92.00, 'top' => 20.00, 'width' => 10.76, 'height' => 40.00],
    ['key' => 'filiacaop', 'type' => 'text', 'left' => 58.50, 'top' => 42.80, 'width' => 23.65, 'upper' => true],
    ['key' => 'filiacaom', 'type' => 'text', 'left' => 58.50, 'top' => 48.20, 'width' => 23.65, 'upper' => true],
    ['key' => 'fepublica', 'type' => 'text', 'left' => 58.50, 'top' => 62.80, 'width' => 23.65, 'upper' => true],
    ['key' => 'admissao', 'type' => 'text', 'left' => 58.50, 'top' => 75.80, 'width' => 10.98],
    ['key' => 'porte', 'type' => 'text', 'left' => 58.50, 'top' => 88.50, 'width' => 10.98, 'upper' => true],
];

$labelTipo = static function (string $tipo) use ($tipos_form): string {
    $k = strtoupper(trim($tipo));
    return $tipos_form[$k] ?? $tipo;
};

$labelSituacao = static function (string $situacao) use ($situacoes_form): string {
    $k = strtolower(trim($situacao));
    return $situacoes_form[$k] ?? ucfirst($situacao);
};

$badgeSituacao = static function (string $situacao): string {
    return match (strtolower($situacao)) {
        'ativo' => 'bg-success',
        'inativo' => 'bg-danger',
        'afastado' => 'bg-warning text-dark',
        default => 'bg-secondary',
    };
};
?>

<div class="col-lg-10 col-md-9 ms-sm-auto px-4 py-3 carteirinha-page">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 no-print">
        <h2 class="h4 mb-0">Carteirinhas</h2>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-primary" id="btnGerarCarteirinhas" disabled>
                <i class="fas fa-id-card me-2"></i>Gerar carteirinhas
            </button>
            <button type="button" class="btn btn-success" id="btnImprimirCarteirinhas" disabled>
                <i class="fas fa-print me-2"></i>Imprimir
            </button>
        </div>
    </div>

    <div class="no-print mb-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Selecionar servidores</h5>
            </div>
            <div class="card-body">
                <form method="get" action="/carteirinha" class="row g-3 align-items-end mb-3">
                    <div class="col-12">
                        <span class="form-label d-block mb-2">Tipo de servidor</span>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="filtro_tipo_guarda"
                                    value="GUARDA" <?= $filtro_tipo === 'GUARDA' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="filtro_tipo_guarda">Guarda Municipal</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="filtro_tipo_dmtt"
                                    value="DMTT" <?= $filtro_tipo === 'DMTT' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="filtro_tipo_dmtt">DMTT</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label for="searchInput" class="form-label">Buscar por nome, matrícula ou CPF</label>
                        <input type="text" name="search" id="searchInput" class="form-control"
                            value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Digite para filtrar">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                        <?php if ($busca !== '' || $filtro_tipo !== ''): ?>
                            <a href="/carteirinha" class="btn btn-outline-secondary">Limpar</a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="d-flex justify-content-end align-items-center mb-2">
                    <span class="text-muted small" id="contadorSelecionados">0 selecionado(s)</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:40px"></th>
                                <th>Matrícula</th>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Tipo</th>
                                <th>Cargo</th>
                                <th>Situação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($filtro_tipo === ''): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Selecione Guarda Municipal ou DMTT para exibir os servidores.
                                    </td>
                                </tr>
                            <?php elseif (empty($servidores)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Nenhum servidor encontrado.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($servidores as $servidor): ?>
                                    <?php
                                    $id = (int) ($servidor['id'] ?? 0);
                                    $situacao = strtolower(trim((string) ($servidor['situacao'] ?? 'ativo')));
                                    $tipo = (string) ($servidor['tipo'] ?? '');
                                    ?>
                                    <tr>
                                        <td>
                                            <input class="form-check-input chk-servidor" type="checkbox"
                                                value="<?= $id ?>" id="chk-servidor-<?= $id ?>">
                                        </td>
                                        <td>
                                            <label class="mb-0" for="chk-servidor-<?= $id ?>">
                                                <?= htmlspecialchars((string) ($servidor['matricula'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            </label>
                                        </td>
                                        <td><?= htmlspecialchars((string) ($servidor['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($servidor['cpf'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($labelTipo($tipo), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($servidor['cargo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <span class="badge <?= $badgeSituacao($situacao) ?>">
                                                <?= htmlspecialchars($labelSituacao($situacao), ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($filtro_tipo !== ''): ?>
                    <div class="text-muted small mt-2">Total: <?= $total_servidores ?> servidor(es)</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="areaPreviewCarteirinhas" class="mb-3 no-print d-none">
        <div class="alert alert-info py-2 mb-3">
            Pré-visualização das carteirinhas selecionadas. Clique em <strong>Imprimir</strong> para enviar à impressora.
        </div>
    </div>

    <div class="carteirinhas-lista" id="carteirinhasLista">
        <p class="text-muted text-center no-print" id="carteirinhasListaVazia">
            Selecione um ou mais servidores e clique em &quot;Gerar carteirinhas&quot;.
        </p>
    </div>
</div>

<link rel="stylesheet" href="/assets/css/carteirinha.css?v=20260715-2">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const servidoresCarteirinha = <?= json_encode($servidores_carteirinha, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    const camposCarteirinha = <?= json_encode($camposCarteirinha, JSON_UNESCAPED_UNICODE) ?>;
    const camposCarteirinhaGuarda = <?= json_encode($camposCarteirinhaGuarda, JSON_UNESCAPED_UNICODE) ?>;
    const carteirinhasPorPagina = 4;

    const checks = document.querySelectorAll('.chk-servidor');
    const contador = document.getElementById('contadorSelecionados');
    const btnGerar = document.getElementById('btnGerarCarteirinhas');
    const btnImprimir = document.getElementById('btnImprimirCarteirinhas');
    const lista = document.getElementById('carteirinhasLista');
    const listaVazia = document.getElementById('carteirinhasListaVazia');
    const areaPreview = document.getElementById('areaPreviewCarteirinhas');
    const filtroTipoAtual = <?= json_encode($filtro_tipo, JSON_UNESCAPED_UNICODE) ?>;

    const backgroundsCarteirinha = {
        GUARDA: '/assets/img/bg-carteirinha_guarda.png',
        DMTT: '/assets/img/bg-carteirinha.png'
    };

    const camposPorTipo = {
        DMTT: camposCarteirinha,
        GUARDA: camposCarteirinhaGuarda
    };

    const mapaServidores = {};
    servidoresCarteirinha.forEach(function(s) {
        mapaServidores[String(s.id)] = s;
    });

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function estiloCampo(campo) {
        let style = 'left:' + campo.left + '%;top:' + campo.top + '%;';
        if (campo.width) style += 'width:' + campo.width + '%;';
        if (campo.height) style += 'height:' + campo.height + '%;';
        if (campo['font-size']) style += 'font-size:' + campo['font-size'] + 'px;';
        return style;
    }

    function idsSelecionados() {
        return Array.from(checks)
            .filter(function(chk) { return chk.checked; })
            .map(function(chk) { return String(chk.value); });
    }

    function atualizarContador() {
        const qtd = idsSelecionados().length;
        contador.textContent = qtd + ' selecionado(s)';
        btnGerar.disabled = qtd < 1;
        if (qtd < 1) {
            btnImprimir.disabled = true;
        }
    }

    function renderCampo(usuario, campo) {
        let valor = usuario[campo.key] || '';
        if (valor === '') return '';
        if (campo.upper) valor = String(valor).toUpperCase();
        const style = estiloCampo(campo);
        if (campo.type === 'image') {
            return '<img src="' + escapeHtml(valor) + '" alt="Foto" class="foto" style="' + style + '">';
        }
        return '<div class="campo ' + escapeHtml(campo.key) + '" style="' + style + '">' + escapeHtml(valor) + '</div>';
    }

    function tipoSelecionado() {
        const radio = document.querySelector('input[name="tipo"]:checked');
        return radio ? String(radio.value).toUpperCase() : '';
    }

    function backgroundCarteirinha(tipo) {
        return backgroundsCarteirinha[tipo] || '';
    }

    function camposCarteirinhaPorTipo(tipo) {
        return camposPorTipo[tipo] || [];
    }

    function renderFolha(usuario, backgroundUrl, campos) {
        let camposHtml = '';
        campos.forEach(function(campo) {
            camposHtml += renderCampo(usuario, campo);
        });
        return '<div class="folha">' +
            '<img src="' + escapeHtml(backgroundUrl) + '" alt="Modelo da carteirinha" class="modelo">' +
            camposHtml +
            '</div>';
    }

    function gerarCarteirinhas() {
        const tipo = tipoSelecionado() || String(filtroTipoAtual || '').toUpperCase();
        const backgroundUrl = backgroundCarteirinha(tipo);
        const campos = camposCarteirinhaPorTipo(tipo);

        if (!backgroundUrl || campos.length === 0) {
            alert('Selecione Guarda Municipal ou DMTT antes de gerar as carteirinhas.');
            return;
        }

        const ids = idsSelecionados();
        const selecionados = ids.map(function(id) { return mapaServidores[id]; }).filter(Boolean);

        if (selecionados.length === 0) {
            alert('Selecione ao menos um servidor.');
            return;
        }

        let html = '';
        for (let i = 0; i < selecionados.length; i += carteirinhasPorPagina) {
            const pagina = selecionados.slice(i, i + carteirinhasPorPagina);
            html += '<div class="pagina-impressao" >';
            pagina.forEach(function(usuario) {
                html += renderFolha(usuario, backgroundUrl, campos);
            });
            html += '</div>';
        }

        lista.innerHTML = html;
        if (listaVazia) listaVazia.remove();
        areaPreview.classList.remove('d-none');
        btnImprimir.disabled = false;
        lista.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    checks.forEach(function(chk) {
        chk.addEventListener('change', atualizarContador);
    });

    btnGerar.addEventListener('click', gerarCarteirinhas);

    btnImprimir.addEventListener('click', function() {
        if (!lista.querySelector('.folha')) {
            alert('Gere as carteirinhas antes de imprimir.');
            return;
        }
        const tipo = tipoSelecionado() || String(filtroTipoAtual || '').toUpperCase();
        if (!backgroundCarteirinha(tipo) || camposCarteirinhaPorTipo(tipo).length === 0) {
            alert('Selecione Guarda Municipal ou DMTT antes de imprimir.');
            return;
        }
        window.print();
    });

    document.querySelectorAll('input[name="tipo"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            radio.closest('form').submit();
        });
    });

    atualizarContador();
});
</script>
