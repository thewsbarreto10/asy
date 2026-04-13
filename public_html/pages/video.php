<?php
require_once __DIR__ . '/../../app/core/init.php';
require_once __DIR__ . '/../../app/core/sessao_segura.php';

$idVideo = (int)($_GET['id'] ?? 0);
if (!$idVideo) {
    echo "<h3 style='text-align:center; margin-top:20%; color:red;'>Vídeo inválido.</h3>";
    exit;
}

$stmt = $pdo->prepare("
    SELECT v.idVideo, v.tituloVideo, v.urlVideo, v.duracaoSegundos, v.ordemVideo,
           m.idModulo, m.nomeModulo, m.ordemModulo,
           c.idCurso, c.nomeCurso
    FROM asy_videos v
    INNER JOIN asy_modulos m ON m.idModulo = v.idModulo
    INNER JOIN asy_cursos c ON c.idCurso = m.idCurso
    WHERE v.idVideo = :id
");
$stmt->execute(['id' => $idVideo]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    echo "<h3 style='text-align:center; margin-top:20%; color:red;'>Vídeo não encontrado.</h3>";
    exit;
}

$stmt = $pdo->prepare("
    SELECT * FROM asy_progresso_videos 
    WHERE idUsuario = :idUsuario AND idVideo = :idVideo
");
$stmt->execute(['idUsuario' => $usuario['idUsuario'], 'idVideo' => $idVideo]);
$progresso = $stmt->fetch(PDO::FETCH_ASSOC);
$tempoAssistido = $progresso['tempoAssistido'] ?? 0;
$concluido = $progresso['concluido'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM asy_questionarios WHERE idVideo = :idVideo");
$stmt->execute(['idVideo' => $idVideo]);
$questionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT idVideo, tituloVideo 
    FROM asy_videos 
    WHERE idModulo = :idModulo AND ordemVideo > :ordem
    ORDER BY ordemVideo ASC
    LIMIT 1
");
$stmt->execute(['idModulo' => $video['idModulo'], 'ordem' => $video['ordemVideo']]);
$proximoVideo = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($video['tituloVideo']) ?></title>
<link rel="stylesheet" href="<?= ASSETS_URL ?>/plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/dist/css/adminlte.min.css">
<script src="<?= ASSETS_URL ?>/plugins/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
video::-webkit-media-controls-enclosure { overflow: hidden !important; }
video::-webkit-media-controls-panel { display: flex !important; justify-content: flex-start !important; }
#progressContainer {
    width: 100%;
    height: 8px;
    background-color: #ddd;
    margin: 5px 0 15px 0;
    border-radius: 4px;
}
#progressBar {
    width: 0%;
    height: 100%;
    background-color: #007bff;
    border-radius: 4px;
}
</style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php
require_once __DIR__ . '/../../app/views/navbar.php';
require_once __DIR__ . '/../../app/views/sidebar.php';
?>

<div class="content-wrapper">
<section class="content">
<div class="container-fluid">
    <h3><?= htmlspecialchars($video['tituloVideo']) ?></h3>

    <video id="player" width="100%" controls controlsList="nodownload noremoteplayback noplaybackrate">
        <source src="<?= BASE_URL . '/' . $video['urlVideo'] ?>" type="video/mp4">
        Seu navegador não suporta vídeo.
    </video>

    <div id="progressContainer">
        <div id="progressBar"></div>
    </div>

    <?php if($questionarios): ?>
    <div id="questionario" style="display:none; margin-top:20px;">
        <h4>Questionário</h4>
        <form id="formQuestionario">
            <?php foreach($questionarios as $q): ?>
            <div class="form-group">
                <label><?= htmlspecialchars($q['pergunta']) ?></label>
                <input type="text" name="resposta[<?= $q['idQuest'] ?>]" class="form-control" required>
            </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-success">Enviar respostas</button>
        </form>
    </div>
    <?php endif; ?>

    <?php if($proximoVideo): ?>
    <div id="proximoVideo" style="margin-top:20px;">
        <button id="btnProximo" class="btn btn-primary" <?= $concluido ? '' : 'disabled' ?>>
            Próximo vídeo: <?= htmlspecialchars($proximoVideo['tituloVideo']) ?>
        </button>
    </div>
    <?php endif; ?>
</div>
</section>
</div>

<script>
$(document).ready(function(){
    const player = document.getElementById('player');
    const progressBar = document.getElementById('progressBar');
    let tempoAssistido = <?= $tempoAssistido ?>;
    const concluido = <?= $concluido ?>;
    const idVideo = <?= $video['idVideo'] ?>;
    const idUsuario = <?= $usuario['idUsuario'] ?>;
    let bloqueioAtivo = false;
    let tentativaAvanco = false;

    // Atualiza a barra de progresso
    function atualizarBarra() {
        if (!player.duration) return;
        const perc = (tempoAssistido / player.duration) * 100;
        progressBar.style.width = perc + '%';
    }

    // Define ponto inicial do vídeo
    player.addEventListener('loadedmetadata', () => {
        if (tempoAssistido > 0 && tempoAssistido < player.duration) {
            player.currentTime = tempoAssistido;
        }
        atualizarBarra();
    });

    // Captura tentativas de avanço
    player.addEventListener('seeking', () => {
        if (player.currentTime > tempoAssistido + 0.5) {
            tentativaAvanco = true;
        } else {
            tentativaAvanco = false;
        }
    });

    // Controle de tempo real — sem travar o vídeo
    setInterval(() => {
        if (!player.duration || bloqueioAtivo) return;

        const tempoAtual = Math.floor(player.currentTime);

        // Impede avanço
        if (tentativaAvanco || tempoAtual > tempoAssistido + 1) {
            bloqueioAtivo = true;
            player.pause();
            player.currentTime = tempoAssistido;
            tentativaAvanco = false;
            bloqueioAtivo = false;
        }

        // Atualiza progresso se realmente avançou
        if (tempoAtual > tempoAssistido) {
            tempoAssistido = tempoAtual;
            atualizarBarra();
            $.post('progresso_video.php', { idVideo, tempo: tempoAssistido }, null, 'json');
        }
    }, 500); // checagem a cada meio segundo

    // Quando vídeo termina
    player.addEventListener('ended', () => {
        $.post('progresso_video.php', { idVideo, concluido: 1 }, () => {
            Swal.fire('Parabéns!', 'Você concluiu este vídeo.', 'success');
            $('#questionario').show();
            $('#btnProximo').prop('disabled', false);
            tempoAssistido = Math.floor(player.duration);
            atualizarBarra();
        }, 'json');
    });

    // Envio de questionário
    $('#formQuestionario').on('submit', function(e){
        e.preventDefault();
        $.post('responder_questionario.php', $(this).serialize(), function(resp){
            if(resp.success){
                Swal.fire('Parabéns!', 'Você concluiu o questionário.', 'success');
                $('#btnProximo').prop('disabled', false);
            } else {
                Swal.fire('Erro', resp.error, 'error');
            }
        }, 'json');
    });

    // Botão de próximo vídeo
    $('#btnProximo').click(function(){
        <?php if($proximoVideo): ?>
            window.location.href = 'video.php?id=<?= $proximoVideo['idVideo'] ?>';
        <?php endif; ?>
    });

    atualizarBarra();
});
</script>

</body>
</html>
