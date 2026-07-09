<?php
//var_dump($_SESSION['user']); die;
use App\helpers\Branding;
use App\middleware\SessionSecurity;

if (SessionSecurity::estaLogadoDashboard()) {
    SessionSecurity::ensureDashboardCsrfToken();
}
$csrfToken = SessionSecurity::obterTokenCsrfDashboard();
$branding = Branding::get();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($csrfToken !== null): ?>
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <title><?= htmlspecialchars($branding['titulo_documento'], ENT_QUOTES, 'UTF-8') ?></title>
    <script>
    function getDashboardCsrfToken() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? (m.getAttribute('content') || '') : '';
    }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0C326F;
            --primary-dark: #0d5bb5;
            --primary-light: #e8f0fe;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-400: #ced4da;
            --gray-500: #adb5bd;
            --gray-600: #6c757d;
            --gray-700: #495057;
            --gray-800: #343a40;
            --gray-900: #212529;
        }

        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 70px;
            /* ajuste conforme a altura da sua navbar */
        }

        /* Navbar aprimorada */
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            /* box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); */
            box-shadow: -10px 9px 10px rgba(0, 0, 0, 0.2);
            padding: 0.5rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            color: white !important;
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-right: 1.5rem;
        }

        .navbar-brand:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .brand-icon {
            font-size: 2.2rem;
            margin-right: 0.75rem;
            filter: drop-shadow(0 2px 2px rgba(0, 0, 0, 0.2));
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .brand-title {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .brand-subtitle {
            font-size: 0.85rem;
            font-weight: 400;
            opacity: 0.9;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            margin: 0 0.15rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* .navbar-nav .nav-link i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        } */

        .navbar-toggler {
            border: none;
            color: white;
            padding: 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 0.5rem;
            margin-top: 0.5rem;
        }

        .dropdown-item {
            padding: 0.6rem 1rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .dropdown-item i {
            width: 20px;
            margin-right: 0.75rem;
            color: var(--gray-600);
        }

        .dropdown-divider {
            margin: 0.5rem 0;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        .user-avatar i {
            font-size: 14px;
            /* menor para centralizar melhor */
        }

        /* Restante do CSS permanece igual */
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #e0e0e0;
            font-weight: 600;
            padding: 15px 20px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .sidebar {
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Painel da sidebar fixo na viewport (abaixo da navbar fixed), conteúdo principal rola */
        @media (min-width: 768px) {
            .sidebar-sticky {
                position: sticky;
                top: 70px;
                align-self: flex-start;
                height: calc(100vh - 70px);
                max-height: calc(100vh - 70px);
                overflow-y: auto;
                z-index: 1010;
            }
        }

        .sidebar .nav-link {
            color: #495057;
            padding: 12px 20px;
            border-left: 3px solid transparent;
        }

        .sidebar .nav-link:hover {
            background-color: #f8f9fa;
            border-left: 3px solid var(--primary);
        }

        .sidebar .nav-link.active {
            background-color: #e8f0fe;
            color: var(--primary);
            border-left: 3px solid var(--primary);
        }

        .dashboard-card {
            transition: transform 0.3s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }

        .processo-card {
            transition: all 0.3s;
        }

        .processo-card:hover {
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        .document-list {
            list-style: none;
            padding: 0;
        }

        .document-list li {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .document-list li:last-child {
            border-bottom: none;
        }

        .step-progress {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }

        .step-progress::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e0e0e0;
            z-index: 1;
        }

        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
        }

        .step.active {
            background-color: var(--primary);
            color: white;
        }

        .step.completed {
            background-color: var(--success);
            color: white;
        }

        .step-label {
            position: absolute;
            top: 35px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            font-size: 0.8rem;
        }


        .nav-link[disabled] {
            pointer-events: none;
            /* impede o clique */
            opacity: 0.5;
            /* deixa visualmente desabilitado */
            cursor: default;
            /* muda o cursor */
            color: #6c757d !important;
            /* cinza padrão de desabilitado */
        }

        /* Opcional: deixa o ícone também cinza */
        .nav-link[disabled] i {
            color: #6c757d;
        }

        /* Estilo especifico da modla troca senha  */
        .password-strength-meter {
            height: 5px;
            margin-top: 5px;
            border-radius: 5px;
            background-color: var(--gray-300);
            overflow: hidden;
        }

        .password-strength-meter .strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s, background-color 0.3s;
        }

        .password-strength-meter.weak .strength-bar {
            width: 25%;
            background-color: var(--danger);
        }

        .password-strength-meter.fair .strength-bar {
            width: 50%;
            background-color: var(--warning);
        }

        .password-strength-meter.good .strength-bar {
            width: 75%;
            background-color: var(--info);
        }

        .password-strength-meter.strong .strength-bar {
            width: 100%;
            background-color: var(--success);
        }

        .password-requirements {
            font-size: 0.85rem;
            color: var(--gray-600);
            margin-top: 10px;
        }

        .password-requirements ul {
            padding-left: 20px;
            margin-bottom: 0;
        }

        .password-requirements li {
            margin-bottom: 5px;
        }

        .password-requirements li.valid {
            color: var(--success);
        }

        .password-requirements li.valid::before {
            content: "✓ ";
            font-weight: bold;
        }

        .password-requirements li.invalid {
            color: var(--gray-500);
        }

        .password-requirements li.invalid::before {
            content: "✗ ";
            font-weight: bold;
        }

        .input-group-text {
            background-color: var(--gray-100);
            border: 1px solid var(--gray-300);
        }

        .toggle-password {
            cursor: pointer;
            background-color: var(--gray-100);
            border: 1px solid var(--gray-300);
            border-left: none;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 12px;
            color: var(--gray-600);
        }

        .toggle-password:hover {
            background-color: var(--gray-200);
            color: var(--gray-800);
        }

        .modal-content {
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: none;
        }

        .modal-header {
            background-color: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 1.25rem;
        }

        .modal-header .modal-title {
            display: flex;
            align-items: center;
            font-weight: 600;
        }

        .modal-header .modal-title i {
            margin-right: 10px;
            color: var(--primary);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #e0e0e0;
            padding: 1rem 1.5rem;
        }


        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }

            .sidebar-sticky {
                position: static;
                height: auto;
                max-height: none;
                overflow-y: visible;
            }

            .step-label {
                font-size: 0.7rem;
            }

            .navbar-brand {
                margin-right: 0;
                padding: 0.25rem 0.5rem;
            }

            .brand-title {
                font-size: 1.1rem;
            }

            .brand-subtitle {
                font-size: 0.75rem;
            }
        }
    </style>
</head>

<body>
   
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <i class="<?= htmlspecialchars($branding['icone_classes'], ENT_QUOTES, 'UTF-8') ?> brand-icon" aria-hidden="true"></i>
                    <div class="brand-text">
                        <span class="brand-title"><?= htmlspecialchars($branding['nome_curto'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="brand-subtitle"><?= htmlspecialchars($branding['subtitulo'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarContent">


                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown"
                                role="button" data-bs-toggle="dropdown">
                                <div class="user-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <?php echo isset($_SESSION['user']['nome']) ? htmlspecialchars($_SESSION['user']['nome']) : 'Usuário'; ?>

                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#changePasswordModal"><i class="fas fa-key me-2"></i>Alterar
                                        Senha</a></li>

                                <li><a class="dropdown-item" href="/logout"><i
                                            class="fas fa-sign-out-alt me-2"></i>Sair</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <!-- Conteúdo da página (exemplo) -->

    <!-- Modal trocar senha--->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">
                        <i class="fas fa-key"></i> Alterar Senha
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm">
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Senha Atual</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="currentPassword" required>
                                <span class="toggle-password" data-target="currentPassword">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Nova Senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="newPassword" required>
                                <span class="toggle-password" data-target="newPassword">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
       
                        </div>

                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label">Confirmar Nova Senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmPassword" required>
                                <span class="toggle-password" data-target="confirmPassword">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div class="invalid-feedback" id="passwordMatchError">
                                As senhas não coincidem.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="submitPasswordChange">
                        <i class="fas fa-save me-2"></i>Alterar Senha
                    </button>
                </div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<script>
    // Elementos do modal de senha
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    const confirmPassword = document.getElementById('confirmPassword');
    const submitPasswordChange = document.getElementById('submitPasswordChange');
    const changePasswordModal = document.getElementById('changePasswordModal');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
    });

    function validateForm() {
        let isValid = true;

        // Validar senha atual
        if (!currentPassword.value) {
            currentPassword.classList.add('is-invalid');
            isValid = false;
        } else {
            currentPassword.classList.remove('is-invalid');
        }

        // Validar nova senha
        if (!newPassword.value) {
            newPassword.classList.add('is-invalid');
            isValid = false;
        } else {
            newPassword.classList.remove('is-invalid');
        }

        // Validar confirmação de senha
        if (!validatePasswordMatch()) {
            isValid = false;
        }

        return isValid;
    }
    submitPasswordChange.addEventListener('click', async function() {
        if (validateForm()) {
            submitPasswordChange.disabled = true;
            submitPasswordChange.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Alterando...';

            const dados = {
                senha_atual: document.getElementById('currentPassword').value,
                nova_senha: document.getElementById('newPassword').value
            };
            const bodyContent = new FormData();
            bodyContent.append("senha_atual", dados.senha_atual);
            bodyContent.append("nova_senha", dados.nova_senha);

            try {
                const response = await fetch("/api/alterar-senha", {
                    method: "POST",
                    body: bodyContent,
                    credentials: "same-origin",
                    headers: {
                        "X-CSRF-Token": getDashboardCsrfToken(),
                        "Accept": "application/json"
                    }
                });

                const data = await response.json();
                console.log(response);

                if (response.ok && data.sucesso) {
                    alert(data.mensagem);
                    const modal = bootstrap.Modal.getInstance(changePasswordModal);
                    modal.hide();
               
                } else {
                    alert("Erro: " + (data.erro || "Não foi possível concluir a operação."));
                    submitPasswordChange.disabled = false;
                    submitPasswordChange.innerHTML = '<i class="fas fa-save me-2"></i>Alterar Senha';
                }
            } catch (error) {
                console.error("Erro ao enviar avaliação:", error);
                alert("Ocorreu um erro de comunicação com o servidor.");           }


           
        }
    });

    confirmPassword.addEventListener('input', function() {
        validatePasswordMatch();
    });

    function validatePasswordMatch() {
        if (newPassword.value && confirmPassword.value) {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                passwordMatchError.style.display = 'block';
                return false;
            } else {
                confirmPassword.classList.remove('is-invalid');
                passwordMatchError.style.display = 'none';
                return true;
            }
        }
        return false;
    }
</script>