<?php
require_once __DIR__ . '/../../../app/core/init.php';
require_once __DIR__ . '/../../../app/core/sessao_segura.php';

// Salvar evento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO asy_eventos 
            (nomeEvento, tipoEvento, dataHoraInicio, dataHoraFim, idMinisterio, observacao, criadoPor, idResponsavel)
            VALUES 
            (:nome, :tipo, :inicio, :fim, :ministerio, :obs, :criadoPor, :responsavel)
        ");

        $stmt->execute([
            ':nome' => $_POST['nomeEvento'],
            ':tipo' => $_POST['tipoEvento'],
            ':inicio' => $_POST['dataHoraInicio'],
            ':fim' => $_POST['dataHoraFim'],
            ':ministerio' => $_POST['idMinisterio'],
            ':obs' => $_POST['observacao'],
            ':criadoPor' => $usuario['idUsuario'],
            ':responsavel' => $_POST['responsavel']
        ]);

        $sucesso = true;
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Carregar ministérios e usuários
$ministerios = $pdo->query("SELECT idMinisterio, descricaoMinisterio FROM asy_ministerios ORDER BY descricaoMinisterio")->fetchAll();
$usuarios = $pdo->query("SELECT idUsuario, nomeCompletoUsuario FROM asy_usuarios ORDER BY nomeCompletoUsuario")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex, nofollow">

  <title>Cadastrar Eventos - ASY Gospel Church</title>

  <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php
require_once __DIR__ . '/../../../app/views/navbar.php';
require_once __DIR__ . '/../../../app/views/sidebar.php';
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h4 class="mt-4 mb-4" align="center"><i class="fas fa-calendar-plus mr-2"></i>Cadastro de Eventos</h4>

      <!-- Removemos os alerts HTML e deixamos o SweetAlert cuidar disso -->
      <form method="POST" class="card p-4 shadow-sm">
        <div class="form-group">
          <label>Nome do Evento</label>
          <input type="text" name="nomeEvento" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Tipo de Evento</label>
          <select name="tipoEvento" class="form-control" required>
            <option value="">Selecione...</option>
            <option value="Festa">Festa</option>
            <option value="Culto">Culto</option>
            <option value="Estudo">Estudo</option>
            <option value="Célula">Célula</option>
          </select>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Data e Hora de Início</label>
            <input type="datetime-local" name="dataHoraInicio" class="form-control" required>
          </div>
          <div class="form-group col-md-6">
            <label>Data e Hora de Término</label>
            <input type="datetime-local" name="dataHoraFim" class="form-control" required>
          </div>
        </div>

        <div class="form-group">
          <label>Ministério</label>
          <select name="idMinisterio" class="form-control" required>
            <option value="">Selecione...</option>
            <?php foreach ($ministerios as $m): ?>
              <option value="<?= $m['idMinisterio'] ?>"><?= htmlspecialchars($m['descricaoMinisterio']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Responsável pelo Evento</label>
          <select name="responsavel" class="form-control" required>
            <option value="">Selecione...</option>
            <?php foreach ($usuarios as $u): ?>
              <option value="<?= $u['idUsuario'] ?>"><?= htmlspecialchars($u['nomeCompletoUsuario']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Observação</label>
          <textarea name="observacao" class="form-control" rows="4"></textarea>
        </div>

        <div class="form-group">
          <label>Criado por</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['nomeCompletoUsuario'] ?? 'Desconhecido') ?>" disabled>
        </div>

        <button type="submit" class="btn btn-success mt-3">
          <i class="fas fa-save mr-1"></i>Salvar Evento
        </button>
      </form>
    </div>
  </section>
</div>

<script src="<?= ASSETS_URL ?>/plugins/jquery/jquery.min.js"></script>
<script src="<?= ASSETS_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (!empty($sucesso)): ?>
<script>
Swal.fire({
  title: 'Evento cadastrado!',
  text: 'O evento foi salvo com sucesso.',
  icon: 'success',
  confirmButtonText: 'OK',
  confirmButtonColor: '#28a745'
}).then(() => {
  // redirecionar se quiser, por exemplo:
  window.location.href = 'cadastrar_eventos.php';
});
</script>
<?php elseif (!empty($erro)): ?>
<script>
Swal.fire({
  title: 'Erro ao cadastrar!',
  text: '<?= addslashes($erro) ?>',
  icon: 'error',
  confirmButtonText: 'Fechar',
  confirmButtonColor: '#d33'
});
</script>
<?php endif; ?>

</body>
</html>
