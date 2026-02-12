<?php
// NÃO iniciar sessão nem incluir config.php aqui
// Este arquivo deve ser incluído pelo proxy que já carregou init.php

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email && $senha) {
        try {
            // Usa o PDO global do init.php
            $pdo = $GLOBALS['pdo'];

            // Busca o usuário incluindo a coluna forcarTrocaSenha
            $stmt = $pdo->prepare("
                SELECT idUsuario, senhaUsuario, statusUsuario, forcarTrocaSenha
                FROM asy_usuarios 
                WHERE emailUsuario = :email 
                LIMIT 1
            ");
            $stmt->execute(['email' => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // 1) Usuário não encontrado
            if (!$usuario) {
                echo json_encode(['erro' => 'Usuário ou senha incorretos.']);
                exit;
            }

            // 2) Usuário INATIVO → bloqueia login
            if ($usuario['statusUsuario'] !== 'ativo') {
                echo json_encode([
                    'erro' => 'Você está com um problema na sua conta. Entre em contato com o pastor.'
                ]);
                exit;
            }

            // 3) Verificar senha
            if (password_verify($senha, $usuario['senhaUsuario'])) {

                // Gera token único criptografado
                $token = bin2hex(random_bytes(32));
                $horario = date('Y-m-d H:i:s');

                // Atualiza token no banco
                $stmt = $pdo->prepare("
                    UPDATE asy_usuarios 
                    SET tokenUsuario = :token, horarioLogonUsuario = :horario 
                    WHERE idUsuario = :id
                ");
                $stmt->execute([
                    'token' => $token,
                    'horario' => $horario,
                    'id' => $usuario['idUsuario']
                ]);

                // Salva sessão
                $_SESSION['token'] = $token;
                $_SESSION['idUsuario'] = $usuario['idUsuario'];

                // Retorna também se o usuário deve trocar a senha
                echo json_encode([
                    'erro' => '',
                    'success' => true,
                    'token' => $token,
                    'forcarTrocaSenha' => (bool)$usuario['forcarTrocaSenha']
                ]);
                exit;

            } else {
                echo json_encode(['erro' => 'Usuário ou senha incorretos.']);
                exit;
            }

        } catch (PDOException $e) {
            echo json_encode(['erro' => 'Erro no banco de dados: ' . $e->getMessage()]);
            exit;
        }
    } else {
        echo json_encode(['erro' => 'Preencha todos os campos.']);
        exit;
    }
}

echo json_encode(['erro' => $erro]);
