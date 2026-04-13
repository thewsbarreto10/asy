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
$id = isset($_POST['idCurso']) ? (int) $_POST['idCurso'] : 0;
$status = trim($_POST['statusCurso'] ?? '');

if (!$id || !in_array($status, ['ativo', 'inativo'])) {
    echo json_encode(['success' => false, 'error' => 'Parâmetros inválidos.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE asy_cursos SET statusCurso = :status WHERE idCurso = :id");
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
