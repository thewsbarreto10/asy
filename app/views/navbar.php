<?php

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/sessao_segura.php';

?>

<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">

    <?php if ($usuario && $usuario['idPermissaoUsuario'] == 1): // Só admins ?>
    <li class="nav-item">
      <a class="nav-link" href="<?= PAGES_URL ?>/admin/restrito.php" role="button">
        <i class="fas fa-user-shield"></i>
      </a>
    </li>
    <?php endif; ?>

    <li class="nav-item">
      <a id="btnLogout" class="nav-link" href="#" role="button">
        <i class="fas fa-sign-out-alt"></i>
      </a>
    </li>

  </ul>
</nav>
<!-- /.navbar -->
