<?php
require_once __DIR__ . '/../../../app/core/init.php';
require_once __DIR__ . '/../../../app/core/sessao_segura.php';

header('Content-Type: application/json');

if (!$usuario || ($usuario['idPermissaoUsuario'] ?? null) != 1) {
    echo json_encode(['ok' => false, 'msg' => 'Acesso negado']);
    exit;
}

$idCurso = $_POST['idCurso'] ?? null;
$nomeModulo = trim($_POST['nomeModulo'] ?? '');
$ordemModulo = $_POST['ordemModulo'] ?? null;

if (!$idCurso || !$nomeModulo || !$ordemModulo) {
    echo json_encode(['ok' => false, 'msg' => 'Dados incompletos']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO asy_modulos (idCurso, nomeModulo, ordemModulo) VALUES (?, ?, ?)");
$ok = $stmt->execute([$idCurso, $nomeModulo, $ordemModulo]);

if ($ok) {
    echo json_encode(['ok' => true, 'msg' => 'Módulo criado com sucesso!']);
} else {
    echo json_encode(['ok' => false, 'msg' => 'Erro ao criar módulo']);
}
