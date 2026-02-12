<?php
require_once __DIR__ . '/../app/core/init.php';        // inicializa config e session
require_once __DIR__ . '/../app/core/sessao_segura.php'; // valida sessão
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php
require_once __DIR__ . '/../app/views/navbar.php';
require_once __DIR__ . '/../app/views/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <!-- Conteúdo específico -->
    </div>
  </section>
</div>

<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>/js/logout.js"></script>

</body>
</html>
