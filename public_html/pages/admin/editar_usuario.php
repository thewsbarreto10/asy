<?php
  require_once __DIR__ . '/../../../app/core/init.php';
  require_once __DIR__ . '/../../../app/core/sessao_segura.php';

  // Somente administradores podem editar
  if (!$usuario || $usuario['idPermissaoUsuario'] != 1) {
    echo "<h1 style='text-align:center; margin-top:20%; color:red;'>Acesso negado</h1>";
    exit;
  }

  // Obtém o ID do usuário via GET
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if ($id <= 0) {
    echo "<h3 style='text-align:center; margin-top:20%; color:red;'>Usuário inválido.</h3>";
    exit;
  }

  // Busca dados do usuário
  $stmt = $pdo->prepare("
    SELECT *
    FROM asy_usuarios
    WHERE idUsuario = :id
    LIMIT 1
  ");
  $stmt->execute(['id' => $id]);
  $dadosUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$dadosUsuario) {
    echo "<h3 style='text-align:center; margin-top:20%; color:red;'>Usuário não encontrado.</h3>";
    exit;
  }

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

  // CEP → 00000-000
  if (!empty($dadosUsuario['cepUsuario'])) {
    $cep = preg_replace('/\D/', '', $dadosUsuario['cepUsuario']);
    if (strlen($cep) === 8) {
      $dadosUsuario['cepUsuario'] = substr($cep, 0, 5) . '-' . substr($cep, 5);
    }
  }

  // Buscar cursos em que o usuário está cadastrado
  $stmtCursos = $pdo->prepare("
      SELECT 
          c.idCurso, 
          c.nomeCurso, 
          c.statusCurso,
          m.descricaoMinisterio AS nomeMinisterio,
          uc.dataInicio
      FROM asy_cursos c
      JOIN asy_usuario_curso uc ON uc.idCurso = c.idCurso
      LEFT JOIN asy_ministerios m ON m.idMinisterio = c.idMinisterio
      WHERE uc.idUsuario = ?
      ORDER BY c.nomeCurso ASC
  ");
  $stmtCursos->execute([$id]);
  $cursosUsuario = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);



  // Busca permissões e ministérios
  $permissoes = $pdo->query("SELECT idPermissaoUsuario, descricaoPermissaoUsuario FROM asy_PermissaoUsuario")->fetchAll(PDO::FETCH_ASSOC);
  $ministerios = $pdo->query("SELECT idMinisterio, descricaoMinisterio FROM asy_ministerios ORDER BY descricaoMinisterio ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>Editar Usuário - ASY Gospel Church</title>

    <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="<?= ASSETS_URL ?>/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/estilo_editar-usuario.css">
    <style>

html, body {
  height: 100%;
  overflow: hidden;
}

.content-wrapper {
  height: 100vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.foto-usuario-header {
    width: 60px;
    height: 60px;
    border-radius: 50%; /* deixa circular */
    object-fit: cover;  /* evita deformar */
    border: 2px solid #ddd;
}

.nav-tabs .nav-link {
    color: #555;
    transition: 0.3s;
}

.nav-tabs .nav-link:hover {
    background-color: #f1f1f1;
}

.nav-tabs .nav-link.active {
    background-color: #007bff;
    color: #fff;
    border-color: #007bff #007bff #fff;
}

.form-group.position-relative .icon-left {
    position: absolute;
    top: 50%;
    left: 10px;
    transform: translateY(-50%);
    color: #6c757d;
}

.form-group.position-relative input {
  padding-left: 35px;
  padding-right: 40px; /* 🔥 espaço pro botão */
}

.icon-left {
    position: absolute;
    top: 50%;
    left: 12px;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
}

.input-com-icone {
    padding-left: 40px;
}

.icon-right {
  position: absolute;   /* 🔥 FALTAVA ISSO */
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  color: #25d366; /* verde WhatsApp */
  cursor: pointer;
}


    </style>
  </head>
  <body class="hold-transition sidebar-mini">
    <div class="wrapper">

      <?php
        require_once __DIR__ . '/../../../app/views/navbar.php';
        require_once __DIR__ . '/../../../app/views/sidebar.php';
      ?>

      <div class="content-wrapper">
        <?php
$foto = !empty($dadosUsuario['foto_perfil'])
    ? BASE_URL . '/proxy/usuario/foto.php?id=' . $dadosUsuario['idUsuario']
    : BASE_URL . '/assets/img/user-profile-img-default.png';
?>
<section class="content-header">
  <div class="container-fluid text-center">

<div class="d-flex justify-content-center align-items-center">

  <img src="<?= $foto ?>" 
       alt="Foto do usuário"
       class="foto-usuario-header mr-3">

  <div>
    <h1 class="mb-0">Editar Usuário</h1>
    <p class="mb-0">(<?= htmlspecialchars($dadosUsuario['nomeCompletoUsuario']) ?>)</p>
  </div>

</div>

  </div>
</section>

        <section class="content">
          <div class="container-fluid">
            <div class="card card-primary card-outline">
              <div class="card-body">
                <ul class="nav nav-tabs" id="tabUsuario" role="tablist">
                  <li class="nav-item"><a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab">Informações Pessoais</a></li>
                  <li class="nav-item"><a class="nav-link" id="acesso-tab" data-toggle="tab" href="#acesso" role="tab">Acesso</a></li>
                  <li class="nav-item"><a class="nav-link" id="permissao-tab" data-toggle="tab" href="#permissao" role="tab">Permissão</a></li>
                  <li class="nav-item"><a class="nav-link" id="ministerio-tab" data-toggle="tab" href="#ministerio" role="tab">Ministério</a></li>
                  <li class="nav-item"><a class="nav-link" id="cursos-tab" data-toggle="tab" href="#cursos" role="tab">Cursos</a></li>
<li class="ml-auto d-flex align-items-center">
  <span class="badge 
    <?= $dadosUsuario['statusUsuario'] === 'ativo' ? 'bg-success' : 'bg-danger' ?>">
    <?= htmlspecialchars($dadosUsuario['statusUsuario']) ?>
  </span>
</li>
                </ul>

                <div class="tab-content mt-3">

                  <!-- Aba 1 - Informações pessoais -->
                  <div class="tab-pane fade show active" id="info" role="tabpanel">
                    <form id="formDadosUsuario">
                      <input type="hidden" name="idUsuario" value="<?= $dadosUsuario['idUsuario'] ?>">

                      <div class="row g-3">

                        <div class="col-md-12">
                          <div class="form-group position-relative">
                            <i class="fas fa-user icon-left"></i>
                            <input type="text" class="form-control" name="nomeUsuario" value="<?= htmlspecialchars($dadosUsuario['nomeCompletoUsuario']) ?>" placeholder="Nome Completo" required>
                            <span class="hint-text">Nome Completo</span>
                          </div>
                        </div>

                        <div class="col-md-4">
  <div class="form-group position-relative">
    <i class="fas fa-address-card icon-left"></i>

    <input 
      type="text" 
      class="form-control cpf-input" 
      name="cpfUsuario" 
      placeholder="CPF"
      maxlength="14"
      required
    >

    <i class="bi status-icon"></i>

    <span class="hint-text">CPF</span>
    <small class="cpf-feedback text-danger"></small>
  </div>
</div>    

                        <div class="col-md-4">
                          <div class="form-group position-relative">
                            <i class="fas fa-calendar-alt icon-left"></i>
                            <input type="text" class="form-control" name="dataNascimentoUsuario" value="<?= htmlspecialchars($dadosUsuario['dataNascimentoUsuario']) ?>" placeholder="Data de nascimento" inputmode="numeric" maxlength="10" required>
                            <span class="hint-text">Data de Nascimento</span>
                          </div>
                        </div>

<div class="col-md-4">
  <div class="form-group position-relative">
    <i class="fab fa-whatsapp icon-left"></i>

    <input type="tel"
           class="form-control pr-5"
           name="telefoneUsuario"
           id="telefoneUsuario"
           value="<?= htmlspecialchars($dadosUsuario['telefoneUsuario'] ?? '') ?>"
           placeholder="Celular | WhatsApp"
           required>

    <span class="hint-text">Celular | WhatsApp</span>

    <button type="button" 
            id="btnWhatsapp"
            class="btn btn-success btn-sm position-absolute"
            style="top: 50%; right: 5px; transform: translateY(-50%); padding: 2px 8px;">
      Abrir no WhatsApp
    </button>
  </div>
</div>

                        <div class="col-md-3">
                          <div class="form-group position-relative">
                            <i class="fas fa-sort-numeric-up icon-left"></i>
                            
                            <input type="text" class="form-control" name="cepUsuario" id="cepUsuario" value="<?= htmlspecialchars($dadosUsuario['cepUsuario'] ?? '') ?>" placeholder="CEP" required>
                            <span class="hint-text">CEP</span>
                          </div>
                        </div>

                        <div class="col-md-7">
                          <div class="form-group position-relative">
                            <i class="fas fa-map-marker-alt icon-left"></i>
                            
                            <input type="text" class="form-control" name="enderecoUsuario" id="enderecoUsuario" value="<?= htmlspecialchars($dadosUsuario['enderecoUsuario'] ?? '') ?>" placeholder="Endereço" readonly>
                            <span class="hint-text">Endereço</span>
                          </div>
                        </div>

                        <div class="col-md-2">
                          <div class="form-group position-relative">
                            <i class="fas fa-map-marker-alt icon-left"></i>
                            
                            <input type="text" class="form-control" name="numeroEnderecoUsuario" value="<?= htmlspecialchars($dadosUsuario['numeroEnderecoUsuario'] ?? '') ?>" placeholder="Número" required>
                            <span class="hint-text">Número da Residência</span>
                          </div>
                        </div>

                        <div class="col-md-12">
                          <div class="form-group position-relative">
                            <i class="fas fa-map-marker-alt icon-left"></i>
                            
                            <input type="text" class="form-control" name="complementoEnderecoUsuario" value="<?= htmlspecialchars($dadosUsuario['complementoEnderecoUsuario'] ?? '') ?>" placeholder="Complemento">
                            <span class="hint-text">Complemento</span>
                          </div>
                        </div>

                        <div class="col-md-5">
                          <div class="form-group position-relative">
                            <i class="fas fa-map-marker-alt icon-left"></i>
                            
                            <input type="text" class="form-control" name="bairroEnderecoUsuario" id="bairroEnderecoUsuario" value="<?= htmlspecialchars($dadosUsuario['bairroEnderecoUsuario'] ?? '') ?>" placeholder="Bairro" readonly>
                            <span class="hint-text">Bairro</span>
                          </div>
                        </div>

                        <div class="col-md-5">
                          <div class="form-group position-relative">
                            <i class="fas fa-map-marker-alt icon-left"></i>
                            
                            <input type="text" class="form-control" name="cidadeUsuario" id="cidadeUsuario" value="<?= htmlspecialchars($dadosUsuario['cidadeUsuario'] ?? '') ?>" placeholder="Cidade" readonly>
                            <span class="hint-text">Cidade</span>
                          </div>
                        </div>

                        <div class="col-md-2">
                          <div class="form-group position-relative">
                            <i class="fas fa-map-marker-alt icon-left"></i>
                            
                            <input type="text" class="form-control" name="estadoEnderecoUsuario" id="estadoEnderecoUsuario" value="<?= htmlspecialchars($dadosUsuario['estadoEnderecoUsuario'] ?? '') ?>" placeholder="Estado" readonly>
                            <span class="hint-text">Estado</span>
                          </div>
                        </div>

<div class="col-12 text-center mt-3">
    <button type="submit" class="btn btn-primary px-5">
        Atualizar Informações
    </button>
</div>
                      </div>
                    </form>
                  </div>

                  <!-- Aba 2 - Acesso -->
                  <div class="tab-pane fade" id="acesso" role="tabpanel">
                    <form id="formAcessoUsuario">
                      <input type="hidden" name="idUsuario" value="<?= $dadosUsuario['idUsuario'] ?>">

<div class="form-group">
  <label>E-mail</label>

  <div class="row align-items-center">
    
    <div class="col-md-6">
<div class="form-group position-relative mb-0">
  <i class="fas fa-envelope icon-left"></i>

  <input type="email" 
         name="emailUsuario" 
         class="form-control input-com-icone" 
         value="<?= htmlspecialchars($dadosUsuario['emailUsuario']) ?>">
</div>
    </div>

    <div class="col-md-2">
      <button type="button" 
              class="btn btn-warning btn-block" 
              id="btnResetarSenha">
        Resetar senha
      </button>
    </div>

    <div class="col-md-4 text-right">
      <button type="submit" class="btn btn-primary px-5">
        Atualizar acesso
      </button>
    </div>

    <div class="col-12 text-center mt-2">
      <small class="form-text text-muted">
        O usuário receberá uma senha temporária e será obrigado a trocá-la no próximo login.
      </small>
    </div>

  </div>
</div>
</form>
                  </div>

                  <!-- Aba 3 - Permissão -->
                  <div class="tab-pane fade" id="permissao" role="tabpanel">
                    <form id="formPermissaoUsuario">
                      <input type="hidden" name="idUsuario" value="<?= $dadosUsuario['idUsuario'] ?>">

<div class="form-group">
  <label>Tipo de usuário</label>

  <div class="row align-items-end">

    <!-- Select com ícone -->
    <div class="col-md-6">
      <div class="position-relative">
        <i class="fas fa-user-shield icon-left"></i>

        <select name="idPermissaoUsuario" 
                class="form-control input-com-icone">
          <?php foreach ($permissoes as $p): ?>
            <option value="<?= $p['idPermissaoUsuario'] ?>" <?= $p['idPermissaoUsuario'] == $dadosUsuario['idPermissaoUsuario'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['descricaoPermissaoUsuario']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Botão na direita -->
    <div class="col-md-6 text-right">
      <button type="submit" class="btn btn-primary px-4">
        Atualizar Permissão
      </button>
    </div>

  </div>
</div>
                    </form>
                  </div>

                  <!-- Aba 4 - Ministério -->
                  <div class="tab-pane fade" id="ministerio" role="tabpanel">
                    <form id="formMinisterioUsuario">
                      <input type="hidden" name="idUsuario" value="<?= $dadosUsuario['idUsuario'] ?>">

<div class="form-group">
  <label>Ministério</label>

  <div class="row align-items-end">

    <!-- Select com ícone -->
    <div class="col-md-6">
      <div class="position-relative">
        <i class="fas fa-church icon-left"></i>

        <select name="idMinisterioUsuario" 
                class="form-control input-com-icone">
          <?php foreach ($ministerios as $m): ?>
            <option value="<?= $m['idMinisterio'] ?>" <?= $m['idMinisterio'] == $dadosUsuario['idMinisterioUsuario'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($m['descricaoMinisterio']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Botão na direita -->
    <div class="col-md-6 text-right">
      <button type="submit" class="btn btn-primary px-4">
        Atualizar Ministério
      </button>
    </div>

  </div>
</div>
                    </form>
                  </div>

                  <!-- Aba 5 - Cursos -->
                  <div class="tab-pane fade" id="cursos" role="tabpanel">
                    <div id="cursos-container">
                      <?php if ($cursosUsuario): ?>
                        <table class="table table-striped">
                          <thead>
                            <tr>
                              <th>Curso</th>
                              <th>Ministério</th>
                              <th>Data de Inscrição</th>
                              <th>Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($cursosUsuario as $c): ?>
                              <tr>
                                <td><?= htmlspecialchars($c['nomeCurso']) ?></td>
                                <td><?= htmlspecialchars($c['nomeMinisterio'] ?? '-') ?></td>
                                <td><?= date('d/m/Y', strtotime($c['dataInicio'])) ?></td>
                                <td>
                                  <span class="badge badge-<?= $c['statusCurso'] === 'ativo' ? 'success' : ($c['statusCurso'] === 'inativo' ? 'secondary' : 'warning') ?>">
                                    <?= ucfirst($c['statusCurso']) ?>
                                  </span>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      <?php else: ?>
                        <p>Este usuário não está cadastrado em nenhum curso.</p>
                      <?php endif; ?>
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', () => {
  const btnWhatsapp = document.getElementById('btnWhatsapp');
  const inputTelefone = document.getElementById('telefoneUsuario');

  if (btnWhatsapp && inputTelefone) {
    btnWhatsapp.addEventListener('click', (e) => {
      e.preventDefault();

      let numero = inputTelefone.value.replace(/\D/g, '');
      if (!numero || numero.length < 10) {
        Swal.fire('Erro', 'Informe um número válido.', 'error');
        return;
      }

      if (!numero.startsWith('55')) numero = '55' + numero;

      const url = `https://wa.me/${numero}`;

      // Forma 100% confiável de abrir
      const a = document.createElement('a');
      a.href = url;
      a.target = '_blank';
      a.rel = 'noopener noreferrer';
      a.click();
    });
  }
});
    </script>

    <script src="<?= ASSETS_URL ?>/plugins/jquery/jquery.min.js"></script>
    <script src="<?= ASSETS_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= ASSETS_URL ?>/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= ASSETS_URL ?>/js/editar_usuario.js?v=<?= time() ?>"></script>
    <script src="<?= ASSETS_URL ?>/js/logout.js?v=<?= time() ?>"></script>
  </body>
</html>
