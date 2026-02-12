<?php
require_once __DIR__ . '/../core/init.php';

header('Content-Type: application/json');

$erro = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitização
    $nomeCurso       = trim($_POST['nomeCurso'] ?? '');
    $descricaoCurso  = trim($_POST['descricaoCurso'] ?? '');
    $idMinisterio    = $_POST['idMinisterio'] ?? null;
    $statusCurso     = $_POST['statusCurso'] ?? 'ativo';

    // Validação
    if (!$nomeCurso || !$descricaoCurso || !$idMinisterio) {
        echo json_encode(['erro' => 'Preencha todos os campos obrigatórios.']);
        exit;
    }

    try {

        // 1) Inserir curso sem imagem
        $stmt = $pdo->prepare("
            INSERT INTO asy_cursos (
                nomeCurso,
                descricaoCurso,
                idMinisterio,
                statusCurso,
                dataCriacao
            ) VALUES (
                :nomeCurso,
                :descricaoCurso,
                :idMinisterio,
                :statusCurso,
                NOW()
            )
        ");

        $stmt->execute([
            'nomeCurso'     => $nomeCurso,
            'descricaoCurso'=> $descricaoCurso,
            'idMinisterio'  => $idMinisterio,
            'statusCurso'   => $statusCurso
        ]);

        // Pega o ID inserido
        $idCurso = $pdo->lastInsertId();

        $urlImagem = null;

        // 2) Se houver imagem, processar upload
        if (!empty($_FILES['cardImagem']['name']) && $_FILES['cardImagem']['error'] === UPLOAD_ERR_OK) {

            $nomeOriginal = $_FILES['cardImagem']['name'];
            $ext = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
            $extPermitida = ['jpg','jpeg','png','gif','webp'];

            if (!in_array($ext, $extPermitida)) {
                echo json_encode(['erro' => 'Extensão de imagem inválida. Use JPG, PNG, GIF ou WEBP.']);
                exit;
            }

            // Cria diretório: /assets/img/cursos/{idCurso}/CARD/
            $destinoDir = ASSETS_PATH . "/img/cursos/$idCurso/CARD/";
            if (!is_dir($destinoDir)) {
                mkdir($destinoDir, 0755, true);
            }

            // Nome seguro para o arquivo
            $nomeArquivo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nomeCurso) . '.' . $ext;
            $destinoPath = $destinoDir . $nomeArquivo;

            if (!move_uploaded_file($_FILES['cardImagem']['tmp_name'], $destinoPath)) {
                echo json_encode(['erro' => 'Erro ao mover a imagem enviada.']);
                exit;
            }

            // Monta URL pública
            $urlImagem = ASSETS_URL . "/img/cursos/$idCurso/CARD/$nomeArquivo";

            // Atualiza registro com imagem
            $stmtUpdate = $pdo->prepare("UPDATE asy_cursos SET imagemCurso = :url WHERE idCurso = :id");
            $stmtUpdate->execute([
                'url' => $urlImagem,
                'id'  => $idCurso
            ]);
        }

        $success = "Curso cadastrado com sucesso!";

    } catch (PDOException $e) {
        $erro = "Erro ao cadastrar curso: " . $e->getMessage();
    }
}

// Retorno JSON
echo json_encode(['erro' => $erro, 'success' => $success]);
