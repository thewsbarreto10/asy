<?php
require_once __DIR__ . '/../app/core/init.php';
require_once __DIR__ . '/../app/core/sessao_segura.php';

$pdo = $GLOBALS['pdo']; 

$idUsuario = $_SESSION['idUsuario'] ?? 0;
if (!$idUsuario) {
    header('Location: /asy/');
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $novaSenha = trim($_POST['novaSenha'] ?? '');
    if($novaSenha){
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            UPDATE asy_usuarios 
            SET senhaUsuario = :senha, forcarTrocaSenha = 0
            WHERE idUsuario = :id
        ");
        $stmt->execute(['senha' => $hash, 'id' => $idUsuario]);
        echo "Redirecionando...";
        exit;
    } else {
        echo "<p>Preencha todos os campos!</p>";
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>Login - ASY Gospel Church</title>

    <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- CSS personalizado -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/estilo_index.css?v=<?= time() ?>">

  </head>

  <body>
    <div class="login-container">
      <div class="login-image">
        <img src="<?= ASSETS_URL ?>/img/asy_logo.png" alt="Logo">
      </div>

      <form id="loginForm">

        <div class="form-group">
          <i class="bi bi-lock-fill icon-left"></i>
          <input type="password" class="form-control" name="novaSenha" placeholder="Nova Senha" required>
          <span class="hint-text">Senha</span>
          <i class="bi bi-eye-fill icon-right" id="toggleSenha"></i>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary btn-lg">Atualizar senha</button>
        </div>
      </form>
    </div>

    <!-- JS personalizado -->
    <script>
        const PROXY_URL = '<?= PUBLIC_URL ?>/proxy';
    </script>

    <script src="<?= ASSETS_URL ?>/js/script_trocar-senha.js?v=<?= time() ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  </body>
</html>