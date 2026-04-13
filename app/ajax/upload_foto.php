<?php
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/sessao_segura.php';

header('Content-Type: application/json');

// Verifica login
if (empty($_SESSION['idUsuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

$idUsuario = (int) $_SESSION['idUsuario'];

// Verifica se o arquivo foi enviado
if (empty($_FILES['foto'])) {
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado.']);
    exit;
}

$foto = $_FILES['foto'];

// Verifica erros no upload
if ($foto['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Erro no upload da imagem.']);
    exit;
}

// Caminho base (dentro de app/controllers)
$baseDir = __DIR__ . "/../controllers/usuario/{$idUsuario}/foto-perfil/";

// Cria diretório se não existir
if (!is_dir($baseDir)) {
    if (!mkdir($baseDir, 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'Falha ao criar diretório do usuário.']);
        exit;
    }
}

// Extensão segura
$ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
$permitidas = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($ext, $permitidas)) {
    echo json_encode(['success' => false, 'message' => 'Formato de imagem inválido.']);
    exit;
}

// Define nome e caminho final do arquivo
$nomeArquivo = "{$idUsuario}_user_icon.{$ext}";
$caminhoCompleto = $baseDir . $nomeArquivo;

// Move arquivo temporário
if (!move_uploaded_file($foto['tmp_name'], $caminhoCompleto)) {
    echo json_encode(['success' => false, 'message' => 'Falha ao mover a imagem enviada.']);
    exit;
}

// Redimensiona para 128x128 (corte central)
$img = imagecreatefromstring(file_get_contents($caminhoCompleto));
if (!$img) {
    echo json_encode(['success' => false, 'message' => 'Falha ao processar imagem.']);
    exit;
}

$largura = imagesx($img);
$altura = imagesy($img);
$tamanho = min($largura, $altura);
$x = ($largura - $tamanho) / 2;
$y = ($altura - $tamanho) / 2;

$recortada = imagecrop($img, ['x' => $x, 'y' => $y, 'width' => $tamanho, 'height' => $tamanho]);
$final = imagecreatetruecolor(128, 128);
imagecopyresampled($final, $recortada, 0, 0, 0, 0, 128, 128, $tamanho, $tamanho);

if ($ext === 'png') {
    imagepng($final, $caminhoCompleto);
} else {
    imagejpeg($final, $caminhoCompleto, 90);
}

imagedestroy($img);
imagedestroy($recortada);
imagedestroy($final);

// Atualiza caminho no banco (mantém registro interno)
$caminhoBanco = "app/controllers/usuario/{$idUsuario}/foto-perfil/{$nomeArquivo}";
$stmt = $pdo->prepare("UPDATE asy_usuarios SET foto_perfil = :foto WHERE idUsuario = :id");
$stmt->execute([
    'foto' => $caminhoBanco,
    'id' => $idUsuario
]);

// Caminho público via proxy
$caminhoPublico = "proxy/usuario/foto.php?id={$idUsuario}";

// Retorna JSON compatível com o front
echo json_encode([
    'success' => true,
    'message' => 'Foto atualizada com sucesso!',
    'path' => $caminhoPublico
]);
