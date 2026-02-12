<?php
  require_once __DIR__ . '/../../../app/core/init.php';
  require_once __DIR__ . '/../../../app/core/sessao_segura.php';

  // Verifica se o usuário está logado
  if (!$usuario) {
      header('Location: ../index.php');
      exit;
  }

  // Permissões
  $permiteEditar = ($usuario['idPermissaoUsuario'] == 1); // apenas administradores
  $permiteVisualizar = in_array($usuario['idPermissaoUsuario'], [1,2]); // Adm ou coordenador

  if (!$permiteVisualizar) {
      echo "<script>alert('Acesso negado.'); window.location.href='dashboard.php';</script>";
      exit;
  }

  // Busca cursos (com filtro opcional)
  $filtro = trim($_GET['filtro'] ?? '');
  $where = '';
  $params = [];

  if ($filtro !== '') {
      $where = "WHERE c.nomeCurso LIKE :filtroNome OR c.descricaoCurso LIKE :filtroDesc";
      $params['filtroNome'] = "%$filtro%";
      $params['filtroDesc'] = "%$filtro%";
  }

  $stmt = $pdo->prepare("
      SELECT c.idCurso, c.nomeCurso, c.descricaoCurso, c.statusCurso, c.imagemCurso, m.descricaoMinisterio
      FROM asy_cursos c
      LEFT JOIN asy_ministerios m ON c.idMinisterio = m.idMinisterio
      $where
      ORDER BY c.nomeCurso ASC
  ");
  $stmt->execute($params);
  $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow">

    <title>Lista de Cursos - ASY Gospel Church</title>

    <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="<?= ASSETS_URL ?>/plugins/fontawesome-free/css/all.min.css" />
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/dist/css/adminlte.min.css" />
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/estilo_lista-cursos.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" />

  </head>
  <body class="hold-transition sidebar-mini">
    <div class="wrapper">
      <?php require_once __DIR__ . '/../../../app/views/navbar.php'; ?>
      <?php require_once __DIR__ . '/../../../app/views/sidebar.php'; ?>

      <div class="content-wrapper">
        <section class="content-header">
          <div class="container-fluid">
            <h4 class="text-center"><i class="fas fa-graduation-cap"></i> Lista de Cursos</h4>
            <br>

            <div class="search-register-container">
              <div class="search-box">
                <input type="search" id="tableSearch" placeholder="Buscar por nome ou descrição..." />
                <i class="fas fa-search"></i>
              </div>
              <div class="btn-group" role="group">
                <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Relatório
                </button>
                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                  <a class="dropdown-item" href="#"> Aulas</a>
                  <a class="dropdown-item" href="#"> Notas</a>
                </div>
              </div>

              <?php if($permiteEditar): ?>

              <button id="btnCadastrar" onclick="window.location.href='cadastrar_curso.php'">
                <i class="fas fa-plus-circle"></i> Cadastrar
              </button>


              <?php endif; ?>
            </div>

            <div class="table-responsive">
              <table id="cursosTable" class="table table-bordered table-striped">
                <thead class="table-dark">
                  <tr>
                    <th>Curso</th>
                    <th>Descrição</th>
                    <th>Ministério</th>
                    <?php if($permiteEditar): ?><th>Ações</th><?php endif; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php if(count($cursos) > 0): ?>
                  <?php foreach($cursos as $c): ?>
                  <?php
                    $isInativo = ($c['statusCurso'] === 'inativo');
                  ?>
                  <tr class="<?= $isInativo ? 'usuario-inativo' : '' ?>" data-id="<?= $c['idCurso'] ?>">
                    <td><b><?= htmlspecialchars($c['nomeCurso']) ?></b></td>
                    <td><?= htmlspecialchars($c['descricaoCurso']) ?></td>
                    <td><?= htmlspecialchars($c['descricaoMinisterio'] ?: '-') ?></td>
                    
                    <?php if($permiteEditar): ?>

                    <td class="text-center">
                      <a href="editar_curso.php?idCurso=<?= $c['idCurso'] ?>" class="btn btn-sm btn-outline-info <?= $isInativo?'disabled':'' ?>">
                        <i class="fas fa-edit"></i> Editar
                      </a>
                      <button class="btn btn-sm btn-outline-<?= $isInativo?'success':'danger' ?> btn-toggle-status"
                        data-id="<?= $c['idCurso'] ?>"
                        data-status="<?= $isInativo?'ativo':'inativo' ?>">
                        <i class="fas fa-<?= $isInativo?'redo':'times' ?>"></i>
                        <?= $isInativo?'Reativar':'Inativar' ?>
                      </button>
                      <a href="upload_video.php?idCurso=<?= $c['idCurso'] ?>" class="btn btn-sm btn-outline-warning <?= $isInativo?'disabled':'' ?>">
                        <i class="fas fa-video"></i> Subir vídeos
                      </a>
                    </td>

                    <?php endif; ?>

                  </tr>

                  <?php endforeach; ?>

                  <?php else: ?>
                      <tr><td colspan="<?= $permiteEditar?6:5 ?>" class="text-center">Nenhum curso encontrado.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
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

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.colVis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script src="<?= ASSETS_URL ?>/js/script_lista-curso.js?v=<?= time() ?>"></script>

  </body>
</html>
