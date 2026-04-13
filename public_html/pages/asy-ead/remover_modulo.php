<?php
require_once __DIR__ . '/../../../app/core/init.php';
require_once __DIR__ . '/../../../app/core/sessao_segura.php';

header('Content-Type: application/json');

$idModulo = filter_input(INPUT_POST, 'idModulo', FILTER_VALIDATE_INT);

if(!$idModulo){
    echo json_encode(['ok' => false, 'msg' => 'Módulo inválido.']);
    exit;
}

// Verifica se o módulo ainda tem vídeos (proteção extra)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM asy_videos WHERE idModulo = ?");
$stmt->execute([$idModulo]);
$temVideos = $stmt->fetchColumn();

if($temVideos > 0){
    echo json_encode(['ok' => false, 'msg' => 'O módulo possui vídeos e não pode ser removido.']);
    exit;
}

// Remover módulo
$stmt = $pdo->prepare("DELETE FROM asy_modulos WHERE idModulo = ?");
$stmt->execute([$idModulo]);

echo json_encode(['ok' => true, 'msg' => 'Módulo removido com sucesso!']);
