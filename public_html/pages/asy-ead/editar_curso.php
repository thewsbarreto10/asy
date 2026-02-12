<?php
// editar_curso.php
require_once __DIR__ . '/../../../app/core/init.php';
require_once __DIR__ . '/../../../app/core/sessao_segura.php';

// Segurança: somente admins (idPermissaoUsuario == 1)
if (!$usuario || ($usuario['idPermissaoUsuario'] ?? null) != 1) {
    http_response_code(403);
    echo "Acesso negado.";
    exit;
}

/* ------------------ Rotas AJAX ------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    header('Content-Type: application/json; charset=utf-8');

    $json = function($arr){
        echo json_encode($arr, JSON_UNESCAPED_UNICODE);
        exit;
    };

    $action = $_POST['action'];
    $idCursoPost = isset($_POST['idCurso']) ? (int)$_POST['idCurso'] : 0;

    if ($idCursoPost <= 0) $json(['ok'=>false,'msg'=>'Curso inválido.']);

/* ---------- Save Course ---------- */
if ($action === 'save_course') {

    $nome = trim($_POST['nomeCurso'] ?? '');
    $descricao = trim($_POST['descricaoCurso'] ?? '');
    $status = in_array($_POST['statusCurso'] ?? '', ['ativo','finalizado','inativo']) ? $_POST['statusCurso'] : 'ativo';
    $idMinisterio = !empty($_POST['idMinisterio']) ? (int)$_POST['idMinisterio'] : null;

    if ($nome === '') $json(['ok'=>false,'msg'=>'Nome do curso é obrigatório.']);

    $imagemNome = null;
    $imagemPublic = null;

if (!empty($_FILES['imagemCurso']) && $_FILES['imagemCurso']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['imagemCurso'];
    if ($f['error'] !== UPLOAD_ERR_OK) $json(['ok'=>false,'msg'=>'Erro no upload da imagem.']);

    $allowed = ['image/jpeg','image/png','image/webp'];
    if (!in_array($f['type'], $allowed)) $json(['ok'=>false,'msg'=>'Formato inválido. Use JPG, PNG ou WEBP.']);

    // Diretório correto do curso
    $dirCurso = '/var/www/html/asy/public_html/assets/img/cursos/' . $idCursoPost . '/CARD/';
    if (!is_dir($dirCurso)) {
        $json(['ok'=>false,'msg'=>"Diretório de upload não existe. Crie: $dirCurso"]);
    }

// Nome do arquivo = nome do curso + extensão
$ext = pathinfo($f['name'], PATHINFO_EXTENSION);
$nomeArquivo = $nome . '.' . $ext;

// Caminho completo no servidor
$dest = $dirCurso . $nomeArquivo;

// Mover arquivo (substitui se já existir)
if (!move_uploaded_file($f['tmp_name'], $dest)) {
    $json(['ok'=>false,'msg'=>'Falha ao mover arquivo.']);
}

// URL pública completa
$baseURL = 'http://srv880732.hstgr.cloud/asy'; // seu domínio + pasta base
$imagemPublic = $baseURL . '/assets/img/cursos/' . $idCursoPost . '/CARD/' . $nomeArquivo;

// Salvar no banco a URL completa
$imagemNome = $imagemPublic;

}


    // Atualiza no banco
    $stmt = $pdo->prepare("
        UPDATE asy_cursos
        SET nomeCurso = ?, descricaoCurso = ?, statusCurso = ?, idMinisterio = ?
        " . ($imagemNome ? ", imagemCurso = ? " : "") . "
        WHERE idCurso = ?
    ");
    $params = [$nome, $descricao, $status, $idMinisterio];
    if ($imagemNome) $params[] = $imagemNome;
    $params[] = $idCursoPost;
    $stmt->execute($params);

    $json(['ok'=>true,'msg'=>'Curso salvo com sucesso.','imagem'=>$imagemPublic ?? null]);
}


    /* ---------- Assign / Remove User ---------- */
    if ($action === 'assign_user' || $action === 'remove_user') {
        $idUsuario = isset($_POST['idUsuario']) ? (int)$_POST['idUsuario'] : 0;
        if (!$idUsuario) $json(['ok'=>false,'msg'=>'Parâmetros inválidos.']);

        if ($action === 'assign_user') {
            $stmtChk = $pdo->prepare("SELECT COUNT(*) FROM asy_usuario_curso WHERE idUsuario = ? AND idCurso = ?");
            $stmtChk->execute([$idUsuario, $idCursoPost]);
            if ($stmtChk->fetchColumn() == 0) {
                $stmtIns = $pdo->prepare("INSERT INTO asy_usuario_curso (idUsuario,idCurso,dataInicio) VALUES (?, ?, NOW())");
                $stmtIns->execute([$idUsuario, $idCursoPost]);
            }
            $json(['ok'=>true,'msg'=>'Usuário liberado para o curso.']);
        } else {
            $stmtDel = $pdo->prepare("DELETE FROM asy_usuario_curso WHERE idUsuario = ? AND idCurso = ?");
            $stmtDel->execute([$idUsuario, $idCursoPost]);
            $json(['ok'=>true,'msg'=>'Acesso removido.']);
        }
    }

    /* ---------- Assign / Remove Ministry ---------- */
    if ($action === 'assign_ministry') {
        $idMinisterio = isset($_POST['idMinisterio']) ? (int)$_POST['idMinisterio'] : 0;
        $assign = isset($_POST['assign']) ? (int)$_POST['assign'] : 1;
        if (!$idMinisterio) $json(['ok'=>false,'msg'=>'Parâmetros inválidos.']);

        if ($assign === 1) {
            $stmtUsers = $pdo->prepare("SELECT idUsuario FROM asy_usuarios WHERE idMinisterioUsuario = ?");
            $stmtUsers->execute([$idMinisterio]);
            $users = $stmtUsers->fetchAll(PDO::FETCH_COLUMN,0);

            $ins = $pdo->prepare("INSERT INTO asy_usuario_curso (idUsuario,idCurso,dataInicio) VALUES (?, ?, NOW())");
            $chk = $pdo->prepare("SELECT COUNT(*) FROM asy_usuario_curso WHERE idUsuario = ? AND idCurso = ?");
            foreach ($users as $u) {
                $chk->execute([$u, $idCursoPost]);
                if ($chk->fetchColumn() == 0) $ins->execute([$u, $idCursoPost]);
            }
            $json(['ok'=>true,'msg'=>'Todos usuários do ministério atribuídos ao curso.']);
        } else {
            $stmtDel = $pdo->prepare("
                DELETE uc FROM asy_usuario_curso uc
                JOIN asy_usuarios u ON u.idUsuario = uc.idUsuario
                WHERE u.idMinisterioUsuario = ? AND uc.idCurso = ?
            ");
            $stmtDel->execute([$idMinisterio, $idCursoPost]);
            $json(['ok'=>true,'msg'=>'Removidos acessos dos usuários do ministério.']);
        }
    }

    $json(['ok'=>false,'msg'=>'Ação desconhecida']);
}

/* ------------------ Página (GET) ------------------ */
$idCurso = isset($_GET['idCurso']) ? (int)$_GET['idCurso'] : 0;
if ($idCurso <= 0) {
    echo "<h3 style='text-align:center;margin-top:20%;color:red;'>Curso inválido.</h3>";
    exit;
}

// Buscar curso
$stmt = $pdo->prepare("SELECT * FROM asy_cursos WHERE idCurso = ?");
$stmt->execute([$idCurso]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$curso) {
    echo "<h3 style='text-align:center;margin-top:20%;color:red;'>Curso não encontrado.</h3>";
    exit;
}

// Buscar ministérios e usuários
$ministerios = $pdo->query("SELECT idMinisterio, descricaoMinisterio FROM asy_ministerios ORDER BY descricaoMinisterio ASC")->fetchAll(PDO::FETCH_ASSOC);
$usuarios = $pdo->query("SELECT idUsuario, nomeCompletoUsuario, idMinisterioUsuario FROM asy_usuarios ORDER BY nomeCompletoUsuario ASC")->fetchAll(PDO::FETCH_ASSOC);

// Usuários liberados para o curso
$stmt = $pdo->prepare("SELECT idUsuario FROM asy_usuario_curso WHERE idCurso = ?");
$stmt->execute([$idCurso]);
$liberados = $stmt->fetchAll(PDO::FETCH_COLUMN,0);

$usuarios_json = json_encode($usuarios, JSON_UNESCAPED_UNICODE);
$ministerios_json = json_encode($ministerios, JSON_UNESCAPED_UNICODE);
$liberados_json = json_encode($liberados, JSON_UNESCAPED_UNICODE);

// Imagem pública
$imagemPublic = $curso['imagemCurso'] ?: null;
?>



<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Editar Curso — <?= htmlspecialchars($curso['nomeCurso']) ?></title>
<link rel="stylesheet" href="<?= ASSETS_URL ?>/plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/dist/css/adminlte.min.css">
<style>
/* estilos mínimos (integração com seu tema) */
.card-curso { border-radius:6px; }
.thumbnail-preview { width:100%; max-height:200px; object-fit:cover; border-radius:6px; border:1px solid #ddd; }
.user-list { max-height:420px; overflow:auto; padding:6px; border:1px solid rgba(255,255,255,0.03); border-radius:6px; background:transparent;}
.user-item { display:flex; justify-content:space-between; align-items:center; padding:8px; border-bottom:1px solid rgba(255,255,255,0.03); }
.user-item:last-child{ border-bottom:0; }
.min-filter { margin-bottom:8px; }
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
    <div class="container-fluid" align="center">
      <h3><i class="fas fa-book"></i> Editar Curso</h3>
      <p>(<?= htmlspecialchars($curso['nomeCurso']) ?>)</p>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card card-primary card-outline">
        <div class="card-body">
          <ul class="nav nav-tabs" id="tabsCurso" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tabDados">Dados do Curso</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabUsuarios">Liberação por Usuário</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabMinisterios">Liberação por Ministério</a></li>
          </ul>

          <div class="tab-content mt-3">
            <!-- DADOS -->
            <div class="tab-pane fade show active" id="tabDados">
              <form id="formCurso" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save_course">
                <input type="hidden" name="idCurso" value="<?= (int)$curso['idCurso'] ?>">

                <div class="row">
                  <div class="col-md-8">
                    <div class="form-group">
                      <label>Nome do Curso</label>
                      <input type="text" name="nomeCurso" class="form-control" required value="<?= htmlspecialchars($curso['nomeCurso']) ?>">
                    </div>

                    <div class="form-group">
                      <label>Descrição</label>
                      <textarea name="descricaoCurso" class="form-control" rows="6"><?= htmlspecialchars($curso['descricaoCurso']) ?></textarea>
                    </div>

                    <div class="form-group">
                      <label>Status</label>
                      <select name="statusCurso" class="form-control">
                        <option value="ativo" <?= $curso['statusCurso'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                        <option value="finalizado" <?= $curso['statusCurso'] === 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                        <option value="inativo" <?= $curso['statusCurso'] === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                      </select>
                    </div>

                    <div class="form-group">
                      <label>Ministério (opcional)</label>
                      <select name="idMinisterio" class="form-control">
                        <option value="">— Nenhum —</option>
                        <?php foreach ($ministerios as $m): ?>
                          <option value="<?= $m['idMinisterio'] ?>" <?= ($curso['idMinisterio'] == $m['idMinisterio']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['descricaoMinisterio']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div>
                      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                      <span id="statusSave" style="margin-left:10px;"></span>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label>Capa do Curso</label>
                    <div class="form-group">
                      <?php if ($imagemPublic): ?>
                          <img id="previewImg" src="<?= htmlspecialchars($imagemPublic) ?>" class="thumbnail-preview" alt="Capa atual">
                      <?php else: ?>
                          <img id="previewImg" src="<?= ASSETS_URL ?>/img/curso-thumbnail-default.jpg" class="thumbnail-preview" alt="Sem capa">
                      <?php endif; ?>
                    </div>

                    <div class="form-group">
                      <input type="file" name="imagemCurso" id="imagemCurso" accept="image/*" class="form-control-file">
                      <small class="form-text text-muted">JPG / PNG / WEBP — máximo recomendado 2MB</small>
                    </div>
                    
                  </div>
                </div>
              </form>
            </div>

            <!-- LIBERAÇÃO POR USUÁRIO -->
            <div class="tab-pane fade" id="tabUsuarios">
              <div class="row">
                <div class="col-md-4">
                  <label>Filtrar por Ministério</label>
                  <select id="filtroMinisterioUsers" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($ministerios as $m): ?>
                      <option value="<?= $m['idMinisterio'] ?>"><?= htmlspecialchars($m['descricaoMinisterio']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-8 text-right">
                  <label>&nbsp;</label><br>
                  <button id="btnMarcarTodos" class="btn btn-secondary btn-sm"><i class="fas fa-check-square"></i> Marcar todos</button>
                  <button id="btnAplicarSelecionados" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Aplicar Selecionados</button>
                  <button id="btnRemoverSelecionados" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i> Remover Selecionados</button>
                </div>
              </div>

              <div class="mt-3 user-list" id="userList">
                <!-- lista gerada via JS -->
              </div>
            </div>

            <!-- LIBERAÇÃO POR MINISTÉRIO -->
            <div class="tab-pane fade" id="tabMinisterios">
              <div class="mb-2">
                <label>Selecione um ministério</label>
                <select id="selectMinisterioBatch" class="form-control">
                  <option value="">Escolha um ministério</option>
                  <?php foreach ($ministerios as $m): ?>
                    <option value="<?= $m['idMinisterio'] ?>"><?= htmlspecialchars($m['descricaoMinisterio']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <button id="btnAssignMin" class="btn btn-success"><i class="fas fa-user-plus"></i> Liberar Todos do Ministério</button>
                <button id="btnRemoveMin" class="btn btn-outline-danger"><i class="fas fa-user-minus"></i> Remover Todos do Ministério</button>
              </div>

              <div>
                <small>Ao liberar por ministério, todos os usuários com esse ministério receberão acesso ao curso.</small>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>
</div>
</div>

<!-- variáveis JS -->
<script>
const USUARIOS = <?= $usuarios_json ?>;
const MINISTERIOS = <?= $ministerios_json ?>;
const LIBERADOS = <?= $liberados_json ?>;
const ID_CURSO = <?= (int)$curso['idCurso'] ?>;
</script>

<script src="<?= ASSETS_URL ?>/plugins/jquery/jquery.min.js"></script>
<script src="<?= ASSETS_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>/js/logout.js"></script>

<script>
// helpers
function ajaxPost(formData) {
    return fetch(window.location.href, { method: 'POST', body: formData })
        .then(r => r.json());
}

// ---------- Form salvar curso ----------
$('#formCurso').on('submit', function(e){
    e.preventDefault();
    const fd = new FormData(this);
    fd.set('action','save_course');
    fd.set('idCurso', ID_CURSO);

    $('#statusSave').text(' Salvando...');
    ajaxPost(fd).then(res=>{
        if (res.ok) {
            $('#statusSave').html('<span class="text-success">Salvo.</span>');
            if (res.imagem) {
                // atualizar preview (força cache-bust)
                $('#previewImg').attr('src', res.imagem + '?v=' + Date.now());
            }
        } else {
            $('#statusSave').html('<span class="text-danger">'+res.msg+'</span>');
        }
        setTimeout(()=>$('#statusSave').text(''), 2500);
    }).catch(err=>{
        $('#statusSave').html('<span class="text-danger">Erro.</span>');
    });
});

// preview de imagem
$('#imagemCurso').on('change', function(){
    const f = this.files[0];
    if (!f) return;
    const url = URL.createObjectURL(f);
    $('#previewImg').attr('src', url);
});

// ---------- Lista de usuários (render) ----------
function renderUserList(filterMinisterio = '') {
    const container = $('#userList');
    container.empty();
    const filtered = USUARIOS.filter(u => !filterMinisterio || String(u.idMinisterioUsuario) === String(filterMinisterio));
    if (filtered.length === 0) {
        container.html('<div class="p-3 text-muted">Nenhum usuário encontrado.</div>');
        return;
    }
    filtered.forEach(u=>{
        const checked = LIBERADOS.indexOf(u.idUsuario) !== -1;
        const minLabel = (function(){
            const m = MINISTERIOS.find(x => x.idMinisterio == u.idMinisterioUsuario);
            return m ? m.descricaoMinisterio : '-';
        })();
        const item = $(`
            <div class="user-item" data-id="${u.idUsuario}">
                <div>
                    <strong>${escapeHtml(u.nomeCompletoUsuario)}</strong><br>
                    <small class="text-muted">${escapeHtml(minLabel)}</small>
                </div>
                <div>
                    <input type="checkbox" class="user-checkbox" ${checked ? 'checked' : ''}>
                </div>
            </div>
        `);
        container.append(item);
    });
}

// escape
function escapeHtml(s) {
    return String(s).replace(/[&<>"'`]/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#x60;'}[m]; });
}

$(function(){
    // render inicial
    renderUserList('');

    // filtro por ministerio
    $('#filtroMinisterioUsers').on('change', function(){
        renderUserList(this.value);
    });

    // aplicar selecionados -> atribuir
    $('#btnAplicarSelecionados').on('click', function(){
        const checked = $('.user-checkbox:checked').closest('.user-item').map(function(){ return $(this).data('id'); }).get();
        if (checked.length === 0) return Swal.fire('Nenhum usuário selecionado','Selecione usuários para liberar','info');
        // para cada, chamar assign_user (pode otimizar em batch se quiser)
        Swal.fire({ title: 'Atribuindo...', allowOutsideClick:false, didOpen: ()=> Swal.showLoading() });
        Promise.all(checked.map(id => {
            const fd = new FormData();
            fd.append('action','assign_user');
            fd.append('idCurso', ID_CURSO);
            fd.append('idUsuario', id);
            return ajaxPost(fd);
        })).then(results=>{
            Swal.close();
            // atualizar LIBERADOS localmente
            checked.forEach(id=>{
                if (LIBERADOS.indexOf(id) === -1) LIBERADOS.push(id);
            });
            Swal.fire('Pronto','Usuários liberados com sucesso.','success');
            renderUserList($('#filtroMinisterioUsers').val());
        }).catch(()=>{ Swal.fire('Erro','Falha ao liberar alguns usuários.','error') });
    });

    // remover selecionados -> remover
    $('#btnRemoverSelecionados').on('click', function(){
        const checked = $('.user-checkbox:checked').closest('.user-item').map(function(){ return $(this).data('id'); }).get();
        if (checked.length === 0) return Swal.fire('Nenhum usuário selecionado','Selecione usuários para remover','info');

        Swal.fire({
            title:'Remover acesso?',
            text: 'Removerá o acesso dos usuários selecionados a este curso.',
            icon:'warning', showCancelButton:true
        }).then(result=>{
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Removendo...', allowOutsideClick:false, didOpen: ()=> Swal.showLoading() });
            Promise.all(checked.map(id => {
                const fd = new FormData();
                fd.append('action','remove_user');
                fd.append('idCurso', ID_CURSO);
                fd.append('idUsuario', id);
                return ajaxPost(fd);
            })).then(results=>{
                // atualizar LIBERADOS
                checked.forEach(id=>{
                    const idx = LIBERADOS.indexOf(id);
                    if (idx !== -1) LIBERADOS.splice(idx,1);
                });
                Swal.close();
                Swal.fire('Pronto','Acessos removidos.','success');
                renderUserList($('#filtroMinisterioUsers').val());
            }).catch(()=>{
                Swal.fire('Erro','Falha ao remover acessos','error');
            });
        });
    });

    // ministerio: assign all
    $('#btnAssignMin').on('click', function(){
        const idMin = $('#selectMinisterioBatch').val();
        if (!idMin) return Swal.fire('Escolha um ministério','Selecione antes','info');
        Swal.fire({ title:'Liberar todos?', text:'Liberar todos os usuários deste ministério para o curso?', showCancelButton:true }).then(res=>{
            if (!res.isConfirmed) return;
            const fd = new FormData();
            fd.append('action','assign_ministry');
            fd.append('idCurso', ID_CURSO);
            fd.append('idMinisterio', idMin);
            fd.append('assign', 1);
            Swal.fire({ title:'Atribuindo...', allowOutsideClick:false, didOpen: ()=> Swal.showLoading() });
            ajaxPost(fd).then(r=>{
                Swal.close();
                if (r.ok) {
                    // atualizar LIBERADOS: adicionar todos usuarios daquele ministerio
                    USUARIOS.forEach(u => { if (String(u.idMinisterioUsuario) === String(idMin) && LIBERADOS.indexOf(u.idUsuario) === -1) LIBERADOS.push(u.idUsuario); });
                    Swal.fire('Sucesso', r.msg, 'success');
                    renderUserList($('#filtroMinisterioUsers').val());
                } else Swal.fire('Erro', r.msg, 'error');
            }).catch(()=> Swal.fire('Erro','Falha na requisição','error'));
        });
    });

    // ministerio: remove all
    $('#btnRemoveMin').on('click', function(){
        const idMin = $('#selectMinisterioBatch').val();
        if (!idMin) return Swal.fire('Escolha um ministério','Selecione antes','info');
        Swal.fire({ title:'Remover todos?', text:'Remover o acesso de todos usuários deste ministério para o curso?', icon:'warning', showCancelButton:true }).then(res=>{
            if (!res.isConfirmed) return;
            const fd = new FormData();
            fd.append('action','assign_ministry');
            fd.append('idCurso', ID_CURSO);
            fd.append('idMinisterio', idMin);
            fd.append('assign', 0);
            Swal.fire({ title:'Removendo...', allowOutsideClick:false, didOpen: ()=> Swal.showLoading() });
            ajaxPost(fd).then(r=>{
                Swal.close();
                if (r.ok) {
                    // atualizar LIBERADOS: remover todos usuarios daquele ministerio
                    USUARIOS.forEach(u => { if (String(u.idMinisterioUsuario) === String(idMin)) {
                        const idx = LIBERADOS.indexOf(u.idUsuario);
                        if (idx !== -1) LIBERADOS.splice(idx,1);
                    }});
                    Swal.fire('Sucesso', r.msg, 'success');
                    renderUserList($('#filtroMinisterioUsers').val());
                } else Swal.fire('Erro', r.msg, 'error');
            }).catch(()=> Swal.fire('Erro','Falha na requisição','error'));
        });
    });

    // Quando clicar no checkbox individual, fazer toggle imediato (assign/remove)
    $(document).on('change', '.user-checkbox', function(){
        const $it = $(this).closest('.user-item');
        const idUser = $it.data('id');
        const checked = $(this).is(':checked');
        const fd = new FormData();
        fd.append('action', checked ? 'assign_user' : 'remove_user');
        fd.append('idCurso', ID_CURSO);
        fd.append('idUsuario', idUser);
        ajaxPost(fd).then(r=>{
            if (!r.ok) Swal.fire('Erro', r.msg, 'error');
            else {
                if (checked) {
                    if (LIBERADOS.indexOf(idUser) === -1) LIBERADOS.push(idUser);
                } else {
                    const idx = LIBERADOS.indexOf(idUser);
                    if (idx !== -1) LIBERADOS.splice(idx,1);
                }
            }
        }).catch(()=> Swal.fire('Erro','Falha na requisição','error'));
    });

});

// Marcar todos
$('#btnMarcarTodos').on('click', function(){
    $('.user-checkbox').prop('checked', true).trigger('change');
});

</script>
</body>
</html>
