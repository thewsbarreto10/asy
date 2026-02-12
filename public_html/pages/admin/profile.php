<?php
require_once __DIR__ . '/../../../app/core/init.php';        // inicializa config e session
require_once __DIR__ . '/../../../app/core/sessao_segura.php'; // valida sessão

// Se não estiver logado, impede acesso
if (!$usuario || !isset($usuario['idUsuario'])) {
    echo "<h3 style='text-align:center; margin-top:20%; color:red;'>Usuário inválido.</h3>";
    exit;
}

// Carrega todos os dados do usuário do banco
$stmt = $pdo->prepare("
    SELECT *
    FROM asy_usuarios
    WHERE idUsuario = :id
    LIMIT 1
");
$stmt->execute(['id' => $usuario['idUsuario']]);
$dadosUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

  // ==========================
  // 🔹 FORMATAÇÃO PARA EXIBIÇÃO
  // ==========================

  // Data de nascimento → dd/mm/yyyy
  if (!empty($dadosUsuario['dataNascimentoUsuario']) && $dadosUsuario['dataNascimentoUsuario'] != '0000-00-00') {
    $dadosUsuario['dataNascimentoUsuario'] = date('d/m/Y', strtotime($dadosUsuario['dataNascimentoUsuario']));
  }

  // Telefone → (11) 9 1234-5678
  if (!empty($dadosUsuario['telefoneUsuario'])) {
    $telefone = preg_replace('/\D/', '', $dadosUsuario['telefoneUsuario']);
    if (strlen($telefone) === 11) {
      $dadosUsuario['telefoneUsuario'] = sprintf("(%s) %s %s-%s",
        substr($telefone, 0, 2),
        substr($telefone, 2, 1),
        substr($telefone, 3, 4),
        substr($telefone, 7)
      );
    } elseif (strlen($telefone) === 10) {
      $dadosUsuario['telefoneUsuario'] = sprintf("(%s) %s-%s",
        substr($telefone, 0, 2),
        substr($telefone, 2, 4),
        substr($telefone, 6)
      );
    }
  }

// Se não achou usuário
if (!$dadosUsuario) {
    echo "<h3 style='text-align:center; margin-top:20%; color:red;'>Usuário não encontrado.</h3>";
    exit;
}

// Carrega ministérios
$ministerioUsuario = '';
if (!empty($dadosUsuario['idMinisterioUsuario'])) {
    $stmt = $pdo->prepare("SELECT descricaoMinisterio FROM asy_ministerios WHERE idMinisterio = :id LIMIT 1");
    $stmt->execute(['id' => $dadosUsuario['idMinisterioUsuario']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res) {
        $ministerioUsuario = $res['descricaoMinisterio'];
    }
}

?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Perfil do Usuário - ASY Gospel Church</title>
  <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/dist/css/adminlte.min.css">
  <style>

  /* HINT (flutua abaixo sem afetar layout) */
  .hint-text {
    position: absolute;
    left: 0.8rem;
    bottom: -1.1rem;
    font-size: 0.8rem;
    color: rgba(230, 237, 243, 0.6);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease, transform 0.2s ease;
    transform: translateY(-2px);
  }
  .form-group.active .hint-text {
    opacity: 1;
    transform: translateY(0);
  }

  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php
require_once __DIR__ . '/../../../app/views/navbar.php';
require_once __DIR__ . '/../../../app/views/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <!-- Conteúdo específico -->
       <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3">

            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="d-flex justify-content-center align-items-center" style="min-height:150px;">
                  <div class="position-relative d-inline-block" style="width:130px; height:130px;">
                    <?php
                      $fotoPerfil = !empty($usuario['foto_perfil'])
                        ? BASE_URL . '/proxy/usuario/foto.php?id=' . $usuario['idUsuario']
                        : BASE_URL . '/assets/img/user-profile-img-default.png';
                    ?>

                    <img id="fotoPerfil"
                      class="profile-user-img img-fluid img-circle"
                      src="<?= htmlspecialchars(cacheBusterUrl($fotoPerfil)) ?>"
                      alt="Foto do perfil"
                      style="width:128px; height:128px; object-fit:cover; border:3px solid #ddd; display:block; margin:auto;"
                    >

                    <div id="editarFotoOverlay"
                        style="position:absolute; top:0; left:0; width:100%; height:100%; border-radius:50%;
                                background:rgba(0,0,0,0.5); color:white; display:flex; align-items:center;
                                justify-content:center; opacity:0; transition:opacity 0.3s; cursor:pointer;">
                      <i class="fas fa-pencil-alt"></i>
                    </div>

                    <form id="formFotoPerfil" enctype="multipart/form-data" style="display:none;">
                      <input type="file" name="foto" id="inputFoto" accept="image/*">
                    </form>
                  </div>
                </div>

                <h3 class="profile-username text-center"><?= $usuario['nomeCompletoUsuario'] ?? 'Visitante' ?></h3>

                <p class="text-muted text-center"><?= htmlspecialchars($ministerioUsuario) ?></p>

                <a href="#" class="btn btn-primary btn-block"><b>Follow</b></a>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

            <!-- About Me Box -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">About Me</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <strong><i class="fas fa-book mr-1"></i> Education</strong>

                <p class="text-muted">
                  B.S. in Computer Science from the University of Tennessee at Knoxville
                </p>

                <hr>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>

                <p class="text-muted">Malibu, California</p>

                <hr>

                <strong><i class="fas fa-pencil-alt mr-1"></i> Skills</strong>

                <p class="text-muted">
                  <span class="tag tag-danger">UI Design</span>
                  <span class="tag tag-success">Coding</span>
                  <span class="tag tag-info">Javascript</span>
                  <span class="tag tag-warning">PHP</span>
                  <span class="tag tag-primary">Node.js</span>
                </p>

                <hr>

                <strong><i class="far fa-file-alt mr-1"></i> Notes</strong>

                <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam fermentum enim neque.</p>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
          <div class="col-md-9">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link" href="#informacoesPessoais" data-toggle="tab">Informações Pessoais</a></li>
                  <li class="nav-item"><a class="nav-link" href="#acesso" data-toggle="tab">Acesso</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="informacoesPessoais">
                    <form id="formDadosUsuario">
                      <input type="hidden" name="idUsuario" value="<?= $dadosUsuario['idUsuario'] ?>">

                      <div class="row g-3">

                        <div class="col-md-12">
                          <div class="form-group position-relative">
                            <i class="bi bi-person-fill icon-left"></i>
                            <input type="text" class="form-control" name="nomeUsuario" value="<?= htmlspecialchars($dadosUsuario['nomeCompletoUsuario']) ?>" placeholder="Nome Completo" required>
                            <span class="hint-text">Nome Completo</span>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group position-relative">
                            <i class="bi bi-calendar-date-fill icon-left"></i>
                            <input type="text" class="form-control" name="dataNascimentoUsuario" value="<?= htmlspecialchars($dadosUsuario['dataNascimentoUsuario']) ?>" placeholder="Data de nascimento" inputmode="numeric" maxlength="10" required>
                            <span class="hint-text">Data de Nascimento</span>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group position-relative">
                            <i class="bi bi-whatsapp icon-left"></i>
                            <input type="tel" class="form-control" name="telefoneUsuario" value="<?= htmlspecialchars($dadosUsuario['telefoneUsuario'] ?? '') ?>" placeholder="Celular | WhatsApp" required>
                            <span class="hint-text">Celular | WhatsApp</span>
                          </div>
                        </div>

                        <div class="col-md-12">
                          <div class="form-group position-relative">
                            <i class="bi bi-diagram-3-fill icon-left"></i>
                            <input type="text" class="form-control" name="ministerioUsuario" value="<?= htmlspecialchars($ministerioUsuario) ?>" readonly>
                          </div>
                        </div>

                        <div class="col-md-3">
                          <div class="form-group position-relative">
                            <i class="bi bi-123 icon-left"></i>
                            <input type="text" class="form-control" name="cepUsuario" id="cepUsuario" value="<?= htmlspecialchars($dadosUsuario['cepUsuario'] ?? '') ?>" placeholder="CEP" required>
                            <span class="hint-text">CEP</span>
                          </div>
                        </div>

                        <div class="col-md-7">
                          <div class="form-group position-relative">
                            <i class="bi bi-geo-alt-fill icon-left"></i>
                            <input type="text" class="form-control" name="enderecoUsuario" id="enderecoUsuario" value="<?= htmlspecialchars($dadosUsuario['enderecoUsuario'] ?? '') ?>" placeholder="Endereço" readonly>
                            <span class="hint-text">Endereço</span>
                          </div>
                        </div>

                        <div class="col-md-2">
                          <div class="form-group position-relative">
                            <i class="bi bi-123 icon-left"></i>
                            <input type="text" class="form-control" name="numeroEnderecoUsuario" value="<?= htmlspecialchars($dadosUsuario['numeroEnderecoUsuario'] ?? '') ?>" placeholder="Número" required>
                            <span class="hint-text">Número da Residência</span>
                          </div>
                        </div>

                        <div class="col-md-12">
                          <div class="form-group position-relative">
                            <i class="bi bi-mailbox-flag icon-left"></i>
                            <input type="text" class="form-control" name="complementoEnderecoUsuario" value="<?= htmlspecialchars($dadosUsuario['complementoEnderecoUsuario'] ?? '') ?>" placeholder="Complemento">
                            <span class="hint-text">Complemento</span>
                          </div>
                        </div>

                        <div class="col-md-5">
                          <div class="form-group position-relative">
                            <i class="bi bi-geo-alt-fill icon-left"></i>
                            <input type="text" class="form-control" name="bairroEnderecoUsuario" id="bairroEnderecoUsuario" value="<?= htmlspecialchars($dadosUsuario['bairroEnderecoUsuario'] ?? '') ?>" placeholder="Bairro" readonly>
                            <span class="hint-text">Bairro</span>
                          </div>
                        </div>

                        <div class="col-md-5">
                          <div class="form-group position-relative">
                            <i class="bi bi-geo-alt-fill icon-left"></i>
                            <input type="text" class="form-control" name="cidadeUsuario" id="cidadeUsuario" value="<?= htmlspecialchars($dadosUsuario['cidadeUsuario'] ?? '') ?>" placeholder="Cidade" readonly>
                            <span class="hint-text">Cidade</span>
                          </div>
                        </div>

                        <div class="col-md-2">
                          <div class="form-group position-relative">
                            <i class="bi bi-geo-alt-fill icon-left"></i>
                            <input type="text" class="form-control" name="estadoEnderecoUsuario" id="estadoEnderecoUsuario" value="<?= htmlspecialchars($dadosUsuario['estadoEnderecoUsuario'] ?? '') ?>" placeholder="Estado" readonly>
                            <span class="hint-text">Estado</span>
                          </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Atualizar Informações</button>

                      </div>
                    </form>
                  </div>

                  <div class="tab-pane" id="acesso" role="tabpanel">
                    <form id="formAcessoUsuario">
                      <input type="hidden" name="idUsuario" value="<?= $dadosUsuario['idUsuario'] ?>">

                      <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="emailUsuario" class="form-control" value="<?= htmlspecialchars($dadosUsuario['emailUsuario']) ?>">
                      </div>

                      <div class="form-group">
                        <label>Senha</label><br>
                        <input type="password" name="senhaUsuario" class="form-control">
                      </div>

                      <button type="submit" class="btn btn-primary">Atualizar Acesso</button>
                    </form>
                  </div>
                  <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    </div>
  </section>
</div>

<script src="<?= ASSETS_URL ?>/plugins/jquery/jquery.min.js"></script>
<script src="<?= ASSETS_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>/js/logout.js?v=<?= time() ?>"></script>
<script src="<?= ASSETS_URL ?>/js/script_profile.js?v=<?= time() ?>"></script>

<script>
const foto = document.getElementById('fotoPerfil');
const overlay = document.getElementById('editarFotoOverlay');
const inputFoto = document.getElementById('inputFoto');
const form = document.getElementById('formFotoPerfil');

overlay.addEventListener('click', () => inputFoto.click());
foto.parentElement.addEventListener('mouseenter', () => overlay.style.opacity = '1');
foto.parentElement.addEventListener('mouseleave', () => overlay.style.opacity = '0');

inputFoto.addEventListener('change', () => {
  const formData = new FormData(form);
  fetch('<?= BASE_URL ?>/proxy/usuario/upload_foto.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      // ⚠️ É AQUI que você coloca a linha correta
      foto.src = '<?= BASE_URL ?>/' + data.path + '?v=' + Date.now();

      Swal.fire({
        icon: 'success',
        title: 'Foto atualizada com sucesso!',
        timer: 1500,
        showConfirmButton: false
      });
    } else {
      Swal.fire('Erro', data.message, 'error');
    }
  })
  .catch(() => {
    Swal.fire('Erro', 'Falha ao enviar a imagem', 'error');
  });
});

</script>

</body>
</html>
