<?php

require_once __DIR__ . '/../core/init.php';

function cacheBusterUrl($url) {
    $sep = (strpos($url, '?') === false) ? '?' : '&';
    return $url . $sep . 'v=' . time();
}

?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="#" class="brand-link">
    <img src="<?= ASSETS_URL ?>/img/logoASY_circulo.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light">ASY Gospel Church</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <?php
          $fotoPerfil = !empty($usuario['foto_perfil'])
            ? BASE_URL . '/proxy/usuario/foto.php?id=' . $usuario['idUsuario']
            : BASE_URL . '/assets/img/user-profile-img-default.png';
          ?>
        <img id="fotoPerfil-sidebar"
        src="<?= htmlspecialchars(cacheBusterUrl($fotoPerfil)) ?>"
        class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info">
        <a href="<?= ADMIN_URL ?>/profile.php" class="d-block"><?= $usuario['nomeCompletoUsuario'] ?? 'Visitante' ?></a>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
          <a href="<?= PUBLIC_URL ?>/painel.php" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              Painel
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= ASYEAD_URL ?>/cursos.php" class="nav-link">
            <i class="nav-icon fas fa-chalkboard-teacher"></i>
            <p>
              Cursos
            </p>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</aside>
