<?php
  require_once __DIR__ . '/../../../app/core/init.php';        // inicializa config e session
  require_once __DIR__ . '/../../../app/core/sessao_segura.php'; // valida sessão
  require_once __DIR__ . '/../../../app/processamento/select_db.php'; //busca informações no banco de dados e printa na tela

  // Verifica se o usuário está logado
  if (!$usuario) {
      header('Location: /asy/');
      exit;
  }

?>


<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>Painel de Administrador - ASY Gospel Church</title>

    <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">
    
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/dist/css/adminlte.min.css">
  </head>
  <body class="hold-transition sidebar-mini">
    <div class="wrapper">

    <?php
      require_once __DIR__ . '/../../../app/views/navbar.php';
      require_once __DIR__ . '/../../../app/views/sidebar.php';
    ?>

      <!-- Content Wrapper -->
      <div class="content-wrapper">
        <section class="content-header">
          <div class="container-fluid">
            <!-- Conteúdo específico -->
            <div class="row">
              <!-- Exemplo de cards -->
              <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                  <div class="inner"><h3><?= $totalUsuarios ?></h3><p>usuários</p></div>
                  <div class="icon"><i class="fas fa-users"></i></div>
                  <a href="<?= PAGES_URL ?>/admin/lista_usuarios.php" class="small-box-footer">Ver usuários <i class="fas fa-arrow-circle-right"></i></a>
                </div>
              </div>
              <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                  <div class="inner"><h3><?= $totalEventos ?></h3><p>eventos</p></div>
                  <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                  <a href="<?= PAGES_URL ?>/eventos/lista_eventos.php" class="small-box-footer">Cadastrar eventos <i class="fas fa-arrow-circle-right"></i></a>
                </div>
              </div>
              <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                  <div class="inner"><h3><?= $totalCursos ?></h3><p>cursos</p></div>
                  <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
                  <a href="<?= PAGES_URL ?>/asy-ead/lista_cursos.php" class="small-box-footer">Ver cursos <i class="fas fa-arrow-circle-right"></i></a>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <script src="<?= ASSETS_URL ?>/plugins/jquery/jquery.min.js"></script>
    <script src="<?= ASSETS_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= ASSETS_URL ?>/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= ASSETS_URL ?>/js/logout.js"></script>

  </body>
</html>
