<?php
use App\helpers\Branding;
use App\middleware\SessionSecurity;

if (SessionSecurity::estaLogadoDashboard()) {
    SessionSecurity::ensureDashboardCsrfToken();
}
$csrfToken = SessionSecurity::obterTokenCsrfDashboard();
$branding  = Branding::get();

$erro = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#1351B4">
<title>Acessar - <?= htmlspecialchars($branding['nome_curto'], ENT_QUOTES, 'UTF-8') ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
  :root{
    --gov-blue-warm-vivid-70:#1351B4;
    --gov-blue-warm-vivid-80:#0C326F;
    --gov-blue-warm-vivid-90:#071D41;
    --gov-yellow-vivid:#FFCD07;
    --gov-green-cool-vivid:#168821;
    --gov-red-vivid:#E52207;
    --gov-gray-2:#F8F8F8;
    --gov-gray-10:#EEEEEE;
    --gov-gray-20:#CCCCCC;
    --gov-gray-60:#636363;
    --gov-gray-80:#333333;
  }
  *{box-sizing:border-box}
  html,body{height:100%}
  body{
    font-family:'Raleway','Rawline',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
    background:var(--gov-gray-2);
    color:var(--gov-gray-80);
    margin:0;
  }

  /* Top bar gov-like */
  .gov-topbar{
    background:var(--gov-blue-warm-vivid-90);
    color:#fff;
    font-size:.78rem;
    padding:.4rem 1rem;
    display:flex;justify-content:flex-end;gap:1rem;
  }
  .gov-topbar a{color:#fff;text-decoration:none;opacity:.85}
  .gov-topbar a:hover{opacity:1;text-decoration:underline}

  /* Header */
  .gov-header{
    background:#fff;
    border-bottom:4px solid var(--gov-yellow-vivid);
    padding:1rem 1.25rem;
    display:flex;align-items:center;gap:.75rem;
  }
  .gov-header .brand{
    font-weight:700;color:var(--gov-blue-warm-vivid-80);
    font-size:1.05rem;letter-spacing:.2px;
  }
  .gov-header .logo{
    width:38px;height:38px;border-radius:8px;
    background:var(--gov-blue-warm-vivid-70);
    color:#fff;display:grid;place-items:center;font-weight:700;
  }

  /* Layout */
  .login-wrap{
    min-height:calc(100vh - 120px);
    display:flex;align-items:center;justify-content:center;
    padding:2rem 1rem;
  }
  .login-card{
    width:100%;max-width:440px;background:#fff;
    border:1px solid var(--gov-gray-10);
    border-radius:12px;
    box-shadow:0 6px 24px rgba(7,29,65,.08);
    overflow:hidden;
  }
  .login-card .card-top{
    background:linear-gradient(135deg,var(--gov-blue-warm-vivid-70),var(--gov-blue-warm-vivid-80));
    color:#fff;padding:1.5rem 1.5rem 1.25rem;
  }
  .login-card .card-top h1{
    font-size:1.35rem;font-weight:700;margin:0 0 .25rem;
  }
  .login-card .card-top p{margin:0;opacity:.9;font-size:.9rem}

  .login-card .card-body{padding:1.5rem}

  .form-label{
    font-weight:600;color:var(--gov-gray-80);
    font-size:.9rem;margin-bottom:.35rem;
  }
  .form-control{
    border:1px solid var(--gov-gray-20);
    border-radius:8px;padding:.7rem .9rem;
    font-size:.95rem;height:auto;
    transition:border-color .15s, box-shadow .15s;
  }
  .form-control:focus{
    border-color:var(--gov-blue-warm-vivid-70);
    box-shadow:0 0 0 3px rgba(19,81,180,.18);
  }
  .input-group .btn-toggle{
    border:1px solid var(--gov-gray-20);
    border-left:none;background:#fff;color:var(--gov-gray-60);
    border-top-right-radius:8px;border-bottom-right-radius:8px;
  }
  .input-group .form-control{
    border-top-right-radius:0;border-bottom-right-radius:0;
  }

  .btn-gov{
    background:var(--gov-blue-warm-vivid-70);
    color:#fff;font-weight:600;
    border:none;border-radius:999px;
    padding:.75rem 1.25rem;width:100%;
    transition:background .15s, transform .05s;
  }
  .btn-gov:hover{background:var(--gov-blue-warm-vivid-80);color:#fff}
  .btn-gov:active{transform:translateY(1px)}

  .form-check-input:checked{
    background-color:var(--gov-blue-warm-vivid-70);
    border-color:var(--gov-blue-warm-vivid-70);
  }
  .form-check-label{font-size:.9rem;color:var(--gov-gray-60)}

  .alert-gov{
    background:#FDECEA;border:1px solid #F5C2BD;
    color:#8B1A0E;border-radius:8px;padding:.75rem .9rem;
    font-size:.9rem;display:flex;gap:.5rem;align-items:flex-start;
  }
  .alert-gov i{color:var(--gov-red-vivid);margin-top:2px}

  .help-link{
    color:var(--gov-blue-warm-vivid-70);
    text-decoration:none;font-weight:500;font-size:.88rem;
  }
  .help-link:hover{text-decoration:underline}

  /* Footer */
  .gov-footer{
    background:var(--gov-blue-warm-vivid-90);
    color:#fff;padding:1rem;text-align:center;font-size:.82rem;
  }
  .gov-footer .status{display:inline-flex;align-items:center;gap:.4rem}
  .gov-footer .dot{
    width:8px;height:8px;border-radius:50%;
    background:var(--gov-green-cool-vivid);
    box-shadow:0 0 0 3px rgba(22,136,33,.25);
  }

  /* Skip link (acessibilidade) */
  .skip{
    position:absolute;left:-999px;top:0;background:#000;color:#fff;
    padding:.5rem .75rem;z-index:1000;
  }
  .skip:focus{left:8px;top:8px}
    .gov-header .logo img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 25px;
}
</style>
</head>
<body>

<a href="#main" class="skip">Ir para o conteúdo</a>

<!--div class="gov-topbar">
  <a href="#"><i class="fa-solid fa-universal-access me-1"></i> Acessibilidade</a>
  <a href="#"><i class="fa-solid fa-circle-question me-1"></i> Ajuda</a>
</div-->

<header class="gov-header" role="banner">
 <div class="logo" aria-hidden="true" style="background: rgba(0, 0, 0, 0.125) !important;">
   <img src="assets/img/brasao.png" alt="Logo" >
  </div>
  <div>
    <div class="brand"><?= htmlspecialchars($branding['nome_curto'], ENT_QUOTES, 'UTF-8') ?></div>
    <div style="font-size:.78rem;color:var(--gov-gray-60)"> <?= htmlspecialchars($branding['subtitulo'], ENT_QUOTES, 'UTF-8') ?></div>
  </div>
</header>

<main id="main" class="login-wrap">
  <section class="login-card" aria-labelledby="loginTitle">
    <div class="card-top">
      <h1 id="loginTitle">Identifique-se</h1>
      <p>Informe seus dados de acesso para continuar.</p>
    </div>

    <div class="card-body">

      <?php if ($erro): ?>
        <div class="alert-gov mb-3" role="alert">
          <i class="fa-solid fa-circle-exclamation"></i>
          <div><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      <?php endif; ?>

      <form id="loginForm" method="POST"  novalidate autocomplete="on">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <div class="mb-3">
          <label for="username" class="form-label">Usuário</label>
          <input type="text" id="username" name="username"
                 class="form-control" required autocomplete="username"
                 placeholder="Digite seu usuário" autofocus>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Senha</label>
          <div class="input-group">
            <input type="password" id="password" name="password"
                   class="form-control" required autocomplete="current-password"
                   placeholder="Digite sua senha">
            <button type="button" id="togglePassword" class="btn btn-toggle"
                    aria-label="Mostrar ou ocultar senha">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="form-check">
            <input type="checkbox" id="rememberMe" name="remember" class="form-check-input">
            <label for="rememberMe" class="form-check-label">Lembrar-me</label>
          </div>
          <a href="/recuperar-senha" class="help-link">Esqueci minha senha</a>
        </div>

        <button type="submit" class="btn-gov">
          <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Entrar
        </button>
      </form>

    </div>
  </section>
</main>

<footer class="gov-footer">
  <span class="status"><span class="dot"></span> Sistema seguro · Online</span>
  · © <?= date('Y') ?> <?= htmlspecialchars($branding['titulo_documento'], ENT_QUOTES, 'UTF-8') ; ?> 
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const loginForm       = document.getElementById('loginForm');
  const togglePassword  = document.getElementById('togglePassword');
  const passwordInput   = document.getElementById('password');

  if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', function () {
      const isPwd = passwordInput.getAttribute('type') === 'password';
      passwordInput.setAttribute('type', isPwd ? 'text' : 'password');
      this.innerHTML = isPwd
        ? '<i class="fa-solid fa-eye-slash"></i>'
        : '<i class="fa-solid fa-eye"></i>';
    });
  }

  const savedUsername = localStorage.getItem('savedUsername');
  const rememberMe    = localStorage.getItem('rememberMe') === 'true';
  if (savedUsername && rememberMe) {
    const u = document.getElementById('username');
    const r = document.getElementById('rememberMe');
    if (u) u.value = savedUsername;
    if (r) r.checked = true;
  }

  const rememberEl = document.getElementById('rememberMe');
  if (rememberEl) {
    rememberEl.addEventListener('change', function () {
      if (this.checked) {
        localStorage.setItem('rememberMe', 'true');
      } else {
        localStorage.setItem('rememberMe', 'false');
        localStorage.removeItem('savedUsername');
      }
    });
  }

  if (loginForm) {
    loginForm.addEventListener('submit', function () {
       // alert('Formulário enviado!'); // Apenas para teste, remova ou comente esta linha em produção
      const remember = document.getElementById('rememberMe');
      const username = document.getElementById('username');
      if (remember && remember.checked && username && username.value) {
        localStorage.setItem('savedUsername', username.value);
      }
    });
  }
});
</script>
</body>
</html>
