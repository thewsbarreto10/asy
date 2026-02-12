<?php

  require_once __DIR__ . '/../app/core/init.php';
  require_once __DIR__ . '/../app/core/sessao_segura.php';

  // Verifica se o usuário está logado
  if (!$usuario) {
      header('Location: /asy/');
      exit;
  }

  // Buscar eventos do banco
  $eventos = $pdo->query("
      SELECT e.idEvento, e.nomeEvento, e.tipoEvento, e.dataHoraInicio, e.dataHoraFim,
            m.descricaoMinisterio AS ministerio,
            e.observacao,
            u1.nomeCompletoUsuario AS criadoPor,
            u2.nomeCompletoUsuario AS responsavel,
            e.statusEvento AS status
      FROM asy_eventos e
      LEFT JOIN asy_ministerios m ON e.idMinisterio = m.idMinisterio
      LEFT JOIN asy_usuarios u1 ON e.criadoPor = u1.idUsuario
      LEFT JOIN asy_usuarios u2 ON e.idResponsavel = u2.idUsuario
      ORDER BY e.dataHoraInicio ASC
  ")->fetchAll(PDO::FETCH_ASSOC);

  $totalUsuarios = $pdo->query("
    SELECT COUNT(*) FROM asy_usuarios WHERE statusUsuario = 'ativo'
  ")->fetchColumn(); // ← Pega apenas o valor (número) da contagem

?>

<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>Painel - ASY Gospel Church</title>

    <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="<?= ASSETS_URL ?>/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/dist/css/adminlte.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/estilo_painel.css">
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
            <h4 class="text-center">Painel</h4>
          </div>
        </section>

        <!-- Calendário + Eventos -->
        <section class="content">
          <div class="container-fluid">
            <div class="card">
              <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-calendar-alt mr-2"></i> Calendário e Eventos</h3>
                <div class="card-tools ml-auto">
                  <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Minimizar">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>

              <div class="card-body">
                <div class="row">
                  <!-- Lista de eventos -->
                  <div class="col-md-4">
                    <h5 class="text-success mb-3"><i class="fas fa-list mr-1"></i> Eventos do Mês</h5>
                    <ul id="lista-eventos" class="list-group">
                      <?php foreach ($eventos as $ev): ?>
                        <li class="list-group-item list-group-item-action" style="cursor:pointer"
                            data-toggle="modal" data-target="#modalEvento"
                            data-event='<?= json_encode($ev, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                          <?= date('d/m/Y H:i', strtotime($ev['dataHoraInicio'])) ?> - <?= htmlspecialchars($ev['nomeEvento']) ?>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </div>

                  <!-- Calendário -->
                  <div class="col-md-8">
                    <div id="calendar" style="min-height:450px;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- Modal Evento -->
      <div class="modal fade" id="modalEvento" tabindex="-1" aria-labelledby="modalEventoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalEventoLabel">Detalhes do Evento</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <dl class="row">
                <dt class="col-sm-3">Nome:</dt>
                <dd class="col-sm-9" id="evNome"></dd>
                <dt class="col-sm-3">Tipo:</dt>
                <dd class="col-sm-9" id="evTipo"></dd>
                <dt class="col-sm-3">Início:</dt>
                <dd class="col-sm-9" id="evInicio"></dd>
                <dt class="col-sm-3">Término:</dt>
                <dd class="col-sm-9" id="evFim"></dd>
                <dt class="col-sm-3">Ministério:</dt>
                <dd class="col-sm-9" id="evMinisterio"></dd>
                <dt class="col-sm-3">Responsável:</dt>
                <dd class="col-sm-9" id="evResponsavel"></dd>
                <dt class="col-sm-3">Criado por:</dt>
                <dd class="col-sm-9" id="evCriadoPor"></dd>
                <dt class="col-sm-3">Status:</dt>
                <dd class="col-sm-9" id="evStatus"></dd>
                <dt class="col-sm-3">Observação:</dt>
                <dd class="col-sm-9" id="evObs"></dd>
              </dl>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="<?= ASSETS_URL ?>/plugins/jquery/jquery.min.js"></script>
    <script src="<?= ASSETS_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= ASSETS_URL ?>/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= ASSETS_URL ?>/js/logout.js"></script>
    <script src="<?= ASSETS_URL ?>/js/script_painel.js"></script>
    <script>
      const eventos = <?php echo json_encode($eventos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    </script>

  </body>
</html>
