<?php
require_once __DIR__ . '/../../../app/core/init.php';
require_once __DIR__ . '/../../../app/core/sessao_segura.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = (int)($_POST['idUsuario'] ?? 0);

    if ($idUsuario <= 0) {
        echo json_encode(['erro' => 'Usuário inválido.']);
        exit;
    }

    // Gerar senha temporária
    $senhaTemp = bin2hex(random_bytes(4)); // 8 caracteres hex
    $hash = password_hash($senhaTemp, PASSWORD_DEFAULT);

    // Atualiza senha e marca como "precisa trocar"
    $stmt = $pdo->prepare("
        UPDATE asy_usuarios 
        SET senhaUsuario = :senha, forcarTrocaSenha = 1 
        WHERE idUsuario = :id
    ");
    $stmt->execute([
        'senha' => $hash,
        'id' => $idUsuario
    ]);

    // Retorna senha temporária para mostrar admin
    echo json_encode([
        'erro' => '',
        'success' => true,
        'senhaTemp' => $senhaTemp
    ]);
    exit;
}

echo json_encode(['erro' => 'Requisição inválida.']);
