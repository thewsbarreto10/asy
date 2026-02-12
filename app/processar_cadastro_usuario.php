<?php
require_once __DIR__ . '/../core/init.php'; // já cria $pdo

header('Content-Type: application/json');

$erro = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Captura e sanitiza os dados
    $nome               = trim($_POST['nomeUsuario'] ?? '');
    $dataNascimento     = $_POST['dataNascimentoUsuario'] ?? '';
    $idMinisterio       = $_POST['idMinisterioUsuario'] ?? null;
    $email              = trim($_POST['emailUsuario'] ?? '');
    $senha              = $_POST['senhaUsuario'] ?? '';
    $telefone           = preg_replace('/\D/', '', $_POST['telefoneUsuario'] ?? '');
    $cep                = preg_replace('/\D/', '', $_POST['cepUsuario'] ?? '');
    $endereco           = trim($_POST['enderecoUsuario'] ?? '');
    $numero             = trim($_POST['numeroEnderecoUsuario'] ?? '');
    $complemento        = trim($_POST['complementoEnderecoUsuario'] ?? '');
    $bairro             = trim($_POST['bairroEnderecoUsuario'] ?? '');
    $cidade             = trim($_POST['cidadeUsuario'] ?? '');
    $estado             = strtoupper(trim($_POST['estadoUsuario'] ?? ''));
    $status             = 'ativo';

    // Validação simples
    if ($nome && $dataNascimento && $email && $senha && $cep && $endereco && $numero && $bairro && $cidade && $estado) {
        try {
            // Verifica se o e-mail já existe
            $stmt = $pdo->prepare("SELECT idUsuario FROM asy_usuarios WHERE emailUsuario = :email LIMIT 1");
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                $erro = "Usuário já cadastrado.";
            } else {
                // Hash seguro da senha
                $hashSenha = password_hash($senha, PASSWORD_DEFAULT);

                // Inserir novo usuário
                $stmt = $pdo->prepare("
                    INSERT INTO asy_usuarios (
                        nomeCompletoUsuario,
                        dataNascimentoUsuario,
                        idMinisterioUsuario,
                        emailUsuario,
                        senhaUsuario,
                        telefoneUsuario,
                        cepUsuario,
                        enderecoUsuario,
                        numeroEnderecoUsuario,
                        complementoEnderecoUsuario,
                        bairroEnderecoUsuario,
                        cidadeUsuario,
                        UF,
                        statusUsuario,
                        dataCriacaoUsuario
                    ) VALUES (
                        :nome,
                        :data,
                        :idMinisterio,
                        :email,
                        :senha,
                        :telefone,
                        :cep,
                        :endereco,
                        :numero,
                        :complemento,
                        :bairro,
                        :cidade,
                        :estado,
                        :status,
                        NOW()
                    )
                ");

                $stmt->execute([
                    'nome'         => $nome,
                    'data'         => date('Y-m-d', strtotime(str_replace('/', '-', $dataNascimento))),
                    'idMinisterio' => $idMinisterio,
                    'email'        => $email,
                    'senha'        => $hashSenha,
                    'telefone'     => $telefone,
                    'cep'          => $cep,
                    'endereco'     => $endereco,
                    'numero'       => $numero,
                    'complemento'  => $complemento,
                    'bairro'       => $bairro,
                    'cidade'       => $cidade,
                    'estado'       => $estado,
                    'status'       => $status
                ]);

                $success = "Usuário cadastrado com sucesso!";
            }

        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar usuário: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha todos os campos obrigatórios.";
    }
}

// Retorna JSON para o AJAX
echo json_encode(['erro' => $erro, 'success' => $success]);
