<?php $this->layout("dashboard/template") ?>

<?php
$servidores = $servidores ?? [];
$busca = $busca ?? '';
$tipos_form = $tipos_form ?? [];
$situacoes_form = $situacoes_form ?? [];
$total_servidores = (int) ($total_servidores ?? count($servidores));

$badgeSituacao = static function (string $situacao): string {
    return match (strtolower($situacao)) {
        'ativo' => 'bg-success',
        'inativo' => 'bg-danger',
        'afastado' => 'bg-warning text-dark',
        default => 'bg-secondary',
    };
};

$labelTipo = static function (string $tipo) use ($tipos_form): string {
    $k = strtoupper(trim($tipo));
    return $tipos_form[$k] ?? $tipo;
};

$labelSituacao = static function (string $situacao) use ($situacoes_form): string {
    $k = strtolower(trim($situacao));
    return $situacoes_form[$k] ?? ucfirst($situacao);
};

$fmtData = static function ($valor): string {
    if ($valor === null || $valor === '') {
        return '—';
    }
    $ts = strtotime((string) $valor);
    return $ts !== false ? date('d/m/Y', $ts) : '—';
};
?>

<div class="col-lg-10 col-md-9 ms-sm-auto px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Gerenciamento de Servidores</h2>
        <button class="btn btn-primary" type="button" id="novoServidor" data-bs-toggle="modal" data-bs-target="#modalNovoServidor">
            <i class="fas fa-user-plus me-2"></i>Novo Servidor
        </button>
    </div>

    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars((string) $_SESSION['erro'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['sucesso'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars((string) $_SESSION['sucesso'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php unset($_SESSION['sucesso']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Buscar servidores</h5>
        </div>
        <div class="card-body">
            <form method="get" action="/servidores" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="searchInput" class="form-label">Nome, matrícula ou CPF</label>
                    <input type="text" name="search" class="form-control" id="searchInput"
                        value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Digite para filtrar">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Buscar
                    </button>
                    <?php if ($busca !== ''): ?>
                        <a href="/servidores" class="btn btn-outline-secondary">Limpar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Servidores cadastrados</h5>
            <span class="text-muted small">Total: <?= $total_servidores ?></span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Tipo</th>
                            <th>Cargo</th>
                            <th>Admissão</th>
                            <th>Situação</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($servidores)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Nenhum servidor encontrado.
                                    <?php if ($busca !== ''): ?>
                                        Tente outro termo de busca ou
                                        <a href="/servidores">limpar o filtro</a>.
                                    <?php else: ?>
                                        Use &quot;Novo Servidor&quot; para cadastrar.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($servidores as $servidor): ?>
                                <?php
                                $situacao = strtolower(trim((string) ($servidor['situacao'] ?? 'ativo')));
                                $tipo = (string) ($servidor['tipo'] ?? '');
                                $servidorJson = htmlspecialchars(
                                    json_encode($servidor, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) ($servidor['matricula'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($servidor['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($servidor['cpf'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($labelTipo($tipo), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($servidor['cargo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($fmtData($servidor['data_admissao'] ?? null), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="badge <?= $badgeSituacao($situacao) ?>">
                                            <?= htmlspecialchars($labelSituacao($situacao), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-acao-ver" title="Visualizar"
                                            data-servidor="<?= $servidorJson ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-acao-editar" title="Editar"
                                            data-servidor="<?= $servidorJson ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-acao-excluir" title="Excluir"
                                            data-servidor="<?= $servidorJson ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNovoServidor" tabindex="-1" aria-labelledby="modalNovoServidorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <form id="formNovoServidor" class="d-flex flex-column min-h-0" novalidate enctype="multipart/form-data">
                <div class="modal-header flex-shrink-0">
                    <h5 class="modal-title" id="modalNovoServidorLabel">Novo servidor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div id="formNovoServidorErro" class="alert alert-danger d-none" role="alert"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="ns_foto" class="form-label">Foto do servidor</label>
                            <input type="file" class="form-control" id="ns_foto" name="foto"
                                accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG ou WEBP. Tamanho máximo: 2 MB.</div>
                            <div class="mt-2">
                                <img id="ns_foto_preview" src="" alt="Pré-visualização da foto"
                                    class="img-thumbnail d-none" style="max-height: 160px;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="ns_tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" id="ns_tipo" name="tipo" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($tipos_form as $valor => $label): ?>
                                    <option value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="ns_matricula" class="form-label">Matrícula <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ns_matricula" name="matricula" required maxlength="20">
                        </div>
                        <div class="col-md-8">
                            <label for="ns_nome" class="form-label">Nome completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ns_nome" name="nome" required minlength="2" maxlength="200">
                        </div>
                        <div class="col-md-4">
                            <label for="ns_cpf" class="form-label">CPF <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ns_cpf" name="cpf" required maxlength="14" placeholder="000.000.000-00">
                        </div>
                        <div class="col-md-6">
                            <label for="ns_situacao" class="form-label">Situação</label>
                            <select class="form-select" id="ns_situacao" name="situacao">
                                <?php foreach ($situacoes_form as $valor => $label): ?>
                                    <option value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>" <?= $valor === 'ativo' ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="ns_rg" class="form-label">RG</label>
                            <input type="text" class="form-control" id="ns_rg" name="rg" maxlength="40">
                        </div>
                        <div class="col-md-4">
                            <label for="ns_naturalidade" class="form-label">Naturalidade</label>
                            <input type="text" class="form-control" id="ns_naturalidade" name="naturalidade" maxlength="120">
                        </div>
                        <div class="col-md-4">
                            <label for="ns_tipo_sanguineo" class="form-label">Tipo sanguíneo</label>
                            <input type="text" class="form-control" id="ns_tipo_sanguineo" name="tipo_sanguineo" maxlength="5" placeholder="Ex: O+">
                        </div>
                        <div class="col-md-4">
                            <label for="ns_data_nascimento" class="form-label">Data de nascimento</label>
                            <input type="date" class="form-control" id="ns_data_nascimento" name="data_nascimento">
                        </div>
                        <div class="col-md-4">
                            <label for="ns_data_admissao" class="form-label">Data de admissão</label>
                            <input type="date" class="form-control" id="ns_data_admissao" name="data_admissao">
                        </div>
                        <div class="col-md-4">
                            <label for="ns_data_emissao" class="form-label">Data de emissão</label>
                            <input type="date" class="form-control" id="ns_data_emissao" name="data_emissao">
                        </div>
                        <div class="col-md-4">
                            <label for="ns_data_validade" class="form-label">Data de validade</label>
                            <input type="date" class="form-control" id="ns_data_validade" name="data_validade">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" id="ns_validade_indeterminada" name="validade_indeterminada" value="1">
                                <label class="form-check-label" for="ns_validade_indeterminada">Indeterminado</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="ns_filiacao_pai" class="form-label">Filiação (pai)</label>
                            <input type="text" class="form-control" id="ns_filiacao_pai" name="filiacao_pai" maxlength="200">
                        </div>
                        <div class="col-md-6">
                            <label for="ns_filiacao_mae" class="form-label">Filiação (mãe)</label>
                            <input type="text" class="form-control" id="ns_filiacao_mae" name="filiacao_mae" maxlength="200">
                        </div>
                        <div class="col-md-8">
                            <label for="ns_fe_publica" class="form-label">Fé pública</label>
                            <input type="text" class="form-control" id="ns_fe_publica" name="fe_publica" maxlength="120">
                        </div>
                        <div class="col-md-4">
                            <label for="ns_porte_arma" class="form-label">Porte de arma (código)</label>
                            <input type="text" class="form-control" id="ns_porte_arma" name="porte_arma" maxlength="50" disabled>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-shrink-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvarNovoServidor">
                        <i class="fas fa-save me-2"></i>Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVisualizarServidor" tabindex="-1" aria-labelledby="modalVisualizarServidorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVisualizarServidorLabel">Visualizar servidor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="visualizarServidorFotoWrap" class="text-center mb-3 d-none">
                    <img id="visualizarServidorFoto" src="" alt="Foto do servidor"
                        class="img-thumbnail" style="max-height: 200px;">
                </div>
                <dl class="row mb-0" id="visualizarServidorConteudo"></dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarServidor" tabindex="-1" aria-labelledby="modalEditarServidorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <form id="formEditarServidor" class="d-flex flex-column min-h-0" novalidate enctype="multipart/form-data">
                <input type="hidden" id="es_id" name="id">
                <div class="modal-header flex-shrink-0">
                    <h5 class="modal-title" id="modalEditarServidorLabel">Editar servidor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div id="formEditarServidorErro" class="alert alert-danger d-none" role="alert"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="es_foto" class="form-label">Foto do servidor</label>
                            <input type="file" class="form-control" id="es_foto" name="foto"
                                accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Deixe em branco para manter a foto atual. JPG, PNG ou WEBP, até 2 MB.</div>
                            <div class="mt-2">
                                <img id="es_foto_preview" src="" alt="Foto atual"
                                    class="img-thumbnail d-none" style="max-height: 160px;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="es_tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" id="es_tipo" name="tipo" required>
                                <?php foreach ($tipos_form as $valor => $label): ?>
                                    <option value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="es_matricula" class="form-label">Matrícula <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="es_matricula" name="matricula" required maxlength="20">
                        </div>
                        <div class="col-md-8">
                            <label for="es_nome" class="form-label">Nome completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="es_nome" name="nome" required minlength="2" maxlength="200">
                        </div>
                        <div class="col-md-4">
                            <label for="es_cpf" class="form-label">CPF <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="es_cpf" name="cpf" required maxlength="14">
                        </div>
                        <div class="col-md-6">
                            <label for="es_situacao" class="form-label">Situação</label>
                            <select class="form-select" id="es_situacao" name="situacao">
                                <?php foreach ($situacoes_form as $valor => $label): ?>
                                    <option value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="es_rg" class="form-label">RG</label>
                            <input type="text" class="form-control" id="es_rg" name="rg" maxlength="40">
                        </div>
                        <div class="col-md-4">
                            <label for="es_naturalidade" class="form-label">Naturalidade</label>
                            <input type="text" class="form-control" id="es_naturalidade" name="naturalidade" maxlength="120">
                        </div>
                        <div class="col-md-4">
                            <label for="es_tipo_sanguineo" class="form-label">Tipo sanguíneo</label>
                            <input type="text" class="form-control" id="es_tipo_sanguineo" name="tipo_sanguineo" maxlength="5">
                        </div>
                        <div class="col-md-4">
                            <label for="es_data_nascimento" class="form-label">Data de nascimento</label>
                            <input type="date" class="form-control" id="es_data_nascimento" name="data_nascimento">
                        </div>
                        <div class="col-md-4">
                            <label for="es_data_admissao" class="form-label">Data de admissão</label>
                            <input type="date" class="form-control" id="es_data_admissao" name="data_admissao">
                        </div>
                        <div class="col-md-4">
                            <label for="es_data_emissao" class="form-label">Data de emissão</label>
                            <input type="date" class="form-control" id="es_data_emissao" name="data_emissao">
                        </div>
                        <div class="col-md-4">
                            <label for="es_data_validade" class="form-label">Data de validade</label>
                            <input type="date" class="form-control" id="es_data_validade" name="data_validade">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" id="es_validade_indeterminada" name="validade_indeterminada" value="1">
                                <label class="form-check-label" for="es_validade_indeterminada">Indeterminado</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="es_filiacao_pai" class="form-label">Filiação (pai)</label>
                            <input type="text" class="form-control" id="es_filiacao_pai" name="filiacao_pai" maxlength="200">
                        </div>
                        <div class="col-md-6">
                            <label for="es_filiacao_mae" class="form-label">Filiação (mãe)</label>
                            <input type="text" class="form-control" id="es_filiacao_mae" name="filiacao_mae" maxlength="200">
                        </div>
                        <div class="col-md-8">
                            <label for="es_fe_publica" class="form-label">Fé pública</label>
                            <input type="text" class="form-control" id="es_fe_publica" name="fe_publica" maxlength="120">
                        </div>
                        <div class="col-md-4">
                            <label for="es_porte_arma" class="form-label">Porte de arma (código)</label>
                            <input type="text" class="form-control" id="es_porte_arma" name="porte_arma" maxlength="50">
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-shrink-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvarEditarServidor">
                        <i class="fas fa-save me-2"></i>Salvar alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalExcluirServidor" tabindex="-1" aria-labelledby="modalExcluirServidorLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalExcluirServidorLabel">Excluir servidor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="formExcluirServidorErro" class="alert alert-danger d-none" role="alert"></div>
                <p class="mb-2">Tem certeza que deseja excluir este servidor?</p>
                <p class="mb-0">
                    <strong id="excluirServidorNome"></strong><br>
                    <span class="text-muted">Matrícula: <span id="excluirServidorMatricula"></span></span>
                </p>
                <input type="hidden" id="ex_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExcluirServidor">
                    <i class="fas fa-trash-alt me-2"></i>Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<style>
#modalNovoServidor .modal-content > form,
#modalEditarServidor .modal-content > form {
    max-height: 100%;
    min-height: 0;
}
#modalNovoServidor .modal-body,
#modalEditarServidor .modal-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tiposLabels = <?= json_encode($tipos_form, JSON_UNESCAPED_UNICODE) ?>;
    const situacoesLabels = <?= json_encode($situacoes_form, JSON_UNESCAPED_UNICODE) ?>;

    function getCsrf() {
        if (typeof getDashboardCsrfToken === 'function') {
            return getDashboardCsrfToken();
        }
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? (meta.getAttribute('content') || '') : '';
    }

    function parseServidor(btn) {
        const raw = btn.getAttribute('data-servidor');
        if (!raw) return null;
        try {
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    }

    function fmtDataBr(valor) {
        if (!valor) return '—';
        const partes = String(valor).split('-');
        if (partes.length === 3) {
            return partes[2] + '/' + partes[1] + '/' + partes[0];
        }
        return valor;
    }

    function fmtValidade(valor) {
        if (!valor) return 'Indeterminado';
        return fmtDataBr(valor);
    }

    function configurarValidadeIndeterminada(prefix) {
        const checkbox = document.getElementById(prefix + '_validade_indeterminada');
        const inputData = document.getElementById(prefix + '_data_validade');
        if (!checkbox || !inputData) return;

        function aplicar() {
            if (checkbox.checked) {
                inputData.value = '';
                inputData.disabled = true;
            } else {
                inputData.disabled = false;
            }
        }

        checkbox.addEventListener('change', aplicar);
        aplicar();
    }

    configurarValidadeIndeterminada('ns');
    configurarValidadeIndeterminada('es');

    function aplicarPorteArmaPorTipo(prefix) {
        const tipoEl = document.getElementById(prefix + '_tipo');
        const porteEl = document.getElementById(prefix + '_porte_arma');
        if (!tipoEl || !porteEl) return;

        const tipo = (tipoEl.value || '').toUpperCase();
        if (tipo === 'GUARDA') {
            porteEl.disabled = false;
        } else {
            if (tipo === 'DMTT') {
                porteEl.value = '';
            }
            porteEl.disabled = true;
        }
    }

    function configurarPorteArmaPorTipo(prefix) {
        const tipoEl = document.getElementById(prefix + '_tipo');
        if (!tipoEl) return;
        tipoEl.addEventListener('change', function() {
            aplicarPorteArmaPorTipo(prefix);
        });
        aplicarPorteArmaPorTipo(prefix);
    }

    configurarPorteArmaPorTipo('ns');
    configurarPorteArmaPorTipo('es');

    function montarFormData(formEl) {
        const fd = new FormData(formEl);
        const validadeIndet = formEl.querySelector('[name="validade_indeterminada"]');
        if (validadeIndet) {
            fd.set('validade_indeterminada', validadeIndet.checked ? '1' : '0');
            if (validadeIndet.checked) {
                fd.set('data_validade', 'indeterminado');
            }
        }
        const tipoEl = formEl.querySelector('[name="tipo"]');
        if (tipoEl && tipoEl.value === 'DMTT') {
            fd.set('porte_arma', '');
        }
        return fd;
    }

    function atualizarPreviewFoto(inputEl, previewEl, urlAtual) {
        if (!previewEl) return;
        if (inputEl && inputEl.files && inputEl.files[0]) {
            previewEl.src = URL.createObjectURL(inputEl.files[0]);
            previewEl.classList.remove('d-none');
            return;
        }
        if (urlAtual) {
            previewEl.src = urlAtual;
            previewEl.classList.remove('d-none');
            return;
        }
        previewEl.src = '';
        previewEl.classList.add('d-none');
    }

    function configurarPreviewFoto(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!input || !preview) return;
        input.addEventListener('change', function() {
            atualizarPreviewFoto(input, preview, null);
        });
    }

    configurarPreviewFoto('ns_foto', 'ns_foto_preview');
    configurarPreviewFoto('es_foto', 'es_foto_preview');

    function coletarPayload(prefix) {
        const validadeIndet = document.getElementById(prefix + '_validade_indeterminada');
        return {
            tipo: document.getElementById(prefix + '_tipo').value,
            matricula: document.getElementById(prefix + '_matricula').value.trim(),
            nome: document.getElementById(prefix + '_nome').value.trim(),
            cpf: document.getElementById(prefix + '_cpf').value.trim(),
            situacao: document.getElementById(prefix + '_situacao').value,
            rg: document.getElementById(prefix + '_rg').value.trim(),
            naturalidade: document.getElementById(prefix + '_naturalidade').value.trim(),
            tipo_sanguineo: document.getElementById(prefix + '_tipo_sanguineo').value.trim(),
            data_nascimento: document.getElementById(prefix + '_data_nascimento').value,
            data_admissao: document.getElementById(prefix + '_data_admissao').value,
            data_emissao: document.getElementById(prefix + '_data_emissao').value,
            data_validade: validadeIndet && validadeIndet.checked ? 'indeterminado' : document.getElementById(prefix + '_data_validade').value,
            validade_indeterminada: validadeIndet ? validadeIndet.checked : false,
            filiacao_pai: document.getElementById(prefix + '_filiacao_pai').value.trim(),
            filiacao_mae: document.getElementById(prefix + '_filiacao_mae').value.trim(),
            fe_publica: document.getElementById(prefix + '_fe_publica').value.trim(),
            porte_arma: document.getElementById(prefix + '_porte_arma').value.trim()
        };
    }

    function preencherFormulario(prefix, s) {
        document.getElementById(prefix + '_tipo').value = s.tipo || '';
        document.getElementById(prefix + '_matricula').value = s.matricula || '';
        document.getElementById(prefix + '_nome').value = s.nome || '';
        document.getElementById(prefix + '_cpf').value = s.cpf || '';
        document.getElementById(prefix + '_situacao').value = s.situacao || 'ativo';
        document.getElementById(prefix + '_rg').value = s.rg || '';
        document.getElementById(prefix + '_naturalidade').value = s.naturalidade || '';
        document.getElementById(prefix + '_tipo_sanguineo').value = s.tipo_sanguineo || '';
        document.getElementById(prefix + '_data_nascimento').value = s.data_nascimento || '';
        document.getElementById(prefix + '_data_admissao').value = s.data_admissao || '';
        document.getElementById(prefix + '_data_emissao').value = s.data_emissao || '';
        const validadeIndet = document.getElementById(prefix + '_validade_indeterminada');
        const dataValidade = document.getElementById(prefix + '_data_validade');
        const semValidade = !s.data_validade;
        if (validadeIndet) {
            validadeIndet.checked = semValidade;
            validadeIndet.dispatchEvent(new Event('change'));
        }
        if (dataValidade && !semValidade) {
            dataValidade.value = s.data_validade || '';
        }
        document.getElementById(prefix + '_filiacao_pai').value = s.filiacao_pai || '';
        document.getElementById(prefix + '_filiacao_mae').value = s.filiacao_mae || '';
        document.getElementById(prefix + '_fe_publica').value = s.fe_publica || '';
        const porteArma = document.getElementById(prefix + '_porte_arma');
        if (porteArma) {
            const pa = s.porte_arma;
            if (pa === true || pa === 't' || pa === '1' || pa === 1) {
                porteArma.value = 'SIM';
            } else {
                porteArma.value = pa ? String(pa) : '';
            }
        }
        aplicarPorteArmaPorTipo(prefix);
    }

    function preencherVisualizacao(s) {
        const fotoWrap = document.getElementById('visualizarServidorFotoWrap');
        const fotoImg = document.getElementById('visualizarServidorFoto');
        if (s.foto_url) {
            fotoImg.src = s.foto_url;
            fotoWrap.classList.remove('d-none');
        } else {
            fotoImg.src = '';
            fotoWrap.classList.add('d-none');
        }

        const campos = [
            ['Tipo', tiposLabels[s.tipo] || s.tipo || '—'],
            ['Matrícula', s.matricula || '—'],
            ['Nome', s.nome || '—'],
            ['CPF', s.cpf || '—'],
            ['Cargo', tiposLabels[s.tipo] || s.cargo || '—'],
            ['Situação', situacoesLabels[s.situacao] || s.situacao || '—'],
            ['RG', s.rg || '—'],
            ['Naturalidade', s.naturalidade || '—'],
            ['Tipo sanguíneo', s.tipo_sanguineo || '—'],
            ['Data de nascimento', fmtDataBr(s.data_nascimento)],
            ['Data de admissão', fmtDataBr(s.data_admissao)],
            ['Data de emissão', fmtDataBr(s.data_emissao)],
            ['Data de validade', fmtValidade(s.data_validade)],
            ['Filiação (pai)', s.filiacao_pai || '—'],
            ['Filiação (mãe)', s.filiacao_mae || '—'],
            ['Fé pública', s.fe_publica || '—'],
            ['Porte de arma', (function() {
                const pa = s.porte_arma;
                if (pa === true || pa === 't' || pa === '1' || pa === 1) return 'SIM';
                return pa ? String(pa) : '—';
            })()],
        ];

        const container = document.getElementById('visualizarServidorConteudo');
        container.innerHTML = campos.map(function(item) {
            return '<dt class="col-sm-4 text-muted">' + item[0] + '</dt><dd class="col-sm-8">' + item[1] + '</dd>';
        }).join('');
    }

    document.querySelectorAll('.btn-acao-ver').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const s = parseServidor(this);
            if (!s) return;
            preencherVisualizacao(s);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVisualizarServidor')).show();
        });
    });

    document.querySelectorAll('.btn-acao-editar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const s = parseServidor(this);
            if (!s) return;
            document.getElementById('es_id').value = s.id;
            preencherFormulario('es', s);
            document.getElementById('es_foto').value = '';
            atualizarPreviewFoto(null, document.getElementById('es_foto_preview'), s.foto_url || null);
            document.getElementById('formEditarServidorErro').classList.add('d-none');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarServidor')).show();
        });
    });

    document.querySelectorAll('.btn-acao-excluir').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const s = parseServidor(this);
            if (!s) return;
            document.getElementById('ex_id').value = s.id;
            document.getElementById('excluirServidorNome').textContent = s.nome || '';
            document.getElementById('excluirServidorMatricula').textContent = s.matricula || '';
            document.getElementById('formExcluirServidorErro').classList.add('d-none');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalExcluirServidor')).show();
        });
    });

    const formNovo = document.getElementById('formNovoServidor');
    const erroNovo = document.getElementById('formNovoServidorErro');
    const btnSalvarNovo = document.getElementById('btnSalvarNovoServidor');
    const modalNovoEl = document.getElementById('modalNovoServidor');

    if (formNovo) {
        formNovo.addEventListener('submit', async function(e) {
            e.preventDefault();
            erroNovo.classList.add('d-none');
            erroNovo.textContent = '';
            btnSalvarNovo.disabled = true;
            try {
                const res = await fetch('/servidores', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-CSRF-Token': getCsrf() },
                    body: montarFormData(formNovo)
                });
                const data = await res.json().catch(function() { return {}; });
                if (!res.ok) {
                    erroNovo.textContent = data.erro || 'Não foi possível salvar.';
                    erroNovo.classList.remove('d-none');
                    return;
                }
                bootstrap.Modal.getInstance(modalNovoEl)?.hide();
                window.location.reload();
            } catch (err) {
                erroNovo.textContent = 'Erro de rede. Tente novamente.';
                erroNovo.classList.remove('d-none');
            } finally {
                btnSalvarNovo.disabled = false;
            }
        });
    }

    const formEditar = document.getElementById('formEditarServidor');
    const erroEditar = document.getElementById('formEditarServidorErro');
    const btnSalvarEditar = document.getElementById('btnSalvarEditarServidor');

    if (formEditar) {
        formEditar.addEventListener('submit', async function(e) {
            e.preventDefault();
            erroEditar.classList.add('d-none');
            erroEditar.textContent = '';
            btnSalvarEditar.disabled = true;
            try {
                const res = await fetch('/servidores/atualizar', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-CSRF-Token': getCsrf() },
                    body: montarFormData(formEditar)
                });
                const data = await res.json().catch(function() { return {}; });
                if (!res.ok) {
                    erroEditar.textContent = data.erro || 'Não foi possível salvar.';
                    erroEditar.classList.remove('d-none');
                    return;
                }
                bootstrap.Modal.getInstance(document.getElementById('modalEditarServidor'))?.hide();
                window.location.reload();
            } catch (err) {
                erroEditar.textContent = 'Erro de rede. Tente novamente.';
                erroEditar.classList.remove('d-none');
            } finally {
                btnSalvarEditar.disabled = false;
            }
        });
    }

    const btnExcluir = document.getElementById('btnConfirmarExcluirServidor');
    const erroExcluir = document.getElementById('formExcluirServidorErro');

    if (btnExcluir) {
        btnExcluir.addEventListener('click', async function() {
            erroExcluir.classList.add('d-none');
            erroExcluir.textContent = '';
            const id = parseInt(document.getElementById('ex_id').value, 10);
            btnExcluir.disabled = true;
            try {
                const res = await fetch('/servidores/excluir', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrf() },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json().catch(function() { return {}; });
                if (!res.ok) {
                    erroExcluir.textContent = data.erro || 'Não foi possível excluir.';
                    erroExcluir.classList.remove('d-none');
                    return;
                }
                bootstrap.Modal.getInstance(document.getElementById('modalExcluirServidor'))?.hide();
                window.location.reload();
            } catch (err) {
                erroExcluir.textContent = 'Erro de rede. Tente novamente.';
                erroExcluir.classList.remove('d-none');
            } finally {
                btnExcluir.disabled = false;
            }
        });
    }

    if (modalNovoEl && erroNovo) {
        modalNovoEl.addEventListener('hidden.bs.modal', function() {
            formNovo?.reset();
            const situacao = document.getElementById('ns_situacao');
            if (situacao) situacao.value = 'ativo';
            const validadeIndet = document.getElementById('ns_validade_indeterminada');
            if (validadeIndet) {
                validadeIndet.checked = false;
                validadeIndet.dispatchEvent(new Event('change'));
            }
            aplicarPorteArmaPorTipo('ns');
            atualizarPreviewFoto(null, document.getElementById('ns_foto_preview'), null);
            erroNovo.classList.add('d-none');
            erroNovo.textContent = '';
        });
    }
});
</script>
