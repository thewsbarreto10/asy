<?php
require_once __DIR__ . '/../../../app/core/init.php';
require_once __DIR__ . '/../../../app/core/sessao_segura.php';

header('Content-Type: application/json');

if (!$usuario || ($usuario['idPermissaoUsuario'] ?? null) != 1) {
    echo json_encode(['ok' => false, 'msg' => 'Acesso negado']);
    exit;
}

$idVideo = $_POST['idVideo'] ?? null;
if (!$idVideo) {
    echo json_encode(['ok' => false, 'msg' => 'ID do vídeo inválido']);
    exit;
}

// Buscar vídeo
$stmt = $pdo->prepare("SELECT urlVideo FROM asy_videos WHERE idVideo = ?");
$stmt->execute([$idVideo]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    echo json_encode(['ok' => false, 'msg' => 'Vídeo não encontrado']);
    exit;
}

// Remove arquivo físico
$caminho = __DIR__ . "/$video[urlVideo]";
if (file_exists($caminho)) {
    unlink($caminho);
}

// Remover DB
$stmt = $pdo->prepare("DELETE FROM asy_videos WHERE idVideo = ?");
$ok = $stmt->execute([$idVideo]);

echo json_encode([
    'ok' => $ok,
    'msg' => $ok ? 'Vídeo removido com sucesso!' : 'Erro ao remover vídeo'
]);
