<?php
  require_once __DIR__ . '/../../../app/core/init.php'; // já puxa config e $pdo
  require_once __DIR__ . '/../../../app/core/sessao_segura.php'; // já puxa config e $pdo

  // Verifica se o usuário está logado
  if (!$usuario) {
      header('Location: /asy/');
      exit;
  }

  // Buscar ministérios disponíveis
  try {
      $stmt = $pdo->query("SELECT idMinisterio, descricaoMinisterio FROM asy_ministerios ORDER BY descricaoMinisterio ASC");
      $ministerios = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
      $ministerios = [];
  }
?>

<!doctype html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>Cadastro - ASY Gospel Church</title>

    <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/estilo_cadastrar-usuario.css?v=<?= time() ?>">
  </head>

  <body>
    <div class="login-container">
      <div class="login-image">
        <img src="<?= ASSETS_URL ?>/img/asy_logo.png" alt="Logo">
      </div>

      <h4 class="text-center mb-4">Cadastrar usuário</h4>

      <form id="cadastroForm">
        <div class="row g-3"> <!-- g-3 adiciona espaçamento entre colunas -->

          <div class="col-md-12">
            <div class="form-group position-relative">
              <i class="bi bi-person-fill icon-left"></i>
              <input type="text" class="form-control" name="nomeUsuario" placeholder="Nome Completo" required>
              <span class="hint-text">Nome Completo</span>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group position-relative">
              <i class="bi bi-calendar-date-fill icon-left"></i>
              <input type="text" class="form-control" name="dataNascimentoUsuario" placeholder="Data de nascimento" required inputmode="numeric" maxlength="10">
              <span class="hint-text">Data de Nascimento</span>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group position-relative">
              <i class="bi bi-whatsapp icon-left"></i>
              <input type="tel" class="form-control" name="telefoneUsuario" placeholder="Celular | WhatsApp" required>
              <span class="hint-text">Celular | WhatsApp</span>
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group position-relative">
              <i class="bi bi-diagram-3-fill icon-left"></i>
              <select class="form-control" name="idMinisterioUsuario" required>
                <option value="" readonly selected>Selecione o ministério</option>
                <?php foreach ($ministerios as $m): ?>
                  <option value="<?= $m['idMinisterio'] ?>"><?= htmlspecialchars($m['descricaoMinisterio']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group position-relative">
              <i class="bi bi-123 icon-left"></i>
              <input type="text" class="form-control" name="cepUsuario" id="cepUsuario" placeholder="CEP" required>
              <span class="hint-text">CEP</span>
            </div>
          </div>

          <div class="col-md-7">
            <div class="form-group position-relative">
              <i class="bi bi-geo-alt-fill icon-left"></i>
              <input type="text" class="form-control" name="enderecoUsuario" id="enderecoUsuario" placeholder="Endereço" readonly>
              <span class="hint-text">Endereço</span>
            </div>
          </div>

          <div class="col-md-2">
            <div class="form-group position-relative">
              <i class="bi bi-123 icon-left"></i>
              <input type="text" class="form-control" name="numeroEnderecoUsuario" placeholder="Número" required>
              <span class="hint-text">Número da Residência</span>
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group position-relative">
              <i class="bi bi-mailbox-flag icon-left"></i>
              <input type="text" class="form-control" name="complementoEnderecoUsuario" placeholder="Complemento">
              <span class="hint-text">Complemento</span>
            </div>
          </div>

          <div class="col-md-5">
            <div class="form-group position-relative">
              <i class="bi bi-geo-alt-fill icon-left"></i>
              <input type="text" class="form-control" name="bairroEnderecoUsuario" id="bairroEnderecoUsuario" placeholder="Bairro" readonly>
              <span class="hint-text">Bairro</span>
            </div>
          </div>

          <div class="col-md-5">
            <div class="form-group position-relative">
              <i class="bi bi-geo-alt-fill icon-left"></i>
              <input type="text" class="form-control" name="cidadeUsuario" id="cidadeUsuario" placeholder="Cidade" readonly>
              <span class="hint-text">Cidade</span>
            </div>
          </div>

          <div class="col-md-2">
            <div class="form-group position-relative">
              <i class="bi bi-geo-alt-fill icon-left"></i>
              <input type="text" class="form-control" name="estadoUsuario" id="estadoUsuario" placeholder="Estado" readonly>
              <span class="hint-text">Estado</span>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group position-relative">
              <i class="bi bi-envelope-fill icon-left"></i>
              <input type="email" class="form-control" name="emailUsuario" placeholder="E-mail" required>
              <span class="hint-text">E-mail</span>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <i class="bi bi-lock icon-left"></i>
              <input type="password" class="form-control" name="senhaUsuario" id="senhaUsuario" placeholder="Senha" required>
              <i class="bi bi-eye-fill icon-right" id="toggleSenha"></i>
            </div>
          </div>


          <div class="col-md-4 d-flex justify-content-center">
            <button class="btn btn-secondary btn-lg px-5" onclick="window.location.href='lista_usuarios.php'">Cancelar</button>
          </div>
          <div class="col-md-4 d-flex justify-content-center">
            <button type="submit" class="btn btn-success btn-lg px-5">Cadastrar</button>
          </div>
          <div class="col-md-4 d-flex justify-content-center">
            <button type="reset" class="btn btn-light btn-lg px-5">Limpar</button>
          </div>

        </div>
      </form>
    </div>

    <!-- JS personalizado -->
    <script>
      const PROXY_URL = '<?= PUBLIC_URL ?>/proxy';
    </script>
    <script src="<?= ASSETS_URL ?>/js/script_cadastrar-usuario.js?v=<?= time() ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
