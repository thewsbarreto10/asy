<?php
require_once __DIR__ . '/../../app/core/init.php';
require_once __DIR__ . '/../../app/core/sessao_segura.php';

if (!$usuario) {
    header('Location: index.php');
    exit;
}

$idCurso = (int)($_GET['idCurso'] ?? 0);
if (!$idCurso) {
    echo "<h3 style='text-align:center; margin-top:20%; color:red;'>Curso inválido</h3>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Curso</title>
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php
require_once __DIR__ . '/../../app/views/navbar.php';
require_once __DIR__ . '/../../app/views/sidebar.php';
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1>Curso</h1>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div id="modulos-container">
        <p>Carregando módulos e vídeos...</p>
      </div>
    </div>
  </section>
</div>

<script src="<?= ASSETS_URL ?>/plugins/jquery/jquery.min.js"></script>
<script src="<?= ASSETS_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>/js/logout.js"></script>

<script>
const idCurso = <?= $idCurso ?>;

function carregarCurso() {
    $.getJSON('<?= ASSETS_URL ?>/proxy/curso/listar_curso.php?idCurso=' + idCurso, function(resp) {
        if(!resp.success){
            $('#modulos-container').html('<p style="color:red;">' + resp.error + '</p>');
            return;
        }

        let html = '';
        resp.modulos.forEach(mod => {
            html += `<div class="card card-primary card-outline mb-3">
                        <div class="card-header">
                          <h3 class="card-title">${mod.nomeModulo}</h3>
                        </div>
                        <div class="card-body">
                          <ul class="list-group">`;
            mod.videos.forEach(v => {
                const bloqueado = !v.concluido && v.ordemVideo > 1 ? 'disabled' : '';
                html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                            ${v.tituloVideo} 
                            <button class="btn btn-sm btn-primary assistir" data-video="${v.idVideo}" ${bloqueado}>
                                Assistir
                            </button>
                            ${v.concluido ? '<span class="badge badge-success">Concluído</span>' : ''}
                         </li>`;
            });
            html += `    </ul>
                        </div>
                     </div>`;
        });

        $('#modulos-container').html(html);
    });
}

$(document).on('click', '.assistir', function(){
    const idVideo = $(this).data('video');
    // Aqui você abriria modal ou redirecionaria para assistir o vídeo
    Swal.fire(`Abrir vídeo ${idVideo}`);
});

carregarCurso();
</script>
</body>
</html>
