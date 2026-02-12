<?php
// sessao_segura.php

// Garante que o init.php já foi carregado para ter $pdo e sessão iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define tempo máximo de inatividade (15 minutos)
define('TEMPO_MAX_INATIVIDADE', 900); // 900 segundos = 15 minutos

// Verifica inatividade
if (isset($_SESSION['ultimoAcesso']) && (time() - $_SESSION['ultimoAcesso'] > TEMPO_MAX_INATIVIDADE)) {
    session_unset();
    session_destroy();
    $usuario = null;
    exit('Sessão expirada. Faça login novamente.');
}

// Atualiza último acesso
$_SESSION['ultimoAcesso'] = time();

// Se não existir idUsuario ou token na sessão, considera não logado
if (!isset($_SESSION['idUsuario']) || !isset($_SESSION['token'])) {
    $usuario = null; // usuário não logado
} else {
    try {
        // Busca dados completos do usuário, incluindo permissão
        $stmt = $pdo->prepare("
            SELECT 
                u.idUsuario,
                u.nomeCompletoUsuario,
                u.emailUsuario,
                u.idPermissaoUsuario,
                u.foto_perfil,
                p.descricaoPermissaoUsuario
            FROM asy_usuarios u
            INNER JOIN asy_PermissaoUsuario p ON p.idPermissaoUsuario = u.idPermissaoUsuario
            WHERE u.idUsuario = :id AND u.tokenUsuario = :token AND u.statusUsuario = 'ativo'
            LIMIT 1
        ");
        $stmt->execute([
            'id' => $_SESSION['idUsuario'],
            'token' => $_SESSION['token']
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se não encontrar usuário, limpa sessão
        if (!$usuario) {
            session_unset();
            session_destroy();
            $usuario = null;
        }

    } catch (PDOException $e) {
        // Em caso de erro, limpa sessão
        session_unset();
        session_destroy();
        $usuario = null;
    }
}
