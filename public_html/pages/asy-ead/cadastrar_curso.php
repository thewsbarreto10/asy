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

    <title>Cadastrar Curso - ASY Gospel Church</title>

    <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/estilo_cadastrar-curso.css?v=<?= time() ?>">

  </head>

  <body>
    <div class="login-container">
      <div class="login-image">
        <img src="<?= ASSETS_URL ?>/img/asy_logo.png" alt="Logo">
      </div>

      <h4 class="text-center mb-4">Cadastrar curso</h4>

      <form id="cadastroForm">
        <div class="row g-3"> <!-- g-3 adiciona espaçamento entre colunas -->

          <div class="col-md-9">
            <div class="form-group position-relative">
              <i class="bi bi-person-fill icon-left"></i>
              <input type="text" class="form-control" name="nomeCurso" placeholder="Nome do Curso" required>
              <span class="hint-text">Nome do Curso</span>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group position-relative">
              <i class="bi bi-diagram-3-fill icon-left"></i>
              <select class="form-control" name="idMinisterio" required>
                <option value="" readonly selected>Ministério</option>
                <?php foreach ($ministerios as $m): ?>
                  <option value="<?= $m['idMinisterio'] ?>"><?= htmlspecialchars($m['descricaoMinisterio']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group position-relative">
              <i class="bi bi-card-text icon-left"></i>
              <textarea class="form-control" name="descricaoCurso" placeholder="Descrição do Curso" required></textarea>
              <span class="hint-text">Descrição</span>
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group position-relative image-input-wrapper d-flex align-items-center justify-content-between">
              
              <div class="d-flex align-items-center gap-3">
                <i class="bi bi-image-fill icon-left"></i>

                <label class="custom-file-upload mb-0">
                  <input type="file" id="cardImagem" name="cardImagem" accept="image/*">
                  Escolher imagem
                </label>

                <span class="file-name">Nenhuma imagem selecionada</span>
              </div>

              <!-- Miniatura à direita -->
              <div class="image-preview" id="previewContainer" style="display:none;">
                  <button type="button" class="remove-image-btn" id="removeImageBtn">×</button>
                  <img id="previewImage" src="#" alt="Preview">
              </div>

              <span class="hint-text">Imagem do Curso</span>
            </div>
          </div>

          <div class="col-md-4 d-flex justify-content-center">
            <button class="btn btn-secondary btn-lg px-5" onclick="window.location.href='lista_cursos.php'">Cancelar</button>
          </div>
          <div class="col-md-4 d-flex justify-content-center">
            <button type="submit" class="btn btn-success btn-lg px-5">Criar Curso</button>
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
    <script src="<?= ASSETS_URL ?>/js/script_cadastrar-curso.js?v=<?= time() ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  </body>
</html>
