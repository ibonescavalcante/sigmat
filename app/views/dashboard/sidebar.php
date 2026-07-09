<?php
use App\models\Usuario;

$uidSidebar = (int) ($_SESSION['user']['id'] ?? 0);
$mostrarMenuConfiguracoes =1;// $uidSidebar > 0 && Usuario::perfilPorId($uidSidebar) === 'administrador';
?>
<!-- Sidebar: coluna flex + painel sticky para não rolar com o conteúdo -->
<div class="col-lg-2 col-md-3 p-0 d-none d-md-flex flex-column border">
    <div class="sidebar sidebar-sticky w-100">
    <div class="p-3">
        <h5 class="text-uppercase text-muted small fw-bold">Navegação</h5>
    </div>
    <ul class="nav flex-column" id="sidebarNav">
        <li class="nav-item">
            <a class="nav-link active" href="/">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>
        </li>
       
        <li class="nav-item">
            <a class="nav-link" href="/servidores">
                <i class="fas fa-users me-2"></i> Servidores
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/carteirinha">
                <i class="fas fa-file-alt me-2"></i> Carteirinhas
            </a>
        </li>
        <!--li class="nav-item">
            <a class="nav-link" href="/relatorios">
                <i class="fas fa-chart-bar me-2"></i> Relatórios
            </a>
        </li-->
        <?php if ($mostrarMenuConfiguracoes): ?>
        <li class="nav-item">
            <a class="nav-link" href="/usuarios">
                <i class="fas fa-users-cog me-2"></i> Usuarios
            </a>
        </li>
        <?php endif; ?>
    </ul>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('#sidebarNav .nav-link');
        const path = window.location.pathname;

        links.forEach(link => {
            if (link.getAttribute('href') === path) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    });
</script>