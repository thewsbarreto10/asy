<?php
  require_once __DIR__ . '/../../../app/core/init.php';
  require_once __DIR__ . '/../../../app/core/sessao_segura.php';

  // Verifica se o usuário está logado
  if (!$usuario) {
      header('Location: /asy/');
      exit;
  }

  // Define permissões
  $permiteEditar = ($usuario['idPermissaoUsuario'] == 1); // apenas administradores
  $permiteVisualizar = in_array($usuario['idPermissaoUsuario'], [1, 2]); // Adm ou coordenador

  if (!$permiteVisualizar) {
      echo "<script>alert('Acesso negado.'); window.location.href='dashboard.php';</script>";
      exit;
  }

  // Busca usuários (com filtro se enviado)
  $filtro = trim($_GET['filtro'] ?? '');
  $where = '';
  $params = [];

  if ($filtro !== '') {
      $where = "WHERE u.nomeCompletoUsuario LIKE :filtroNome OR u.emailUsuario LIKE :filtroEmail";
      $params['filtroNome'] = "%{$filtro}%";
      $params['filtroEmail'] = "%{$filtro}%";
  }

  // Incluímos u.foto_perfil
  $stmt = $pdo->prepare("
      SELECT u.idUsuario, u.nomeCompletoUsuario, u.emailUsuario, u.foto_perfil, p.descricaoPermissaoUsuario
      FROM asy_usuarios u
      LEFT JOIN asy_PermissaoUsuario p ON u.idPermissaoUsuario = p.idPermissaoUsuario
      $where
      ORDER BY u.nomeCompletoUsuario ASC
  ");
  $stmt->execute($params);
  $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow">

    <title>Lista de Usuários - ASY Gospel Church</title>

    <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="<?= ASSETS_URL ?>/plugins/fontawesome-free/css/all.min.css" />
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/dist/css/adminlte.min.css" />
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/estilo_lista-usuarios.css">

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
            <h4 align="center"><i class="fas fa-users"></i> Lista de Usuários</h4>
            <br>

            <div class="search-register-container">
              <div class="search-box">
                <input type="search" id="tableSearch" placeholder="Buscar por nome ou email..." />
                <i class="fas fa-search"></i>
              </div>
              <button id="btnCadastrar" onclick="window.location.href='cadastrar_usuario.php'">
                <i class="fas fa-user-plus"></i> Cadastrar
              </button>
            </div>

            <div class="table-responsive">
              <table id="usuariosTable" class="table table-bordered table-striped">
                <thead class="table-dark">
                  <tr>
                    <th></th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Permissão</th>
                    <?php if ($permiteEditar): ?>
                      <th>Ações</th>
                    <?php endif; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($usuarios) > 0): ?>
                    <?php foreach ($usuarios as $u): ?>
                      <?php
                        $foto = !empty($u['foto_perfil'])
                            ? BASE_URL . '/proxy/usuario/foto.php?id=' . $u['idUsuario']
                            : BASE_URL . '/assets/img/user-profile-img-default.png';

                        // Verifica status do usuário
                        $stmtStatus = $pdo->prepare("SELECT statusUsuario FROM asy_usuarios WHERE idUsuario = ?");
                        $stmtStatus->execute([$u['idUsuario']]);
                        $status = $stmtStatus->fetchColumn();
                        $isInativo = ($status === 'inativo');
                      ?>
                      <tr class="<?= $isInativo ? 'usuario-inativo' : '' ?>" data-id="<?= $u['idUsuario'] ?>">
                        <td class="user-photo-cell">
                          <img src="<?= htmlspecialchars($foto) ?>" alt="Foto"
                              class="user-photo" width="36" height="36">
                        </td>
                        <td><?= htmlspecialchars($u['nomeCompletoUsuario']) ?></td>
                        <td><?= htmlspecialchars($u['emailUsuario']) ?></td>
                        <td><?= htmlspecialchars($u['descricaoPermissaoUsuario']) ?></td>

                        <?php if ($permiteEditar): ?>
                          <td align="center">
                            <a href="editar_usuario.php?id=<?= $u['idUsuario'] ?>"
                              class="btn btn-sm btn-outline-info <?= $isInativo ? 'disabled' : '' ?>"
                              <?= $isInativo ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                              <i class="fas fa-edit"></i> Editar
                            </a>
                            <button class="btn btn-sm btn-outline-<?= $isInativo ? 'success' : 'danger' ?> btn-toggle-status"
                                    data-id="<?= $u['idUsuario'] ?>"
                                    data-status="<?= $isInativo ? 'ativo' : 'inativo' ?>">
                              <i class="fas fa-<?= $isInativo ? 'redo' : 'user-slash' ?>"></i>
                              <?= $isInativo ? 'Reativar' : 'Inativar' ?>
                            </button>
                          </td>
                        <?php endif; ?>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="<?= $permiteEditar ? '5' : '4' ?>" class="text-center">Nenhum usuário encontrado.</td>
                    </tr>
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

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.colVis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script>
    $(document).ready(function () {
      var table = $('#usuariosTable').DataTable({
        language: { url: '<?= ASSETS_URL ?>/js/datatables/pt-BR.json' },
        dom: 'Bfrtip',
        buttons: [
          { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar' },
          { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV' },
          { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel' },
          { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF' },
          { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir' },
          { extend: 'colvis', text: '<i class="fas fa-columns"></i> Colunas' }
        ],
        ordering: true,
        paging: true,
        info: true,
        responsive: true,
        lengthChange: true,
        searching: true,
        initComplete: function () {
          $('.dataTables_filter').hide();
          $('#tableSearch').on('keyup change clear', function () {
            if (table.search() !== this.value) {
              table.search(this.value).draw();
            }
          });
        }
      });

      // 🔹 Ação de Inativar/Reativar usuário
      $(document).on('click', '.btn-toggle-status', function () {
        const id = $(this).data('id');
        const novoStatus = $(this).data('status');
        const acao = (novoStatus === 'inativo') ? 'inativar' : 'reativar';

        Swal.fire({
          title: 'Confirmação',
          text: `Deseja realmente ${acao} este usuário?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sim',
          cancelButtonText: 'Cancelar'
        }).then(result => {
          if (result.isConfirmed) {
            $.post('<?= BASE_URL ?>/proxy/usuario/status_usuario.php', {
              idUsuario: id,
              statusUsuario: novoStatus
            }, function (resp) {
              if (resp.success) {
                Swal.fire('Sucesso', resp.mensagem, 'success').then(() => location.reload());
              } else {
                Swal.fire('Erro', resp.error || 'Falha ao atualizar status.', 'error');
              }
            }, 'json').fail(() => {
              Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
            });
          }
        });
      });
    });
    </script>
  </body>
</html>
