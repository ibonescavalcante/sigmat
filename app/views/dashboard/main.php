<?php $this->layout("dashboard/template") ?>
<?php
$total_processos_em_andamento = (int) ($total_processos_em_andamento ?? 0);
$total_candidatos = (int) ($total_candidatos ?? 0);
$total_inscricoes_ativas = (int) ($total_inscricoes_ativas ?? 0);
$processos_em_andamento = $processos_em_andamento ?? [];

$fmtNumPt = static function (int $n): string {
    return number_format($n, 0, ',', '.');
};

$fmtDataPt = static function ($valor): ?string {
    if ($valor === null || $valor === '') {
        return null;
    }
    $ts = strtotime((string) $valor);
    return $ts !== false ? date('d/m/Y', $ts) : null;
};

$badgeStatusGlobal = static function (string $status): array {
    return match ($status) {
        'em_andamento' => ['cls' => 'bg-success', 'label' => 'Em andamento'],
        'encerrado' => ['cls' => 'bg-secondary', 'label' => 'Encerrado'],
        'rascunho' => ['cls' => 'bg-light text-dark', 'label' => 'Rascunho'],
        default => ['cls' => 'bg-info', 'label' => $status],
    };
};
?>
<!-- Conteúdo Principal -->
<div class="col-lg-10 col-md-9 ms-sm-auto px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted fw-normal mb-2">Ultimos cadastros</h6>
                            <h3 class="mb-0"><?= htmlspecialchars($fmtNumPt($total_processos_em_andamento), ENT_QUOTES, 'UTF-8') ?></h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-clipboard-list text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted fw-normal mb-2">Servidores</h6>
                            <h3 class="mb-0"><?= htmlspecialchars($fmtNumPt($total_candidatos), ENT_QUOTES, 'UTF-8') ?></h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-users text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted fw-normal mb-2">Carteinhas Impressas</h6>
                            <h3 class="mb-0"><?= htmlspecialchars($fmtNumPt($total_inscricoes_ativas), ENT_QUOTES, 'UTF-8') ?></h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-alt text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Processos em Destaque -->
    <?php
    $linkInscricoesPainel = '/inscricoes';
    if (!empty($processos_em_andamento)) {
        $primeiroId = (int) ($processos_em_andamento[0]['id'] ?? 0);
        if ($primeiroId > 0) {
            $linkInscricoesPainel = '/inscricoes?processo_id=' . $primeiroId;
        }
    }
    ?>
    <div class="row">
        <div class="col-lg-12 mb-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <a href="<?= htmlspecialchars($linkInscricoesPainel, ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none text-body" title="Abrir inscrições com processo selecionado">Processos em Andamento</a>
                    </h5>
                    <a href="/dashboard/processos" class="btn btn-sm btn-outline-primary">Ver Todos</a>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php if (empty($processos_em_andamento)): ?>
                        <div class="list-group-item text-muted small py-3">
                            Nenhum processo em andamento.
                        </div>
                        <?php else: ?>
                            <?php foreach ($processos_em_andamento as $processo): ?>
                                <?php
                                $titulo = htmlspecialchars((string) ($processo['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
                                $ini = $fmtDataPt($processo['inscricao_ini'] ?? null);
                                $fim = $fmtDataPt($processo['inscricao_fim'] ?? null);
                                if ($ini !== null && $fim !== null) {
                                    $periodo = 'Inscrições: ' . $ini . ' a ' . $fim;
                                } elseif ($ini !== null) {
                                    $periodo = 'Inscrições a partir de ' . $ini;
                                } elseif ($fim !== null) {
                                    $periodo = 'Inscrições até ' . $fim;
                                } else {
                                    $periodo = 'Datas de inscrição não informadas';
                                }
                                $status = (string) ($processo['status_global'] ?? '');
                                $badge = $badgeStatusGlobal($status);
                                $badgeLabel = htmlspecialchars($badge['label'], ENT_QUOTES, 'UTF-8');
                                $badgeCls = htmlspecialchars($badge['cls'], ENT_QUOTES, 'UTF-8');
                                $pid = (int) ($processo['id'] ?? 0);
                                $hrefInscricoes = $pid > 0 ? '/inscricoes?processo_id=' . $pid : '/inscricoes';
                                ?>
                        <a href="<?= htmlspecialchars($hrefInscricoes, ENT_QUOTES, 'UTF-8') ?>" class="list-group-item list-group-item-action d-flex align-items-center processo-card text-decoration-none text-body" title="Ver inscrições deste processo">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= $titulo !== '' ? $titulo : '—' ?></h6>
                                <p class="mb-0 text-muted small"><?= htmlspecialchars($periodo, ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="badge <?= $badgeCls ?> status-badge"><?= $badgeLabel ?></span>
                            </div>
                        </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Modal de Login (exemplo) -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Acesso ao Sistema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Lembrar-me</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="#">Esqueci minha senha</a>
            </div>
        </div>
    </div>
</div>
</div>
