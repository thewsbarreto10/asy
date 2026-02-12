<?php
require_once __DIR__ . '/../../core/init.php';
require_once __DIR__ . '/../../core/sessao_segura.php';

header('Content-Type: application/json');

// Verifica se está logado
if (!$usuario) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado: sessão expirada.']);
    exit;
}

// Apenas administradores podem alterar status de usuários
if ($usuario['idPermissaoUsuario'] != 1) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado: somente administradores podem alterar status.']);
    exit;
}

// Obtém parâmetros
$id = isset($_POST['idUsuario']) ? (int) $_POST['idUsuario'] : 0;
$status = trim($_POST['statusUsuario'] ?? '');

if (!$id || !in_array($status, ['ativo', 'inativo'])) {
    echo json_encode(['success' => false, 'error' => 'Parâmetros inválidos.']);
    exit;
}

// Impede o admin de se inativar
if ($id === (int)$usuario['idUsuario']) {
    echo json_encode(['success' => false, 'error' => 'Você não pode alterar o status do seu próprio usuário.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE asy_usuarios SET statusUsuario = :status WHERE idUsuario = :id");
    $stmt->execute([
        ':status' => $status,
        ':id' => $id
    ]);

    echo json_encode([
        'success' => true,
        'mensagem' => "Usuário " . ($status === 'inativo' ? 'inativado' : 'reativado') . " com sucesso!"
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erro ao atualizar o status: ' . $e->getMessage()]);
}
