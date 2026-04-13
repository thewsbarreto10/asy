<?php

  require_once __DIR__ . '/../app/core/init.php';
  require_once __DIR__ . '/../app/core/sessao_segura.php';

  // Verifica se o usuário está logado
  if (!$usuario) {
      header('Location: /asy/');
      exit;
  }

  // =========================
  // EVENTOS
  // =========================

  $eventos = $pdo->query("
      SELECT 
          e.idEvento,
          e.nomeEvento,
          e.tipoEvento,
          e.dataHoraInicio,
          e.dataHoraFim,
          m.descricaoMinisterio AS ministerio,
          e.observacao,
          u1.nomeCompletoUsuario AS criadoPor,
          u2.nomeCompletoUsuario AS responsavel,
          e.statusEvento AS status
      FROM asy_eventos e
      LEFT JOIN asy_ministerios m ON e.idMinisterio = m.idMinisterio
      LEFT JOIN asy_usuarios u1 ON e.criadoPor = u1.idUsuario
      LEFT JOIN asy_usuarios u2 ON e.idResponsavel = u2.idUsuario
      WHERE e.statusEvento = 'ativo'
      ORDER BY e.dataHoraInicio ASC
  ")->fetchAll(PDO::FETCH_ASSOC);


  // =========================
  // TOTAL DE USUÁRIOS
  // =========================

  $totalUsuarios = $pdo->query("
      SELECT COUNT(*) 
      FROM asy_usuarios 
      WHERE statusUsuario = 'ativo'
  ")->fetchColumn();


  // =========================
  // CONTROLE DE DATAS
  // =========================

  $hoje = new DateTime();

  $diaHoje = (int)$hoje->format('d');
  $mesHoje = (int)$hoje->format('m');
  $anoHoje = (int)$hoje->format('Y');

  $ultimoDiaMes = (int)$hoje->format('t');
  $diasRestantesMes = $ultimoDiaMes - $diaHoje;

  // Se faltar 7 dias ou menos para acabar o mês
  $mostrarProximoMes = $diasRestantesMes <= 7;


  // =========================
  // ANIVERSARIANTES DO MÊS
  // =========================

  $sql = "
      SELECT 
          u.idUsuario,
          u.nomeCompletoUsuario,
          u.dataNascimentoUsuario,
          u.foto_perfil,
          m.descricaoMinisterio
      FROM asy_usuarios u
      LEFT JOIN asy_ministerios m 
          ON m.idMinisterio = u.idMinisterioUsuario
      WHERE MONTH(u.dataNascimentoUsuario) = :mes
      AND u.statusUsuario = 'ativo'
      ORDER BY DAY(u.dataNascimentoUsuario)
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':mes', $mesHoje);
  $stmt->execute();

  $aniversariantes = $stmt->fetchAll(PDO::FETCH_ASSOC);


  // =========================
  // ANIVERSARIANTES PRÓXIMO MÊS
  // (Primeira semana)
  // =========================

  $aniversariantesProximoMes = [];

  if ($mostrarProximoMes) {

      $proximoMes = $mesHoje == 12 ? 1 : $mesHoje + 1;

      $sqlProximo = "
          SELECT 
              u.idUsuario,
              u.nomeCompletoUsuario,
              u.dataNascimentoUsuario,
              u.foto_perfil,
              m.descricaoMinisterio
          FROM asy_usuarios u
          LEFT JOIN asy_ministerios m 
              ON m.idMinisterio = u.idMinisterioUsuario
          WHERE MONTH(u.dataNascimentoUsuario) = :mes
          AND DAY(u.dataNascimentoUsuario) <= 7
          AND u.statusUsuario = 'ativo'
          ORDER BY DAY(u.dataNascimentoUsuario)
      ";

      $stmtProx = $pdo->prepare($sqlProximo);
      $stmtProx->bindParam(':mes', $proximoMes);
      $stmtProx->execute();

      $aniversariantesProximoMes = $stmtProx->fetchAll(PDO::FETCH_ASSOC);
  }


  // =========================
  // NOME DO MÊS
  // =========================

  $meses = [
      1 => 'Janeiro',
      'Fevereiro',
      'Março',
      'Abril',
      'Maio',
      'Junho',
      'Julho',
      'Agosto',
      'Setembro',
      'Outubro',
      'Novembro',
      'Dezembro'
  ];

  $mesNome = $meses[$mesHoje];

  $sql = "
      SELECT
          e.idEvento,
          e.nomeEvento,
          e.dataHoraInicio,
          e.dataHoraFim,
          u.nomeCompletoUsuario AS responsavel
      FROM asy_eventos e
      LEFT JOIN asy_usuarios u 
          ON u.idUsuario = e.idResponsavel
          WHERE e.statusEvento = 'ativo'
      ORDER BY e.dataHoraInicio ASC
  ";

  $stmt = $pdo->query($sql);
  $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <style>
      .card-tools input[type="date"]{
        width:140px;
      }

      .paginacao-eventos{
        display:flex;
        justify-content:center;
        gap:6px;
      }

      .btn-page{
        border:none;
        background:#ffffff;
        color:#28a745;
        border-radius:6px;
        padding:4px 10px;
        font-size:13px;
        cursor:pointer;
        transition:all 0.2s ease;
        box-shadow:0 1px 3px rgba(0,0,0,0.1);
      }

      .btn-page:hover{
        background:#498570;
        color:#fff;
      }

      .btn-page.active{
        background:#84cfb5;
        color:#fff;
        font-weight:600;
        transform:scale(1.05);
      }
    </style>
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
          </div>
        </section>

        <!-- Calendário + Eventos -->
        <section class="content">
          <div class="container-fluid">
            <div class="row">
              <div class="card bg-gradient-success col-md-5">
                <div class="card-header border-0">
                  <h3 class="card-title">
                    <i class="far fa-calendar-alt"></i>
                    Eventos
                  </h3>
                  <!-- tools card -->
                  <div class="card-tools d-flex align-items-center" style="gap:6px;">

                    <input type="date"
                          id="dataInicio"
                          class="form-control form-control-sm date-range">

                    <span style="font-size:12px;">até</span>

                    <input type="date"
                          id="dataFim"
                          class="form-control form-control-sm date-range">

                    <button type="button" class="btn btn-success btn-sm" data-card-widget="collapse">
                      <i class="fas fa-minus"></i>
                    </button>

                  </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body pt-0">

                  <div id="listaEventos">
                    <!-- eventos carregam aqui -->
                  </div>

                </div>
                <!-- /.card-body -->
              </div>

              <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box bg-gradient-info shadow">
                  <span class="info-box-icon"><i class="fas fa-birthday-cake"></i></span>

                  <div class="info-box-content">

                    <span class="info-box-text">
                      Aniversariantes do Mês (<b><?= $mesNome ?></b>)
                    </span>

                    <div style="max-height:160px; overflow-y:auto; margin-top:8px;">

                      <?php if(count($aniversariantes) > 0): ?>

                        <?php foreach ($aniversariantes as $u): ?>

                          <?php

                            $foto = !empty($u['foto_perfil'])
                              ? BASE_URL . '/proxy/usuario/foto.php?id=' . $u['idUsuario']
                              : BASE_URL . '/assets/img/user-profile-img-default.png';

                            $dataNascimento = new DateTime($u['dataNascimentoUsuario']);
                            $hoje = new DateTime();

                            $idade = $hoje->diff($dataNascimento)->y;

                            $diaNascimento = $dataNascimento->format('d');
                            $mesNascimento = $dataNascimento->format('m');

                            $hojeDia = date('d');
                            $hojeMes = date('m');

                            $aniversarioHoje = ($diaNascimento == $hojeDia && $mesNascimento == $hojeMes);

                          ?>

                          <div style="
                            display:flex;
                            align-items:center;
                            justify-content:space-between;
                            margin-bottom:8px;
                            <?= $aniversarioHoje ? 'background:rgba(255,255,255,0.25); padding:6px; border-radius:6px;' : '' ?>
                          ">

                            <div style="display:flex; align-items:center;">

                              <img src="<?= htmlspecialchars($foto) ?>"
                                  width="32"
                                  height="32"
                                  style="border-radius:50%; margin-right:8px; object-fit:cover;">

                              <div>

                                <div style="font-size:14px;">

                                  <?php if($aniversarioHoje): ?>

                                    🎉 <b>HOJE</b> -
                                    <b><?= htmlspecialchars($u['nomeCompletoUsuario']) ?></b>

                                  <?php else: ?>

                                    Dia <?= $diaNascimento ?> -
                                    <b><?= htmlspecialchars($u['nomeCompletoUsuario']) ?></b>

                                  <?php endif; ?>

                                </div>

                                <div style="font-size:12px; opacity:0.9;">
                                  <?= $idade ?> anos | <?= htmlspecialchars($u['descricaoMinisterio'] ?? 'Sem ministério') ?>
                                </div>

                              </div>

                            </div>

                            <?php if($aniversarioHoje): ?>

                              <a href="<?= BASE_URL ?>/usuarios/parabenizar.php?id=<?= $u['idUsuario'] ?>"
                                class="btn btn-warning btn-xs"
                                title="Desejar parabéns">

                                <b>Felicitar</b>

                              </a>

                            <?php endif; ?>

                          </div>

                        <?php endforeach; ?>

                      <?php else: ?>

                        <div style="font-size:14px;">
                          Nenhum aniversariante este mês
                        </div>

                      <?php endif; ?>

                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <script>

      const inicio = document.getElementById('dataInicio');
      const fim = document.getElementById('dataFim');

      let paginaAtual = 1;

      inicio.addEventListener('change', () => {
          paginaAtual = 1;
          atualizarEventos();
      });

      fim.addEventListener('change', () => {
          paginaAtual = 1;
          atualizarEventos();
      });

      function atualizarEventos(){

          const dataInicio = inicio.value;
          const dataFim = fim.value;

          let url = `eventos_range.php?pagina=${paginaAtual}`;

          if(dataInicio && dataFim){
              url += `&dataInicio=${dataInicio}&dataFim=${dataFim}`;
          }

          fetch(url)
          .then(response => response.text())
          .then(html => {

              document.getElementById('listaEventos').innerHTML = html;

          });

      }

      function mudarPagina(p){

          paginaAtual = p;

          atualizarEventos();

      }

      document.addEventListener('DOMContentLoaded', () => {

          atualizarEventos();

      });

    </script>

    <script>

      const datas = document.querySelectorAll('.date-range');

      datas.forEach(input => {

          input.addEventListener('click', () => {
              if (input.showPicker) {
                  input.showPicker();
              }
          });

          input.addEventListener('change', enviarFiltro);

      });

      function enviarFiltro() {

          const inicio = document.getElementById('dataInicio').value;
          const fim = document.getElementById('dataFim').value;

          if(inicio && fim){
              document.getElementById('filtroEventos').submit();
          }

      }

    </script>

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
