<?php
require_once __DIR__ . '/../../../app/core/init.php';
require_once __DIR__ . '/../../../app/core/sessao_segura.php';

header("Content-Type: application/json");

// Segurança
if (!$usuario || ($usuario['idPermissaoUsuario'] ?? null) != 1) {
    echo json_encode(["ok" => false, "msg" => "Acesso negado"]);
    exit;
}

// Validação
$idVideo = intval($_POST['idVideo'] ?? 0);
if (!$idVideo || !isset($_FILES['arquivoVideo'])) {
    echo json_encode(["ok" => false, "msg" => "Dados inválidos"]);
    exit;
}

// Buscar vídeo no banco
$stmt = $pdo->prepare("SELECT urlVideo FROM asy_videos WHERE idVideo = ?");
$stmt->execute([$idVideo]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    echo json_encode(["ok" => false, "msg" => "Vídeo não encontrado"]);
    exit;
}

$caminhoRelativo = $video['urlVideo'];

// O CAMINHO CORRETO É ESTE:
$caminhoAbsoluto = __DIR__ . "/" . $caminhoRelativo;

$diretorio = dirname($caminhoAbsoluto);
$nomeArquivo = basename($caminhoAbsoluto);

// Verificar se diretório existe
if (!is_dir($diretorio)) {
    echo json_encode([
        "ok" => false,
        "msg" => "Diretório não encontrado: $diretorio"
    ]);
    exit;
}

if (!is_writable($diretorio)) {
    echo json_encode([
        "ok" => false,
        "msg" => "Sem permissão para salvar em: $diretorio"
    ]);
    exit;
}

// Substituir arquivo
$tmp = $_FILES['arquivoVideo']['tmp_name'];

if (!move_uploaded_file($tmp, $caminhoAbsoluto)) {
    echo json_encode(["ok" => false, "msg" => "Falha ao salvar novo vídeo"]);
    exit;
}

echo json_encode(["ok" => true, "msg" => "Vídeo substituído com sucesso"]);
exit;
