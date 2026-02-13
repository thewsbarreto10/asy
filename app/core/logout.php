<?php
session_start();

$config = require __DIR__ . '/../config.php';

if (isset($_SESSION['idUsuario'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']};charset={$config['charset']}",
            $config['user'],
            $config['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        // Remove token do banco
        $stmt = $pdo->prepare("UPDATE asy_usuarios SET tokenUsuario = NULL WHERE idUsuario = :id");
        $stmt->execute(['id' => $_SESSION['idUsuario']]);
    } catch (PDOException $e) {
        // Log opcional
    }
}

// Limpa a sessão
$_SESSION = [];
session_destroy();

// Redireciona para a tela de login
header("Location: /?logout=1");

exit;
