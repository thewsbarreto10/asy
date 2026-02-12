<?php
require_once __DIR__ . '/../../../app/core/init.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    exit('ID inválido.');
}

// Busca o caminho da imagem no banco
$stmt = $pdo->prepare("SELECT foto_perfil FROM asy_usuarios WHERE idUsuario = :id");
$stmt->execute(['id' => $id]);
$fotoPerfil = $stmt->fetchColumn();

// Caminho base físico (app/controllers/usuario/{id}/foto-perfil)
if ($fotoPerfil && file_exists(__DIR__ . '/../../../' . $fotoPerfil)) {
    $arquivo = __DIR__ . '/../../../' . $fotoPerfil;
} else {
    // Usa a imagem padrão se não encontrar
    $arquivo = __DIR__ . '/../../assets/img/user-profile-img-default.png';
}

// Define tipo MIME corretamente
$ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
switch ($ext) {
    case 'jpg':
    case 'jpeg':
        header('Content-Type: image/jpeg');
        break;
    case 'png':
        header('Content-Type: image/png');
        break;
    case 'gif':
        header('Content-Type: image/gif');
        break;
    default:
        header('Content-Type: application/octet-stream');
}

// Evita cache (para sempre atualizar no refresh)
header('Cache-Control: no-cache, must-revalidate');

// Envia imagem
readfile($arquivo);
exit;
