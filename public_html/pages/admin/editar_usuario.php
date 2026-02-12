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

    </style>
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
            <h1 class="text-center">Editar Usuário</h1>
            <p class="text-center">(<?= htmlspecialchars($dadosUsuario['nomeCompletoUsuario']) ?>)</p>
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
                </ul>

                <div class="tab-content mt-3">

                  <!-- Aba 1 - Informações pessoais -->
                  <div class="tab-pane fade show active" id="info" role="tabpanel">
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
                            <select class="form-control" name="idMinisterioUsuario" required>
                              <option value="" readonly>Selecione o ministério</option>
                              <?php foreach ($ministerios as $m): ?>
                                <option value="<?= $m['idMinisterio'] ?>" <?= $m['idMinisterio'] == ($dadosUsuario['idMinisterioUsuario'] ?? '') ? 'selected' : '' ?>>
                                  <?= htmlspecialchars($m['descricaoMinisterio']) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
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

                  <!-- Aba 2 - Acesso -->
                  <div class="tab-pane fade" id="acesso" role="tabpanel">
                    <form id="formAcessoUsuario">
                      <input type="hidden" name="idUsuario" value="<?= $dadosUsuario['idUsuario'] ?>">

                      <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="emailUsuario" class="form-control" value="<?= htmlspecialchars($dadosUsuario['emailUsuario']) ?>">
                      </div>

                      <div class="form-group">
                        <label>Senha</label><br>
                        <button type="button" class="btn btn-warning" id="btnResetarSenha">Resetar senha</button>
                        <small class="form-text text-muted">O usuário receberá uma senha temporária e será obrigado a trocá-la no próximo login.</small>
                      </div>

                      <button type="submit" class="btn btn-primary">Atualizar Acesso</button>
                    </form>
                  </div>

                  <!-- Aba 3 - Permissão -->
                  <div class="tab-pane fade" id="permissao" role="tabpanel">
                    <form id="formPermissaoUsuario">
                      <input type="hidden" name="idUsuario" value="<?= $dadosUsuario['idUsuario'] ?>">

                      <div class="form-group">
                        <label>Tipo de usuário</label>
                        <select name="idPermissaoUsuario" class="form-control">
                          <?php foreach ($permissoes as $p): ?>
                            <option value="<?= $p['idPermissaoUsuario'] ?>" <?= $p['idPermissaoUsuario'] == $dadosUsuario['idPermissaoUsuario'] ? 'selected' : '' ?>>
                              <?= htmlspecialchars($p['descricaoPermissaoUsuario']) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <button type="submit" class="btn btn-primary">Atualizar Permissão</button>
                    </form>
                  </div>

                  <!-- Aba 4 - Ministério -->
                  <div class="tab-pane fade" id="ministerio" role="tabpanel">
                    <form id="formMinisterioUsuario">
                      <input type="hidden" name="idUsuario" value="<?= $dadosUsuario['idUsuario'] ?>">

                      <div class="form-group">
                        <label>Ministério</label>
                        <select name="idMinisterioUsuario" class="form-control">
                          <?php foreach ($ministerios as $m): ?>
                            <option value="<?= $m['idMinisterio'] ?>" <?= $m['idMinisterio'] == $dadosUsuario['idMinisterioUsuario'] ? 'selected' : '' ?>>
                              <?= htmlspecialchars($m['descricaoMinisterio']) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <button type="submit" class="btn btn-primary">Atualizar Ministério</button>
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

    <script src="<?= ASSETS_URL ?>/plugins/jquery/jquery.min.js"></script>
    <script src="<?= ASSETS_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= ASSETS_URL ?>/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= ASSETS_URL ?>/js/editar_usuario.js?v=<?= time() ?>"></script>
    <script src="<?= ASSETS_URL ?>/js/logout.js?v=<?= time() ?>"></script>
  </body>
</html>
