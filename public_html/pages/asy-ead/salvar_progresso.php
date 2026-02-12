<?php
require_once __DIR__ . '/../../../app/core/init.php';
require_once __DIR__ . '/../../../app/core/sessao_segura.php';

if (!$usuario) exit;

$idUsuario = $usuario['idUsuario'];
$idVideo = $_POST['idVideo'] ?? null;
$tempo = $_POST['tempo'] ?? 0;

if (!$idVideo) exit;

// pega data só quando concluir (aqui não usamos)
$dataConclusao = null;

$stmt = $pdo->prepare("
    INSERT INTO asy_progresso_videos (idUsuario, idVideo, tempoAssistido, concluido, dataConclusao)
    VALUES (?, ?, ?, 0, NULL)
    ON DUPLICATE KEY UPDATE
        tempoAssistido = VALUES(tempoAssistido)
");

$stmt->execute([$idUsuario, $idVideo, $tempo]);
