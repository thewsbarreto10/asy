<?php
require_once __DIR__ . '/../../core/init.php';
require_once __DIR__ . '/../../core/sessao_segura.php';

header('Content-Type: application/json');

$idCurso = (int)($_GET['idCurso'] ?? 0);
$idUsuario = $usuario['idUsuario'];

if (!$idCurso) {
    echo json_encode(['success' => false, 'error' => 'Curso inválido']);
    exit;
}

// Busca módulos do curso
$stmt = $pdo->prepare("
    SELECT idModulo, nomeModulo, ordemModulo
    FROM asy_modulos
    WHERE idCurso = :idCurso
    ORDER BY ordemModulo ASC
");
$stmt->execute(['idCurso' => $idCurso]);
$modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Para cada módulo, busca os vídeos e o progresso do usuário
foreach ($modulos as &$mod) {
    $stmt = $pdo->prepare("
        SELECT v.idVideo, v.tituloVideo, v.urlVideo, v.ordemVideo,
               IFNULL(p.concluido, 0) AS concluido,
               IFNULL(p.tempoAssistido, 0) AS tempoAssistido
        FROM asy_videos v
        LEFT JOIN asy_progresso_videos p
            ON v.idVideo = p.idVideo AND p.idUsuario = :idUsuario
        WHERE v.idModulo = :idModulo
        ORDER BY v.ordemVideo ASC
    ");
    $stmt->execute([
        'idUsuario' => $idUsuario,
        'idModulo' => $mod['idModulo']
    ]);
    $mod['videos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode(['success' => true, 'modulos' => $modulos]);
