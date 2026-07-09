<?php $this->layout('dashboard/template') ?>
<?php
$usuarios = $usuarios ?? [];
$perfis_form = $perfis_form ?? [];
$perfil_labels = $perfil_labels ?? [];
$pode_gerir_usuarios = !empty($pode_gerir_usuarios);
$badgePerfil = static function (string $perfil): string {
    return match ($perfil) {
        'administrador' => 'bg-primary',
        'operador' => 'bg-info',
        'visualizador' => 'bg-secondary',
        default => 'bg-secondary',
    };
};
?>
<!-- Conteúdo Principal -->
<div class="col-lg-10 col-md-9 ms-sm-auto px-4 py-3">
     
        <div class="tab-pane " id="usuarios">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Gerenciamento de Usuários</h5>
                    <?php if ($pode_gerir_usuarios): ?>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#modalNovoUsuario">
                        <i class="fas fa-plus me-1"></i> Novo Usuário
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!$pode_gerir_usuarios): ?>
                    <div class="alert alert-info mb-3" role="alert">
                        Apenas utilizadores com perfil <strong>Administrador</strong> podem criar ou editar
                        utilizadores. A lista abaixo é apenas para consulta.
                    </div>
                    <?php endif; ?>
                    <div id="novoUsuarioAlertas" class="mb-3"></div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>E-mail</th>
                                    <th>Perfil</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaUsuariosBody">
                                <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="5" class="text-muted text-center py-4">Nenhum utilizador
                                        cadastrado.<?php if ($pode_gerir_usuarios): ?> Use &quot;Novo
                                        Usuário&quot; para adicionar.<?php endif; ?></td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($usuarios as $u): ?>
                                <?php
                                    $pid = strtolower(trim((string) ($u['perfil'] ?? '')));
                                    $perfilNome = $perfil_labels[$pid] ?? $pid;
                                    $ativo = !empty($u['ativo']) && ($u['ativo'] === true || $u['ativo'] === 't' || $u['ativo'] === '1' || $u['ativo'] === 1);
                                    ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) ($u['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td><?= htmlspecialchars((string) ($u['usuario'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td><span
                                            class="badge <?= $badgePerfil($pid) ?> user-role-badge"><?= htmlspecialchars($perfilNome, ENT_QUOTES, 'UTF-8') ?></span>
                                    </td>
                                    <td><?php if ($ativo): ?><span class="badge bg-success">Ativo</span><?php else: ?><span
                                            class="badge bg-warning">Inativo</span><?php endif; ?></td>
                                    <td>
                                        <?php if ($pode_gerir_usuarios):
                                            $editPayload = json_encode([
                                                'id' => (int) ($u['id'] ?? 0),
                                                'nome' => (string) ($u['nome'] ?? ''),
                                                'usuario' => (string) ($u['usuario'] ?? ''),
                                                'telefone' => (string) ($u['telefone'] ?? ''),
                                                'perfil' => $pid,
                                                'ativo' => $ativo,
                                            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                                            ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-editar-usuario"
                                            data-user="<?= htmlspecialchars($editPayload, ENT_QUOTES, 'UTF-8') ?>"
                                            title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($pode_gerir_usuarios): ?>
                    <!-- Modal: novo usuário -->
                    <div class="modal fade" id="modalNovoUsuario" tabindex="-1"
                        aria-labelledby="modalNovoUsuarioLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalNovoUsuarioLabel">Novo usuário</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Fechar"></button>
                                </div>
                                <form id="formNovoUsuario" novalidate>
                                    <div class="modal-body">
                                        <div id="formNovoUsuarioErro" class="alert alert-danger d-none"
                                            role="alert"></div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="nu_nome" class="form-label">Nome completo <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="nu_nome" name="nome"
                                                    required minlength="2" maxlength="200"
                                                    autocomplete="name">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="nu_usuario" class="form-label">E-mail <span
                                                        class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="nu_usuario"
                                                    name="usuario" required maxlength="180"
                                                    autocomplete="email">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="nu_telefone" class="form-label">Telefone</label>
                                                <input type="text" class="form-control" id="nu_telefone"
                                                    name="telefone" maxlength="40" placeholder="(00) 00000-0000"
                                                    autocomplete="tel">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="nu_perfil" class="form-label">Perfil <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select" id="nu_perfil" name="perfil"
                                                    required>
                                                    <?php foreach ($perfis_form as $op): ?>
                                                    <option value="<?= htmlspecialchars($op['value'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= htmlspecialchars($op['label'], ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="nu_senha" class="form-label">Senha <span
                                                        class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="nu_senha"
                                                    name="senha" required minlength="8" maxlength="128"
                                                    autocomplete="new-password">
                                                <div class="form-text">Mínimo de 8 caracteres.</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="nu_senha2" class="form-label">Confirmar senha <span
                                                        class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="nu_senha2"
                                                    name="senha_confirmacao" required minlength="8"
                                                    maxlength="128" autocomplete="new-password">
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="nu_ativo" name="ativo" checked>
                                                    <label class="form-check-label" for="nu_ativo">Usuário
                                                        ativo</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary" id="btnSalvarNovoUsuario">
                                            <i class="fas fa-save me-1"></i> Salvar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                      <!-- Fim Modal: novo usuário -->

                    <!-- Modal: editar usuário -->
                    <div class="modal fade" id="modalEditarUsuario" tabindex="-1"
                        aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalEditarUsuarioLabel">Editar Usuário</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Fechar"></button>
                                </div>
                                <form id="formEditarUsuario" novalidate>
                                    <div class="modal-body">
                                        <input type="hidden" id="eu_id" name="id" value="">
                                        <div id="formEditarUsuarioErro" class="alert alert-danger d-none"
                                            role="alert"></div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="eu_nome" class="form-label">Nome completo <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="eu_nome" name="nome"
                                                    required minlength="2" maxlength="200" autocomplete="name">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="eu_usuario" class="form-label">E-mail <span
                                                        class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="eu_usuario" name="usuario"
                                                    required maxlength="180" autocomplete="email">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="eu_telefone" class="form-label">Telefone</label>
                                                <input type="text" class="form-control" id="eu_telefone"
                                                    name="telefone" maxlength="40" placeholder="(00) 00000-0000"
                                                    autocomplete="tel">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="eu_perfil" class="form-label">Perfil <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select" id="eu_perfil" name="perfil" required>
                                                    <?php foreach ($perfis_form as $op): ?>
                                                    <option value="<?= htmlspecialchars($op['value'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= htmlspecialchars($op['label'], ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="eu_senha" class="form-label">Nova senha</label>
                                                <input type="password" class="form-control" id="eu_senha"
                                                    name="senha" minlength="8" maxlength="128"
                                                    autocomplete="new-password">
                                                <div class="form-text">Deixe em branco para manter a senha atual.
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="eu_senha2" class="form-label">Confirmar nova senha</label>
                                                <input type="password" class="form-control" id="eu_senha2"
                                                    name="senha_confirmacao" minlength="8" maxlength="128"
                                                    autocomplete="new-password">
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="eu_ativo"
                                                        name="ativo" checked>
                                                    <label class="form-check-label" for="eu_ativo">Utilizador
                                                        ativo</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary" id="btnSalvarEditarUsuario">
                                            <i class="fas fa-save me-1"></i> Guardar alterações
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                     <!-- FIM Modal: editar usuário -->
                    <?php endif; ?>
                </div>
            </div>
        </div>
</div>

                  
              
          
     




<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tabs
        const triggerTabList = document.querySelectorAll('.settings-nav .nav-link');
        triggerTabList.forEach(triggerEl => {
            new bootstrap.Tab(triggerEl);
        });

        // Salvar configurações (botão opcional na UI)
        const btnSalvarCfg = document.getElementById('btnSalvarConfiguracoes');
        if (btnSalvarCfg) {
            btnSalvarCfg.addEventListener('click', function() {
                const toast = document.createElement('div');
                toast.className =
                    'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
                toast.style.zIndex = '1060';
                toast.innerHTML =
                    '<i class="fas fa-check-circle me-2"></i> Configurações salvas com sucesso!' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            });
        }

        // Alternar entre abas e salvar estado
        triggerTabList.forEach(tab => {
            tab.addEventListener('click', function() {
                localStorage.setItem('ultimaAbaConfig', this.getAttribute('href'));
            });
        });

        // Restaurar última aba acessada
        const ultimaAba = localStorage.getItem('ultimaAbaConfig');
        if (ultimaAba) {
            const tab = document.querySelector(`.settings-nav .nav-link[href="${ultimaAba}"]`);
            if (tab) {
                let inst = bootstrap.Tab.getInstance(tab);
                if (!inst) {
                    inst = new bootstrap.Tab(tab);
                }
                inst.show();
            }
        }

        const formNovo = document.getElementById('formNovoUsuario');
        const erroBox = document.getElementById('formNovoUsuarioErro');
        const btnSalvarNovo = document.getElementById('btnSalvarNovoUsuario');

        function getCsrf() {
            if (typeof getDashboardCsrfToken === 'function') {
                return getDashboardCsrfToken();
            }
            const m = document.querySelector('meta[name="csrf-token"]');
            return m ? (m.getAttribute('content') || '') : '';
        }

        if (formNovo) {
            formNovo.addEventListener('submit', async function(e) {
                e.preventDefault();
                erroBox.classList.add('d-none');
                erroBox.textContent = '';

                const payload = {
                    nome: document.getElementById('nu_nome').value.trim(),
                    usuario: document.getElementById('nu_usuario').value.trim(),
                    telefone: document.getElementById('nu_telefone').value.trim(),
                    perfil: document.getElementById('nu_perfil').value,
                    senha: document.getElementById('nu_senha').value,
                    senha_confirmacao: document.getElementById('nu_senha2').value,
                    ativo: document.getElementById('nu_ativo').checked
                };

                btnSalvarNovo.disabled = true;
                try {
                    const res = await fetch('/usuarios/usuario', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': getCsrf()
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json().catch(function() {
                        return {};
                    });
                    if (!res.ok) {
                        erroBox.textContent = data.erro || 'Não foi possível salvar.';
                        erroBox.classList.remove('d-none');
                        return;
                    }
                    const modalEl = document.getElementById('modalNovoUsuario');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    }
                    window.location.reload();
                } catch (err) {
                    erroBox.textContent = 'Erro de rede. Tente novamente.';
                    erroBox.classList.remove('d-none');
                } finally {
                    btnSalvarNovo.disabled = false;
                }
            });
        }

        const modalNovoEl = document.getElementById('modalNovoUsuario');
        if (modalNovoEl && erroBox) {
            modalNovoEl.addEventListener('hidden.bs.modal', function() {
                const f = document.getElementById('formNovoUsuario');
                if (f) {
                    f.reset();
                }
                const nuAtivo = document.getElementById('nu_ativo');
                if (nuAtivo) {
                    nuAtivo.checked = true;
                }
                erroBox.classList.add('d-none');
                erroBox.textContent = '';
            });
        }

        const formEdit = document.getElementById('formEditarUsuario');
        const erroEdit = document.getElementById('formEditarUsuarioErro');
        const btnSalvarEdit = document.getElementById('btnSalvarEditarUsuario');

        document.querySelectorAll('.btn-editar-usuario').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const raw = this.getAttribute('data-user');
                let u;
                try {
                    u = JSON.parse(raw);
                } catch (e) {
                    return;
                }
                const idEl = document.getElementById('eu_id');
                if (!idEl) {
                    return;
                }
                idEl.value = u.id;
                document.getElementById('eu_nome').value = u.nome || '';
                document.getElementById('eu_usuario').value = u.usuario || '';
                document.getElementById('eu_telefone').value = u.telefone || '';
                document.getElementById('eu_perfil').value = u.perfil || 'operador';
                document.getElementById('eu_ativo').checked = !!u.ativo;
                document.getElementById('eu_senha').value = '';
                document.getElementById('eu_senha2').value = '';
                if (erroEdit) {
                    erroEdit.classList.add('d-none');
                    erroEdit.textContent = '';
                }
                const modalEl = document.getElementById('modalEditarUsuario');
                if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            });
        });

        if (formEdit && erroEdit && btnSalvarEdit) {
            formEdit.addEventListener('submit', async function(e) {
                e.preventDefault();
                erroEdit.classList.add('d-none');
                erroEdit.textContent = '';

                const senhaVal = document.getElementById('eu_senha').value;
                const senha2Val = document.getElementById('eu_senha2').value;
                const payload = {
                    id: parseInt(document.getElementById('eu_id').value, 10) || 0,
                    nome: document.getElementById('eu_nome').value.trim(),
                    usuario: document.getElementById('eu_usuario').value.trim(),
                    telefone: document.getElementById('eu_telefone').value.trim(),
                    perfil: document.getElementById('eu_perfil').value,
                    ativo: document.getElementById('eu_ativo').checked,
                    senha: senhaVal,
                    senha_confirmacao: senha2Val
                };

                btnSalvarEdit.disabled = true;
                try {
                    const res = await fetch('/usuarios/usuario/atualizar', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': getCsrf()
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json().catch(function() {
                        return {};
                    });
                    if (!res.ok) {
                        erroEdit.textContent = data.erro || 'Não foi possível guardar.';
                        erroEdit.classList.remove('d-none');
                        return;
                    }
                    const modalEl = document.getElementById('modalEditarUsuario');
                    const inst = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
                    if (inst) {
                        inst.hide();
                    }
                    window.location.reload();
                } catch (err) {
                    erroEdit.textContent = 'Erro de rede. Tente novamente.';
                    erroEdit.classList.remove('d-none');
                } finally {
                    btnSalvarEdit.disabled = false;
                }
            });
        }

        const modalEditEl = document.getElementById('modalEditarUsuario');
        if (modalEditEl && erroEdit) {
            modalEditEl.addEventListener('hidden.bs.modal', function() {
                const f = document.getElementById('formEditarUsuario');
                if (f) {
                    f.reset();
                }
                const euAtivo = document.getElementById('eu_ativo');
                if (euAtivo) {
                    euAtivo.checked = true;
                }
                erroEdit.classList.add('d-none');
                erroEdit.textContent = '';
            });
        }
    });
</script>