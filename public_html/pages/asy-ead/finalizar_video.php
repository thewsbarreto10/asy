<?php
require_once __DIR__ . '/../../../app/core/init.php';
require_once __DIR__ . '/../../../app/core/sessao_segura.php';

if (!$usuario) exit;

$idUsuario = $usuario['idUsuario'];
$idVideo = $_POST['idVideo'] ?? null;

if (!$idVideo) exit;

$dataConclusao = date("Y-m-d H:i:s");

$stmt = $pdo->prepare("
    INSERT INTO asy_progresso_videos (idUsuario, idVideo, tempoAssistido, concluido, dataConclusao)
    VALUES (?, ?, 0, 1, ?)
    ON DUPLICATE KEY UPDATE
        concluido = 1,
        dataConclusao = VALUES(dataConclusao)
");

$stmt->execute([$idUsuario, $idVideo, $dataConclusao]);
