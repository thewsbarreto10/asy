<?php
require_once __DIR__ . '/../../../app/core/init.php';
require_once __DIR__ . '/../../../app/core/sessao_segura.php';

// Apenas administradores podem acessar
if (!$usuario || $usuario['idPermissaoUsuario'] != 1) {
    echo "<h1 style='text-align:center; margin-top:20%; color:red;'>Acesso negado</h1>";
    exit;
}

// Busca cursos disponíveis
$cursos = $pdo->query("SELECT idCurso, nomeCurso FROM asy_cursos ORDER BY nomeCurso")->fetchAll(PDO::FETCH_ASSOC);

// Recebe POST de upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivoVideo'])) {
    $idCurso = (int)($_POST['idCurso'] ?? 0);
    $idModulo = (int)($_POST['idModulo'] ?? 0);
    $tituloVideo = trim($_POST['tituloVideo'] ?? '');
    $arquivo = $_FILES['arquivoVideo'];

    if (!$idCurso || !$idModulo || !$tituloVideo || !$arquivo['name']) {
        $erro = "Preencha todos os campos e selecione um arquivo.";
    } else {
        // Busca nomes de curso e módulo
        $cursoNome = $pdo->prepare("SELECT nomeCurso FROM asy_cursos WHERE idCurso = :id");
        $cursoNome->execute(['id' => $idCurso]);
        $cursoNome = $cursoNome->fetchColumn();

        $moduloNome = $pdo->prepare("SELECT nomeModulo FROM asy_modulos WHERE idModulo = :id AND idCurso = :idCurso");
        $moduloNome->execute(['id' => $idModulo, 'idCurso' => $idCurso]);
        $moduloNome = $moduloNome->fetchColumn();

        if (!$cursoNome || !$moduloNome) {
            $erro = "Curso ou módulo inválido.";
        } else {
            // Função para normalizar nomes de pasta mantendo acentos
            function limparParaPasta($string) {
                $string = trim($string);
                // Substitui apenas caracteres problemáticos em nomes de pastas
                return preg_replace('/[\/\\\\:*?"<>|]/u', '_', $string);
            }

            $cursoSlug = limparParaPasta($cursoNome);
            $moduloSlug = limparParaPasta($moduloNome);
            $tituloSlug = limparParaPasta($tituloVideo);

            // Pasta destino
            $pastaDestino = ASSETS_PATH . "/videos/{$cursoSlug}/{$moduloSlug}/";
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0777, true);
            }

            // Extensão do arquivo original
            $ext = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
            $nomeArquivo = $tituloSlug . '.' . $ext;
            $destino = $pastaDestino . $nomeArquivo;

            if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
                // Salva no banco
                $stmt = $pdo->prepare("
                    INSERT INTO asy_videos (idModulo, tituloVideo, urlVideo, duracaoSegundos, ordemVideo)
                    VALUES (:idModulo, :titulo, :url, 0, 0)
                ");
                $stmt->execute([
                    'idModulo' => $idModulo,
                    'titulo' => $tituloVideo,
                    'url' => "assets/videos/{$cursoSlug}/{$moduloSlug}/{$nomeArquivo}"
                ]);
                $sucesso = "Vídeo enviado com sucesso!";
            } else {
                $erro = "Erro ao mover arquivo para o destino.";
            }
        }
    }
}

// Busca módulos do curso selecionado
$modulos = [];
$cursoSelecionado = $_POST['idCurso'] ?? $cursos[0]['idCurso'] ?? 0;
if ($cursoSelecionado) {
    $stmt = $pdo->prepare("SELECT idModulo, nomeModulo FROM asy_modulos WHERE idCurso = :id ORDER BY ordemModulo");
    $stmt->execute(['id' => $cursoSelecionado]);
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload de Vídeo</title>
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
            <h1>Upload de Vídeo</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Curso</label>
                    <select name="idCurso" id="idCurso" class="form-control" required>
                        <option value="">Selecione o curso</option>
                        <?php foreach($cursos as $c): ?>
                            <option value="<?= $c['idCurso'] ?>" <?= ($cursoSelecionado == $c['idCurso']) ? 'selected' : '' ?>><?= htmlspecialchars($c['nomeCurso']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Módulo</label>
                    <select name="idModulo" id="idModulo" class="form-control" required>
                        <option value="">Selecione o módulo</option>
                        <?php foreach($modulos as $m): ?>
                            <option value="<?= $m['idModulo'] ?>"><?= htmlspecialchars($m['nomeModulo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Título do vídeo</label>
                    <input type="text" name="tituloVideo" class="form-control" value="<?= htmlspecialchars($_POST['tituloVideo'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Arquivo de vídeo</label>
                    <input type="file" name="arquivoVideo" class="form-control" accept="video/*" required>
                </div>

                <button type="submit" class="btn btn-primary">Enviar Vídeo</button>
            </form>
        </div>
    </section>
</div>

<script src="<?= ASSETS_URL ?>/plugins/jquery/jquery.min.js"></script>
<script src="<?= ASSETS_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>/js/logout.js"></script>

<script>
$(document).ready(function(){
    $('#idCurso').change(function(){
        var cursoId = $(this).val();
        if(cursoId){
            $.get('listar_modulos.php', {idCurso: cursoId}, function(data){
                $('#idModulo').html(data);
            });
        }
    });

    <?php if(!empty($sucesso)): ?>
    Swal.fire({
        icon: 'success',
        title: 'Sucesso',
        text: '<?= addslashes($sucesso) ?>'
    });
    <?php endif; ?>

    <?php if(!empty($erro)): ?>
    Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: '<?= addslashes($erro) ?>'
    });
    <?php endif; ?>
});
</script>

</body>
</html>
