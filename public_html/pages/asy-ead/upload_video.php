<?php
require_once __DIR__ . '/../../../app/core/init.php';
require_once __DIR__ . '/../../../app/core/sessao_segura.php';

// Segurança: apenas admins
if (!$usuario || ($usuario['idPermissaoUsuario'] ?? null) != 1) {
    echo "<h1 style='text-align:center;margin-top:20%;color:red;'>Acesso negado</h1>";
    exit;
}

// ID do curso
$idCurso = filter_input(INPUT_GET, 'idCurso', FILTER_VALIDATE_INT);
if (!$idCurso) {
    echo "<h3 style='text-align:center;margin-top:20%;color:red;'>Curso inválido.</h3>";
    exit;
}

// Buscar dados do curso
$stmt = $pdo->prepare("SELECT * FROM asy_cursos WHERE idCurso = ?");
$stmt->execute([$idCurso]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$curso) {
    echo "<h3 style='text-align:center;margin-top:20%;color:red;'>Curso não encontrado.</h3>";
    exit;
}

// Buscar módulos e vídeos do curso
$stmt = $pdo->prepare("    SELECT v.idVideo, v.tituloVideo, v.ordemVideo, v.urlVideo, m.idModulo, m.nomeModulo, m.ordemModulo
    FROM asy_modulos m
    LEFT JOIN asy_videos v ON v.idModulo = m.idModulo
    WHERE m.idCurso = ?
    ORDER BY m.ordemModulo ASC, v.ordemVideo ASC");
$stmt->execute([$idCurso]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar vídeos por módulo
$modulos = [];
foreach ($videos as $v) {
    $mid = $v['idModulo'];
    if (!isset($modulos[$mid])) {
        $modulos[$mid] = [
            'nomeModulo' => $v['nomeModulo'],
            'ordemModulo' => $v['ordemModulo'],
            'videos' => []
        ];
    }
    if ($v['idVideo']) {
        $modulos[$mid]['videos'][] = [
            'idVideo' => $v['idVideo'],
            'tituloVideo' => $v['tituloVideo'],
            'ordemVideo' => $v['ordemVideo'],
            'urlVideo' => $v['urlVideo']
        ];
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Upload de Vídeos - <?= htmlspecialchars($curso['nomeCurso']) ?></title>
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/dist/css/adminlte.min.css">
  <style>
    .video-card { width:180px; margin:5px; display:inline-block; vertical-align:top; }
    .video-card video { width:100%; border-radius:5px; }
    .video-actions { text-align:center; display:flex; justify-content:center; gap:10px; margin-top:5px; }
    .module-header { display:flex; justify-content:space-between; align-items:center; }
    .edit-icon { cursor:pointer; margin-left:10px; color:#007bff; }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php
require_once __DIR__ . '/../../../app/views/navbar.php';
require_once __DIR__ . '/../../../app/views/sidebar.php';
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1>Upload de Vídeos - <?= htmlspecialchars($curso['nomeCurso']) ?></h1>

      <!-- Botão para exibir formulário de módulo -->
<div style="display:flex; justify-content:flex-end; width:100%; margin-bottom:10px;">
  <button class="btn btn-success" onclick="$('#containerModulo').toggle()">
    <i class="fas fa-plus"></i> Cadastrar Módulo
  </button>
</div>

      <!-- Formulário escondido -->
      <div id="containerModulo" class="card card-primary" style="display:none;">
        <div class="card-header">Adicionar Novo Módulo</div>
        <div class="card-body">
          <form id="formModulo">
            <input type="hidden" name="idCurso" value="<?= $idCurso ?>">
            <div class="form-group">
              <label>Nome do Módulo</label>
              <input type="text" name="nomeModulo" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Ordem do Módulo</label>
              <input type="number" name="ordemModulo" class="form-control" min="1" value="<?= count($modulos)+1 ?>" required>
            </div>
            <button type="submit" class="btn btn-success">Criar Módulo</button>
          </form>
        </div>
      </div>

      <!-- Listagem de módulos e vídeos -->
      <?php foreach ($modulos as $mid => $modulo): ?>
        <div class="card card-outline card-info mt-3">
          <div class="card-header module-header">
            <h5>
              <span id="titulo-mod-<?= $mid ?>">
                Módulo <?= $modulo['ordemModulo'] ?>: <?= htmlspecialchars($modulo['nomeModulo']) ?>
              </span>
              <i class="fas fa-edit edit-icon" onclick="editarModulo(<?= $mid ?>)"></i>
              <i class="fas fa-trash text-danger ml-3" style="cursor:pointer;"
                onclick="removerModulo(<?= $mid ?>, <?= count($modulo['videos']) ?>)">
              </i>
            </h5>
            <button class="btn btn-primary btn-sm" style="margin-left:auto;" onclick="mostrarFormVideo(<?= $mid ?>)">
              <i class="fas fa-video"></i> Adicionar Vídeo
            </button>
          </div>

          <div class="card-body">
            <div id="videos-modulo-<?= $mid ?>">
              <?php if ($modulo['videos']): ?>
                <?php foreach ($modulo['videos'] as $v): ?>
                  <div class="video-card card p-2">
                    <div class="video-title text-center font-weight-bold">
                      <?= htmlspecialchars($v['tituloVideo']) ?>
                    </div>
                    <video controls src="/asy/pages/asy-ead/<?= htmlspecialchars($v['urlVideo']) ?>"></video>
                    <div class="video-actions">
                      <i class="fas fa-exchange-alt text-warning" onclick="substituirVideo(<?= $v['idVideo'] ?>)"></i>
                      <i class="fas fa-trash text-danger" onclick="removerVideo(<?= $v['idVideo'] ?>)"></i>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p>Nenhum vídeo neste módulo.</p>
              <?php endif; ?>
            </div>

            <!-- Formulário de vídeo -->
            <div id="form-video-<?= $mid ?>" style="display:none; margin-top:10px;">
              <form class="formVideo" data-modulo="<?= $mid ?>">
                <input type="hidden" name="idModulo" value="<?= $mid ?>">
                <div class="form-group">
                  <label>Nome do Vídeo</label>
                  <input type="text" name="nomeVideo" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Arquivo de Vídeo</label>
                  <input type="file" name="arquivoVideo" accept="video/*" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Ordem</label>
                  <input type="number" name="ordemVideo" min="1" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Enviar Vídeo</button>
              </form>
            </div>

          </div>
        </div>
      <?php endforeach; ?>

    </div>
  </section>
</div>

<script src="<?= ASSETS_URL ?>/plugins/jquery/jquery.min.js"></script>
<script src="<?= ASSETS_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function mostrarFormVideo(idModulo){
  $("#form-video-"+idModulo).toggle();
}

function editarModulo(id){
  let atual = $("#titulo-mod-"+id).text();
  $("#titulo-mod-"+id).html(`<input id='inp-mod-${id}' class='form-control' value='${atual}'>` +
    `<button class='btn btn-sm btn-success ml-2' onclick='salvarEdicao(${id})'><i class="fas fa-check"></i></button>`);
}

function salvarEdicao(id){
  let novo = $("#inp-mod-"+id).val();
  // Implementar chamada AJAX futuramente
  Swal.fire('Sucesso', 'Módulo atualizado!', 'success');
}

$(".formVideo").on("submit", function(e){
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
        url: 'upload_video_ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(resp){
            if(resp.ok){
                Swal.fire('Sucesso', resp.msg, 'success').then(()=>location.reload());
            } else {
                Swal.fire('Erro', resp.msg, 'error');
            }
        }
    });
});

function removerModulo(idModulo, qtdVideos){
    if(qtdVideos > 0){
        Swal.fire(
            'Não é possível remover',
            'Este módulo possui vídeos. Remova todos os vídeos antes de excluir o módulo.',
            'warning'
        );
        return;
    }

    Swal.fire({
        title: 'Tem certeza?',
        text: 'Você está prestes a remover este módulo.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, remover!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            $.post('remover_modulo.php', { idModulo: idModulo }, function(resp){
                if(resp.ok){
                    Swal.fire('Removido!', resp.msg, 'success').then(()=>location.reload());
                } else {
                    Swal.fire('Erro', resp.msg, 'error');
                }
            }, 'json');
        }
    });
}

$("#formModulo").on("submit", function(e){
    e.preventDefault();

    $.post("criar_modulo.php", $(this).serialize(), function(resp){
        if(resp.ok){
            Swal.fire('Sucesso', resp.msg, 'success')
                .then(()=> location.reload());
        } else {
            Swal.fire('Erro', resp.msg, 'error');
        }
    }, "json");
});

function removerVideo(idVideo){
    Swal.fire({
        title: 'Remover Vídeo?',
        text: 'Tem certeza que deseja remover este vídeo?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, remover',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            $.post('remover_video.php', { idVideo }, function(resp){
                if(resp.ok){
                    Swal.fire('Removido!', resp.msg, 'success')
                        .then(()=> location.reload());
                } else {
                    Swal.fire('Erro', resp.msg, 'error');
                }
            }, 'json');
        }
    });
}

function substituirVideo(idVideo){
    Swal.fire({
        title: 'Substituir Vídeo',
        html: `
            <input type="file" id="novoVideo" accept="video/*" class="swal2-input">
        `,
        preConfirm: () => {
            let file = document.getElementById('novoVideo').files[0];
            if(!file){
                Swal.showValidationMessage('Escolha um arquivo de vídeo');
            }
            return file;
        },
        showCancelButton: true,
        confirmButtonText: 'Enviar'
    }).then(result => {
        if(result.isConfirmed){
            let formData = new FormData();
            formData.append("idVideo", idVideo);
            formData.append("arquivoVideo", result.value);

            $.ajax({
                url: 'substituir_video.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',   // <------- ADICIONE ESTA LINHA
                success: function(resp){
                    if(resp.ok){
                        Swal.fire('Sucesso', resp.msg, 'success')
                            .then(()=>location.reload());
                    } else {
                        Swal.fire('Erro', resp.msg, 'error');
                    }
                }
            });

        }
    });
}


</script>

</body>
</html>
