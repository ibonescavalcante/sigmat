<?= $this->insert('dashboard/header') ?>
<div class="container-fluid">
    <?php if (isset($_SESSION['erro']) && $_SESSION['erro'] !== ''): ?>
    <div class="row">
        <div class="col-12 px-4 pt-3">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?= htmlspecialchars((string) $_SESSION['erro'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>
    <div class="row">
        <?= $this->insert('dashboard/sidebar') ?>
        <?= $this->section('content') ?>
    </div>
</div>


<script>
// Script para controle das abas e funcionalidades
document.addEventListener('DOMContentLoaded', function() {
    // Controle de abas na sidebar
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Simulação de carregamento de dados
    console.log('Sistema PSS carregado com sucesso!');

    // Exemplo de funcionalidade: alternar visibilidade de senha
    const togglePassword = document.querySelector('#togglePassword');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const password = document.querySelector('#password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }
});
</script>


</body>

</html>