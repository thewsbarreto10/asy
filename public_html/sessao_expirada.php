<?php

require_once __DIR__ . '/../app/core/init.php';

if (!isset($_SESSION['sessao_expirada'])) {
    header('Location: ' . PUBLIC_URL . '/');
    exit;
}

unset($_SESSION['sessao_expirada']);
?>

<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>Sessão Expirada</title>

    <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS personalizado -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/estilo_index.css?v=<?= time() ?>">

  </head>

  <body>
    <div class="login-container">
      <div class="d-grid">
        <h2 align="center">Sessão expirada</h2>
      </div>

      <br />

      <p align="center">Você será redirecionado em alguns segundos, caso contrário, clique no botão abaixo</p>

      <br />

      <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg" onclick="myFunction()">Voltar ao login</button>
      </div>

    </div>

    <script>
      function myFunction() {
          window.location.href = '/asy/';
      }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
      setTimeout(function(){
          window.location.href = "/asy/";
      }, 5000);
    </script>

  </body>
</html>