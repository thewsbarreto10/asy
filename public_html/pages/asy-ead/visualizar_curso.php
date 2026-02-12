<?php
require_once __DIR__ . '/../../../app/core/init.php';
require_once __DIR__ . '/../../../app/core/sessao_segura.php';

if (!$usuario) {
    header("Location: ../index.php");
    exit;
}
$idUsuario = $usuario['idUsuario'];
$idCurso = $_GET['idCurso'] ?? null;
$idVideo = $_GET['idVideo'] ?? null;

if (!$idCurso) exit("Curso não informado.");

/* ---------------- BUSCA CURSO ---------------- */
$stmtCurso = $pdo->prepare("
    SELECT 
        c.idCurso,
        c.nomeCurso,
        c.descricaoCurso,
        c.imagemCurso,
        (
            SELECT COUNT(*)
            FROM asy_modulos m
            JOIN asy_videos v ON v.idModulo = m.idModulo
            WHERE m.idCurso = c.idCurso
        ) AS totalAulas,
        0 AS aulasConcluidas
    FROM asy_cursos c
    WHERE c.idCurso = ?
");
$stmtCurso->execute([$idCurso]);
$curso = $stmtCurso->fetch(PDO::FETCH_ASSOC);

if (!$curso) exit("Curso não encontrado.");

/* ---- Buscar aulas concluídas pelo usuário ---- */
$stmtConcluidas = $pdo->prepare("
    SELECT COUNT(*) AS concluidas
    FROM asy_progresso_videos pv
    JOIN asy_videos v ON v.idVideo = pv.idVideo
    JOIN asy_modulos m ON m.idModulo = v.idModulo
    WHERE pv.idUsuario = ? 
      AND pv.concluido = 1
      AND m.idCurso = ?
");
$stmtConcluidas->execute([$idUsuario, $idCurso]);
$aulasConcluidas = (int)$stmtConcluidas->fetchColumn();

/* ---- Calcular porcentagem ---- */
$porcentagemConcluida = ($curso['totalAulas'] > 0)
    ? round(($aulasConcluidas / $curso['totalAulas']) * 100)
    : 0;


/* ---------------- BUSCA MÓDULOS E VIDEOS ---------------- */
$stmtMod = $pdo->prepare("
    SELECT 
        m.idModulo, 
        m.nomeModulo, 
        v.idVideo, 
        v.tituloVideo, 
        v.duracaoSegundos, 
        v.urlVideo,
        m.ordemModulo,
        v.ordemVideo
    FROM asy_modulos m
    LEFT JOIN asy_videos v ON v.idModulo = m.idModulo
    WHERE m.idCurso = ?
    ORDER BY m.ordemModulo ASC, v.ordemVideo ASC
");
$stmtMod->execute([$idCurso]);
$dados = $stmtMod->fetchAll(PDO::FETCH_ASSOC);

/* --------- ORGANIZAR MÓDULOS E VÍDEOS ---------- */
$modulos = [];            // idModulo => ['nome'=>..., 'videos'=>[...], 'ordem'=>...]
$listaVideos = [];        // lista linear: each item ['idVideo','nome','duracao','url','idModulo']

$moduleOrder = []; // ordered list of module ids

foreach ($dados as $d) {
    $idM = $d['idModulo'];
    if (!isset($modulos[$idM])) {
        $modulos[$idM] = [
            'nome' => $d['nomeModulo'],
            'videos' => [],
            'ordem' => $d['ordemModulo'] ?? 0
        ];
        $moduleOrder[] = $idM;
    }

    if ($d['idVideo']) {
        $video = [
            'idVideo' => (int)$d['idVideo'],
            'nome' => $d['tituloVideo'],
            'duracao' => (int)$d['duracaoSegundos'],
            'url' => $d['urlVideo'],
            'idModulo' => $idM,
            'ordemVideo' => $d['ordemVideo'] ?? 0
        ];
        $modulos[$idM]['videos'][] = $video;
        $listaVideos[] = $video;
    }
}

if (count($listaVideos) === 0) {
    echo "<script>
        alert('Não há vídeos vinculados a este curso, aguarde.');
        window.location.href='cursos.php';
    </script>";
    exit;
}

// Seleciona o primeiro vídeo se idVideo não foi informado
if (!$idVideo) {
    $idVideo = $listaVideos[0]['idVideo'];
}

/* ---------- encontrar índice atual na lista linear ---------- */
$indiceAtual = null;
foreach ($listaVideos as $i => $v) {
    if ($v['idVideo'] == $idVideo) {
        $indiceAtual = $i;
        break;
    }
}

/* ------------ BLOQUEIO DE AVANÇO (BACKEND SEGURO) ------------ */
if ($indiceAtual === null) {
    // caso o vídeo informado na URL não pertença ao curso
    echo "<script>
        alert('Vídeo não pertence a este curso.');
        window.location.href='cursos.php';
    </script>";
    exit;
}


if ($indiceAtual > 0) {
    $idVideoAnterior = $listaVideos[$indiceAtual - 1]['idVideo'];
    $duracaoAnterior = (int)$listaVideos[$indiceAtual - 1]['duracao'];

    $stmtPrev = $pdo->prepare("
        SELECT tempoAssistido, concluido 
        FROM asy_progresso_videos 
        WHERE idUsuario = ? AND idVideo = ?
    ");
    $stmtPrev->execute([$idUsuario, $idVideoAnterior]);
    $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);

    $percentPrev = 0;
    if ($prev) {
        $percentPrev = ($duracaoAnterior > 0)
            ? ($prev['tempoAssistido'] / $duracaoAnterior) * 100
            : 0;
    }

    if (!$prev || (($prev['concluido'] ?? 0) != 1 && $percentPrev < 98)) {
        exit("
            <h2 style='color:red;text-align:center;margin-top:40px;'>
            🚫 Você não pode assistir esta aula ainda.<br>
            Conclua a aula anterior primeiro.
            </h2>
        ");
    }
}

/* ---------------- VÍDEO ATUAL ---------------- */
$stmtVideo = $pdo->prepare("SELECT * FROM asy_videos WHERE idVideo = ?");
$stmtVideo->execute([$idVideo]);
$videoAtual = $stmtVideo->fetch(PDO::FETCH_ASSOC);

if (!$videoAtual) exit("Vídeo não encontrado.");

/* ---------------- PROGRESSO ---------------- */
$stmtProg = $pdo->prepare("
    SELECT tempoAssistido, concluido 
    FROM asy_progresso_videos 
    WHERE idUsuario = ? AND idVideo = ?
");
$stmtProg->execute([$usuario['idUsuario'], $idVideo]);
$prog = $stmtProg->fetch(PDO::FETCH_ASSOC);

$tempoAssistido = isset($prog['tempoAssistido']) ? (int)$prog['tempoAssistido'] : 0;
$concluido = isset($prog['concluido']) ? (int)$prog['concluido'] : 0;

/* --------- PRÓXIMO / ANTERIOR / NEXT MODULE FIRST VIDEO / IS LAST --------- */
$prevVideoId = ($indiceAtual > 0) ? $listaVideos[$indiceAtual - 1]['idVideo'] : null;
$nextVideoId = (isset($listaVideos[$indiceAtual + 1])) ? $listaVideos[$indiceAtual + 1]['idVideo'] : null;

// encontrar o primeiro vídeo do próximo módulo (se houver)
$curModuleId = $listaVideos[$indiceAtual]['idModulo'];
$posModule = array_search($curModuleId, $moduleOrder);
$nextModuleFirstVideoId = null;
if ($posModule !== false && isset($moduleOrder[$posModule + 1])) {
    $nextModuleId = $moduleOrder[$posModule + 1];
    if (!empty($modulos[$nextModuleId]['videos'])) {
        $nextModuleFirstVideoId = $modulos[$nextModuleId]['videos'][0]['idVideo'];
    }
}

// flag se é o último vídeo de todo o curso
$isLastOverall = (!$nextVideoId && !$nextModuleFirstVideoId);

/* --------- FORMATADOR DE TEMPO --------- */
function formatarDuracao($seg)
{
    if (!$seg || $seg <= 0) return "00:00";
    return $seg >= 3600 ? gmdate("H:i:s", $seg) : gmdate("i:s", $seg);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($curso['nomeCurso']) ?> | Aula</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* Mantive seu layout original, adicionei apenas o mínimo para overlay de botões */
body { margin:0; padding:0; background:#0d1117; color:white; font-family: "Inter", sans-serif; display:flex; height:100vh; overflow:hidden; }
.sidebar { width: 320px; background:#111827; overflow-y:auto; border-right:1px solid #1f2937; padding-bottom:40px; }
.course-header { padding:20px; background:#1f2937; border-bottom:1px solid #374151; }
.course-title { font-size:20px; font-weight:600; margin-bottom:10px; }
.progress-info { display:flex; justify-content:space-between; font-size:14px; margin-bottom:6px; }
.progress-bar { width:100%; height:8px; background:#1e1e1e; border-radius:20px; }
.progress-fill { height:8px; background:#3b82f6; border-radius:20px; }
.module-title { padding:15px 20px; margin-top:10px; font-size:1.4rem; font-weight:bold; background:#1e293b; color:#60a5fa; border-left:4px solid #3b82f6; display:flex; align-items:center; gap:10px; }
.video-item { padding:12px 20px; cursor:pointer; color:#cbd5e1; display:flex; justify-content:space-between; border-bottom:1px solid #1e293b; transition:0.2s; align-items:center; }
.video-item:hover { background:#1e293b; }
.video-active { background:#0f172a; color:#fff !important; border-left:4px solid #3b82f6; }
.video-locked { opacity:0.4; cursor:not-allowed; pointer-events:none; }
.video-locked .lesson-icon { color:#b91c1c !important; }
.video-area { flex:1; display:flex; flex-direction:column; padding:20px; gap:20px; }
.player-box { background:#111; border-radius:12px; padding:15px; border:1px solid #1f2937; position:relative; }
.player-box.overlay-active::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.55);
    border-radius: 12px;
    z-index: 40;
}
/* assegura que video fica atrás do overlay sombreado */
video { width:100%; height:480px; border-radius:10px; background:#000; position:relative; z-index:10; }

/* overlay central com os botões (aparece ao concluir) */
.overlay-controls {
    position:absolute;
    left:50%;
    top:50%;
    transform:translate(-50%,-50%);
    display:flex;
    gap:12px;
    z-index:50;
    pointer-events:none; /* enabled per-button when visible */
}
.overlay-controls .btn {
    pointer-events:auto;
    background:rgba(17,24,39,0.95);
    border:1px solid rgba(255,255,255,0.06);
    color:#fff;
    padding:10px 16px;
    border-radius:8px;
    cursor:pointer;
    backdrop-filter: blur(6px);
    display:flex;
    gap:8px;
    align-items:center;
}
.overlay-controls .btn.primary { background:#3b82f6; border-color:transparent; }
.overlay-controls .btn.warn { background:#10b981; border-color:transparent; }
.overlay-controls .btn.ghost { background:transparent; border:1px solid rgba(255,255,255,0.06); }

.progress-container { height:6px; width:100%; background:#1e293b; border-radius:4px; overflow:hidden; }
.progress-bar-video { height:6px; background:#10b981; width:0%; }
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="course-header">
        <a href="cursos.php" style="color:white;text-decoration:none;">⬅ Voltar</a>

        <div class="course-title"><?= htmlspecialchars($curso['nomeCurso']) ?></div>

        <div class="progress-info">
            <span>Progresso</span>
            <span><?= $porcentagemConcluida ?>%</span>
        </div>

        <div class="progress-bar">
            <div class="progress-fill" style="width:<?= $porcentagemConcluida ?>%"></div>
        </div>
    </div>

    <div class="sub" style="padding:15px 20px; background:#0f172a;">
        <strong>Aula atual:</strong><br>
        <?= htmlspecialchars($videoAtual['tituloVideo']) ?>
    </div>

    <?php foreach ($modulos as $idM => $m): ?>

        <div class="module-title">
            <i class="fa-solid fa-folder-open"></i>
            <?= htmlspecialchars($m['nome']) ?>
        </div>

        <?php foreach ($m['videos'] as $v): ?>

            <?php
            $indice = array_search($v['idVideo'], array_column($listaVideos, 'idVideo'));
            $bloqueada = false;

            if ($indice > 0) {
                $prevId = $listaVideos[$indice - 1]['idVideo'];
                $durPrev = (int)$listaVideos[$indice - 1]['duracao'];

                $stmtCheck = $pdo->prepare("
                    SELECT tempoAssistido, concluido 
                    FROM asy_progresso_videos 
                    WHERE idUsuario = ? AND idVideo = ?
                ");
                $stmtCheck->execute([$idUsuario, $prevId]);
                $check = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                $percent = $check ? (($check['tempoAssistido'] ?? 0) / max(1, $durPrev)) * 100 : 0;

                if (!$check || (($check['concluido'] ?? 0) != 1 && $percent < 98)) {
                    $bloqueada = true;
                }
            }
            ?>

            <div class="video-item <?= $bloqueada?'video-locked':'' ?> <?= $v['idVideo']==$idVideo?'video-active':'' ?>"
                 <?= $bloqueada ? '' : "onclick=\"location.href='visualizar_curso.php?idCurso={$idCurso}&idVideo={$v['idVideo']}'\"" ?>>

                <div>
                    <i class="fa-solid <?= $bloqueada?'fa-lock':'fa-play' ?> lesson-icon"></i>
                    <span class="lesson-title"><?= htmlspecialchars($v['nome']) ?></span>
                </div>

                <small style="opacity:.6;">
                    <?= formatarDuracao($v['duracao']) ?>
                </small>
            </div>

        <?php endforeach; ?>
    <?php endforeach; ?>

</div>

<!-- ÁREA DE VÍDEO -->
<div class="video-area">

    <div id="playerBox" class="player-box">
        <video id="player" controls controlsList="nodownload" preload="auto">
            <?php $urlVideo = htmlspecialchars(str_replace(' ', '%20', $videoAtual['urlVideo'])); ?>
            <source src="<?= $urlVideo ?>" type="video/mp4">
        </video>

        <!-- OVERLAY BOTOES (só aparecem quando liberados via JS) -->
        <div id="overlayControls" class="overlay-controls" style="display:none;">
            <!-- botões serão preenchidos via JS conforme situação -->
        </div>
    </div>

    <div class="progress-container">
        <div id="barra" class="progress-bar-video" style="width: <?= $concluido ? '100%' : '0%' ?>"></div>
    </div>

</div>

<script>
const player = document.getElementById('player');
const overlay = document.getElementById('overlayControls');
const playerBox = document.getElementById('playerBox');

let tempoAssistido = <?= (int)$tempoAssistido ?>;
let concluido = <?= (int)$concluido ?>;
let indiceAtual = <?= (int)$indiceAtual ?>;
let prevVideoId = <?= $prevVideoId ? (int)$prevVideoId : 'null' ?>;
let nextVideoId = <?= $nextVideoId ? (int)$nextVideoId : 'null' ?>;
let nextModuleFirstVideoId = <?= $nextModuleFirstVideoId ? (int)$nextModuleFirstVideoId : 'null' ?>;
let isLastOverall = <?= $isLastOverall ? '1' : '0' ?>;
let idCurso = <?= (int)$idCurso ?>;
let idVideo = <?= (int)$idVideo ?>;
let autoFinalizado = false;
let overlaySuppressed = false; // se usuário clicou "assistir novamente" suprime overlay até o fim

// 1) garantir posição do player (retomar ou zero se concluído)
player.addEventListener('loadedmetadata', () => {
    if (concluido === 1) {
        player.currentTime = 0;
    } else {
        // se existe progresso salvo e é menor que duração -> retoma
        if (tempoAssistido > 0 && tempoAssistido < player.duration) {
            player.currentTime = tempoAssistido;
        } else {
            player.currentTime = 0;
        }
    }

    // inicializa barra
    if (player.duration && tempoAssistido >= 0) {
        document.getElementById('barra').style.width = ((tempoAssistido / player.duration) * 100) + "%";
    }

    // se já concluído, mostrar overlay imediatamente (somente se não foi suprimido)
    if (concluido === 1 && !overlaySuppressed) {
        showOverlayButtons();
    }
});

// 2) bloquear avanço além do ponto assistido (quando não concluído)
player.addEventListener('seeking', e => {
    if (concluido !== 1) {
        // permitir uma pequena margem (0.5s)
        if (player.currentTime > (tempoAssistido + 0.5)) {
            player.currentTime = tempoAssistido;
        }
    }
});

// 3) salvar progresso periodicamente e detectar >=98%
setInterval(() => {
    if (!player.duration || player.duration === 0) return;

    const percentWatched = (player.currentTime / player.duration) * 100;
    document.getElementById('barra').style.width = Math.min(100, (percentWatched)) + "%";

    if (player.currentTime > tempoAssistido && concluido !== 1) {
        tempoAssistido = Math.floor(player.currentTime);

        // salvar progresso
        fetch("salvar_progresso.php", {
            method: "POST",
            body: new URLSearchParams({
                idVideo: idVideo,
                tempo: tempoAssistido
            })
        }).catch(()=>{ /* silencioso */ });
    }

    // auto-finalizar quando >=98% (uma única vez)
    if (!autoFinalizado && percentWatched >= 98) {
        fetch("finalizar_video.php", {
            method: "POST",
            body: new URLSearchParams({ idVideo: idVideo })
        }).then(() => {
            autoFinalizado = true;
            concluido = 1;
            document.getElementById('barra').style.width = "100%";
            // só mostrar overlay se não foi suprimido por replay
            if (!overlaySuppressed) showOverlayButtons();
        }).catch(()=>{ /* silencioso */ });
    }
}, 5000);

// 4) quando o vídeo terminar (ended)
player.addEventListener('ended', () => {
    // garantir backend marcado
    fetch("finalizar_video.php", {
        method: "POST",
        body: new URLSearchParams({ idVideo: idVideo })
    }).then(() => {
        concluido = 1;
        document.getElementById('barra').style.width = "100%";
        // resetar supressão para que overlay possa reaparecer ao fim
        overlaySuppressed = false;
        showOverlayButtons();
    }).catch(()=>{ /* silencioso */ });
});

/* ---------- Função que monta e mostra os botões no overlay ---------- */
function showOverlayButtons() {
    overlay.innerHTML = ''; // limpa

    // marca playerBox com classe para escurecer fundo
    playerBox.classList.add('overlay-active');

    // Helper para criar botão
    function createBtn(text, classes, onClick) {
        const b = document.createElement('button');
        b.className = 'btn ' + classes;
        b.innerHTML = text;
        b.onclick = onClick;
        return b;
    }

    // 1) Botão "Assistir Aula Anterior" (se existir)
    if (prevVideoId) {
        const btnPrev = createBtn('<i class="fa-solid fa-arrow-left"></i> Aula Anterior', 'ghost', () => {
            // ir para anterior
            window.location.href = `visualizar_curso.php?idCurso=${idCurso}&idVideo=${prevVideoId}`;
        });
        overlay.appendChild(btnPrev);
    }

    // 2) Botão "Assistir Novamente" (sempre)
    const btnReplay = createBtn('<i class="fa-solid fa-rotate-right"></i> Assistir Novamente', 'warn', () => {
        // Opção B: replay apenas visual — não altera banco
        overlaySuppressed = true; // suprime overlay até o fim da nova reprodução
        overlay.style.display = 'none';
        playerBox.classList.remove('overlay-active');
        // reinicia player visualmente e começa a tocar
        player.currentTime = 0;
        player.play();
    });
    overlay.appendChild(btnReplay);

    // 3) Botão principal (Próxima / Avançar módulo / Concluir)
    if (nextVideoId) {
        const btnNext = createBtn('<i class="fa-solid fa-arrow-right"></i> Próxima Aula', 'primary', async () => {
            // garantir que backend concluiu este vídeo antes de redirecionar
            await fetch("finalizar_video.php", {
                method: "POST",
                body: new URLSearchParams({ idVideo: idVideo })
            }).catch(()=>{/* silencioso */});
            window.location.href = `visualizar_curso.php?idCurso=${idCurso}&idVideo=${nextVideoId}`;
        });
        overlay.appendChild(btnNext);
    } else if (nextModuleFirstVideoId) {
        const btnNextModule = createBtn('<i class="fa-solid fa-arrow-right"></i> Avançar Próximo Módulo', 'primary', async () => {
            await fetch("finalizar_video.php", {
                method: "POST",
                body: new URLSearchParams({ idVideo: idVideo })
            }).catch(()=>{/* silencioso */});
            window.location.href = `visualizar_curso.php?idCurso=${idCurso}&idVideo=${nextModuleFirstVideoId}`;
        });
        overlay.appendChild(btnNextModule);
    } else if (isLastOverall) {
        const btnConclude = createBtn('<i class="fa-solid fa-check"></i> Concluir Curso', 'primary', async () => {
            // marcar vídeo final garantido e depois redirecionar para lista de cursos
            await fetch("finalizar_video.php", {
                method: "POST",
                body: new URLSearchParams({ idVideo: idVideo })
            }).catch(()=>{/* silencioso */});

            alert("Parabéns — você concluiu o curso!");
            window.location.href = 'cursos.php';
        });
        overlay.appendChild(btnConclude);
    }

    overlay.style.display = 'flex';
}

/* Mostrar overlay se já concluído ao carregar (caso não feito no loadedmetadata) */
if (concluido === 1 && !overlaySuppressed) {
    // se metadata ainda não carregou, showOverlayButtons será chamada no loadedmetadata,
    // mas caso já tenha carregado, garanto exibir.
    setTimeout(() => {
        if (player.readyState >= 1 && !overlaySuppressed) showOverlayButtons();
    }, 200);
}
</script>

</body>
</html>
