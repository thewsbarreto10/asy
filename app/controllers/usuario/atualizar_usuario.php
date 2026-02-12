<?php

require_once __DIR__ . '/../../core/init.php';
require_once __DIR__ . '/../../core/sessao_segura.php';

header('Content-Type: application/json');

// Verifica se é administrador
if (!$usuario || $usuario['idPermissaoUsuario'] != 1) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit;
}

$id = (int)($_POST['idUsuario'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID do usuário inválido.']);
    exit;
}

$campos = [];
$params = ['id' => $id];

// Função auxiliar para limpar e validar strings
function limpar($valor) {
    return trim(filter_var($valor, FILTER_SANITIZE_SPECIAL_CHARS));
}

// Nome completo
if (isset($_POST['nomeUsuario'])) {
    $campos[] = "nomeCompletoUsuario = :nome";
    $params['nome'] = limpar($_POST['nomeUsuario']);
}

// Data de nascimento (converte dd/mm/yyyy → yyyy-mm-dd)
if (!empty($_POST['dataNascimentoUsuario'])) {
    $data = str_replace('/', '-', $_POST['dataNascimentoUsuario']);
    $dataConvertida = date('Y-m-d', strtotime($data));
    $campos[] = "dataNascimentoUsuario = :dataNascimento";
    $params['dataNascimento'] = $dataConvertida;
}

// Telefone
if (isset($_POST['telefoneUsuario'])) {
    $telefone = preg_replace('/\D/', '', $_POST['telefoneUsuario']); // remove tudo que não for número
    $campos[] = "telefoneUsuario = :telefone";
    $params['telefone'] = $telefone;
}

// Ministério
if (isset($_POST['idMinisterioUsuario'])) {
    $campos[] = "idMinisterioUsuario = :ministerio";
    $params['ministerio'] = (int)$_POST['idMinisterioUsuario'];
}

// Endereço completo
if (isset($_POST['cepUsuario'])) {
    $cep = preg_replace('/\D/', '', $_POST['cepUsuario']); // só números
    $campos[] = "cepUsuario = :cep";
    $params['cep'] = $cep;
}

if (isset($_POST['enderecoUsuario'])) {
    $campos[] = "enderecoUsuario = :endereco";
    $params['endereco'] = limpar($_POST['enderecoUsuario']);
}

if (isset($_POST['numeroEnderecoUsuario'])) {
    $campos[] = "numeroEnderecoUsuario = :numero";
    $params['numero'] = limpar($_POST['numeroEnderecoUsuario']);
}

if (isset($_POST['complementoEnderecoUsuario'])) {
    $campos[] = "complementoEnderecoUsuario = :complemento";
    $params['complemento'] = limpar($_POST['complementoEnderecoUsuario']);
}

if (isset($_POST['bairroEnderecoUsuario'])) {
    $campos[] = "bairroEnderecoUsuario = :bairro";
    $params['bairro'] = limpar($_POST['bairroEnderecoUsuario']);
}

if (isset($_POST['cidadeUsuario'])) {
    $campos[] = "cidadeUsuario = :cidade";
    $params['cidade'] = limpar($_POST['cidadeUsuario']);
}

if (isset($_POST['estadoUsuario'])) {
    $campos[] = "estadoUsuario = :estado";
    $params['estado'] = strtoupper(limpar($_POST['estadoUsuario']));
}

// E-mail
if (isset($_POST['emailUsuario'])) {
    $campos[] = "emailUsuario = :email";
    $params['email'] = filter_var($_POST['emailUsuario'], FILTER_SANITIZE_EMAIL);
}

// Permissão (caso altere na aba)
if (isset($_POST['idPermissaoUsuario'])) {
    $campos[] = "idPermissaoUsuario = :perm";
    $params['perm'] = (int)$_POST['idPermissaoUsuario'];
}

// Nova senha (se enviada)
if (!empty($_POST['senhaUsuario'])) {
    $hash = password_hash($_POST['senhaUsuario'], PASSWORD_DEFAULT);
    $campos[] = "senhaUsuario = :senha";
    $params['senha'] = $hash;
}

// Nenhum campo alterado
if (empty($campos)) {
    echo json_encode(['success' => false, 'error' => 'Nenhum campo foi alterado.']);
    exit;
}

// Atualiza no banco
try {
    $sql = "UPDATE asy_usuarios SET " . implode(", ", $campos) . " WHERE idUsuario = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'mensagem' => 'Usuário atualizado com sucesso!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erro no banco: ' . $e->getMessage()]);
}
