<?php

    //OBRIGATÓRIO
    require_once __DIR__ . '/../../../app/core/init.php';
    require_once __DIR__ . '/../../../app/core/sessao_segura.php';


    // Se o usuário não tiver logado ou com token válido, não é possível acessar diretamente a página
    if (!$usuario) {
        header('Location: /asy/');
        exit;
    }

    // Busca todos os cursos que o usuário está matriculado
    $stmt = $pdo->prepare("
        SELECT c.idCurso, c.nomeCurso, c.descricaoCurso, c.statusCurso, c.imagemCurso, m.descricaoMinisterio
        FROM asy_usuario_curso uc
        JOIN asy_cursos c ON uc.idCurso = c.idCurso
        LEFT JOIN asy_ministerios m ON c.idMinisterio = m.idMinisterio
        WHERE uc.idUsuario = :idUsuario AND c.statusCurso = 'ativo'
        ORDER BY c.nomeCurso ASC
        ");
    $stmt->execute(['idUsuario' => $usuario['idUsuario']]);
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Função para calcular progresso do curso
    function progressoCurso($pdo, $idUsuario, $idCurso) {
        $stmtTotal = $pdo->prepare("
            SELECT COUNT(v.idVideo) 
            FROM asy_videos v
            JOIN asy_modulos m ON v.idModulo = m.idModulo
            WHERE m.idCurso = :idCurso
        ");
        $stmtTotal->execute(['idCurso' => $idCurso]);
        $totalVideos = (int)$stmtTotal->fetchColumn();

        if ($totalVideos === 0) return 0;

        $stmtConcluidos = $pdo->prepare("
            SELECT COUNT(p.idProgresso)
            FROM asy_progresso_videos p
            JOIN asy_videos v ON p.idVideo = v.idVideo
            JOIN asy_modulos m ON v.idModulo = m.idModulo
            WHERE p.idUsuario = :idUsuario
            AND m.idCurso = :idCurso
            AND p.concluido = 1
        ");
        $stmtConcluidos->execute(['idUsuario' => $idUsuario, 'idCurso' => $idCurso]);
        $concluidos = (int)$stmtConcluidos->fetchColumn();

        return round(($concluidos / $totalVideos) * 100);
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">

        <title>Cursos - ASY Gospel Church</title>

        <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">

        <link rel="stylesheet" href="<?= ASSETS_URL ?>/plugins/fontawesome-free/css/all.min.css">
        <link rel="stylesheet" href="<?= ASSETS_URL ?>/dist/css/adminlte.min.css">
        <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/estilo_cursos.css">

        <style>

            .filtro-cursos {
                flex-wrap: wrap;
                gap: 2rem; /* espaçamento médio */
            }

            .filtro-select label {
                font-weight: bold;
            }

            .form-select-sm {
                min-width: 150px;
                border-radius: 10px;
                padding: 5px 12px;
                transition: all 0.2s;
            }

            .form-select-sm:focus {
                border-color: #6610f2;
                box-shadow: 0 0 5px rgba(102, 16, 242, 0.5);
            }

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
                        <h4 class="text-center"><i class="fas fa-graduation-cap"></i> Meus Cursos</h4>
                        <hr>

                        <div class="filtro-cursos mb-4 d-flex flex-wrap align-items-center gap-4">
                            <div class="filtro-select">
                                <label for="filtro-status" class="fw-bold me-2">Status:</label>
                                <select id="filtro-status" class="form-select form-select-sm">
                                    <option value="todos" selected>Todos</option>
                                    <option value="ativo">Em andamento</option>
                                    <option value="inativo">Finalizado</option>
                                </select>
                            </div>

                            <div class="filtro-select">
                                <label for="filtro-ministerio" class="fw-bold me-2">Ministério:</label>
                                <select id="filtro-ministerio" class="form-select form-select-sm">
                                    <option value="todos" selected>Todos</option>
                                    <?php
                                        $ministerios = [];
                                        foreach ($cursos as $c) {
                                            if (!empty($c['descricaoMinisterio'])) {
                                                $ministerios[$c['descricaoMinisterio']] = true;
                                            }
                                        }
                                        foreach (array_keys($ministerios) as $min) {
                                            echo '<option value="' . htmlspecialchars($min) . '">' . htmlspecialchars($min) . '</option>';
                                        }
                                    ?>
                                </select>
                            </div>

                            <div class="filtro-select">
                                <label for="filtro-ordem" class="fw-bold me-2">Ordenar por:</label>
                                <select id="filtro-ordem" class="form-select form-select-sm">
                                    <option value="alfabetica" selected>Ordem alfabética</option>
                                    <option value="concluidos">Finalizado</option>
                                    <option value="em-andamento">Em andamento</option>
                                </select>
                            </div>
                        </div>


                        <div class="cursos-grid">
                            <?php foreach ($cursos as $curso): 
                                $progresso = progressoCurso($pdo, $usuario['idUsuario'], $curso['idCurso']);
                            ?>
                                <a href="visualizar_curso.php?idCurso=<?= $curso['idCurso'] ?>" class="card-link-curso" 
   data-status="<?= $curso['statusCurso'] ?>" 
   data-ministerio="<?= htmlspecialchars($curso['descricaoMinisterio'] ?? '') ?>"
   data-progresso="<?= $progresso ?>"> <!-- adiciona o progresso real -->

                                    <div class="card card-curso">
                                        <?php $imgCurso = $curso['imagemCurso'] ?: ASSETS_URL . '/img/curso-thumbnail-default.jpg'; ?>
                                        <img src="<?= $imgCurso ?>" class="thumbnail" alt="Curso <?= htmlspecialchars($curso['nomeCurso']) ?>">
                                        <div class="card-body">
                                            <h5><?= htmlspecialchars($curso['nomeCurso']) ?></h5>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $progresso ?>%" aria-valuenow="<?= $progresso ?>" aria-valuemin="0" aria-valuemax="100"><?= $progresso ?>%</div>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            Ministério: <?= htmlspecialchars($curso['descricaoMinisterio'] ?? '-') ?><br>
                                            Status: <?= htmlspecialchars($curso['statusCurso']) ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <script src="<?= ASSETS_URL ?>/plugins/jquery/jquery.min.js"></script>
        <script src="<?= ASSETS_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="<?= ASSETS_URL ?>/dist/js/adminlte.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="<?= ASSETS_URL ?>/js/logout.js?v=<?= time() ?>"></script>

        <script>
function filtrarCursos() {
    const statusFiltro = $('#filtro-status').val();
    const ministerioFiltro = $('#filtro-ministerio').val();
    const ordem = $('#filtro-ordem').val();

    let cursosArray = $('.card-link-curso').toArray();

    // Filtrar
    cursosArray.forEach(el => {
        const status = $(el).data('status'); // do banco, pode ser "ativo", "finalizado", etc
        const ministerio = $(el).data('ministerio');
        const progresso = parseInt($(el).data('progresso'));
        let mostrar = true;

        // Definir status real: se progresso == 100%, considera finalizado
        const statusReal = progresso === 100 ? 'finalizado' : 'ativo';

        // Filtro Status
        if(statusFiltro !== 'todos' && statusReal !== statusFiltro) mostrar = false;
        if(ministerioFiltro !== 'todos' && ministerio !== ministerioFiltro) mostrar = false;

        $(el).toggle(mostrar);
    });

    // Ordenar
    cursosArray.sort((a, b) => {
        const aEl = $(a);
        const bEl = $(b);

        const progressoA = parseInt(aEl.data('progresso'));
        const progressoB = parseInt(bEl.data('progresso'));

        const nomeA = aEl.find('h5').text().toLowerCase();
        const nomeB = bEl.find('h5').text().toLowerCase();

        if(ordem === 'alfabetica') {
            return nomeA.localeCompare(nomeB);
        } else if(ordem === 'concluidos') {
            return progressoB - progressoA; // mais concluídos primeiro
        } else if(ordem === 'em-andamento') {
            return progressoA - progressoB; // menos concluídos primeiro
        }
    });

    // Reorganizar no DOM
    $('.cursos-grid').html(cursosArray);
}

// Executa ao mudar qualquer filtro
$('#filtro-status, #filtro-ministerio, #filtro-ordem').change(filtrarCursos);

// Inicializa ordenação ao carregar a página
$(document).ready(filtrarCursos);


        </script>
        
    </body>
</html>
