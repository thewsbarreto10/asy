<?php
require_once __DIR__ . '/../../../app/core/init.php';
require_once __DIR__ . '/../../../app/core/sessao_segura.php';

// Verifica se o usuário está logado
if (!$usuario) {
    header('Location: /asy/');
    exit;
}

// Permissões
$permiteEditar = ($usuario['idPermissaoUsuario'] == 1); // apenas administradores
$permiteVisualizar = in_array($usuario['idPermissaoUsuario'], [1,2]); // Adm ou coordenador

if (!$permiteVisualizar) {
    echo "<script>alert('Acesso negado.'); window.location.href='/asy/';</script>";
    exit;
}

// Busca eventos (com filtro opcional)
$filtro = trim($_GET['filtro'] ?? '');
$where = '';
$params = [];

if ($filtro !== '') {
    $where = "WHERE (e.nomeEvento LIKE :filtro OR e.tipoEvento LIKE :filtro)";
    $params['filtro'] = "%$filtro%";
}

// ❗ JOIN corrigido
$stmt = $pdo->prepare("
    SELECT 
        e.idEvento, e.nomeEvento, e.tipoEvento, e.dataHoraInicio, 
        e.dataHoraFim, e.idMinisterio, e.statusEvento,
        m.descricaoMinisterio
    FROM asy_eventos e
    LEFT JOIN asy_ministerios m ON m.idMinisterio = e.idMinisterio
    $where
    ORDER BY e.dataHoraInicio ASC
");
$stmt->execute($params);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow">

    <title>Lista de Eventos - ASY Gospel Church</title>

    <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/plugins/fontawesome-free/css/all.min.css" />
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/dist/css/adminlte.min.css" />
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/estilo_lista-cursos.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" />

</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <?php require_once __DIR__ . '/../../../app/views/navbar.php'; ?>
    <?php require_once __DIR__ . '/../../../app/views/sidebar.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">

                <h4 class="text-center"><i class="fas fa-calendar-alt"></i> Lista de Eventos</h4>
                <br>

                <div class="search-register-container">
                    <div class="search-box">
                        <input type="search" id="tableSearch" placeholder="Buscar por nome ou tipo..." />
                        <i class="fas fa-search"></i>
                    </div>

                    <?php if($permiteEditar): ?>
                        <button id="btnCadastrar" onclick="window.location.href='cadastrar_evento.php'">
                            <i class="fas fa-plus-circle"></i> Cadastrar
                        </button>
                    <?php endif; ?>
                </div>

                <div class="table-responsive">
                    <table id="eventosTable" class="table table-bordered table-striped">
                        <thead class="table-dark">
                        <tr>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Início</th>
                            <th>Encerra</th>
                            <th>Ministério</th>
                            <th>Status</th>
                            <?php if ($permiteEditar): ?><th>Ações</th><?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>

                        <?php if (count($eventos) > 0): ?>
                            <?php foreach ($eventos as $e): ?>
                                <?php $inativo = ($e['statusEvento'] === 'inativo'); ?>

                                <tr class="<?= $inativo ? 'usuario-inativo' : '' ?>" data-id="<?= $e['idEvento'] ?>">
                                    <td><b><?= htmlspecialchars($e['nomeEvento']) ?></b></td>
                                    <td><?= htmlspecialchars($e['tipoEvento']) ?></td>
<td><?= date("d/m/Y H:i", strtotime($e['dataHoraInicio'])) ?></td>
<td><?= date("d/m/Y H:i", strtotime($e['dataHoraFim'])) ?></td>

                                    <td><?= htmlspecialchars($e['descricaoMinisterio'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($e['statusEvento']) ?></td>

                                    <?php if ($permiteEditar): ?>
                                        <td class="text-center">

                                            <a href="editar_evento.php?idEvento=<?= $e['idEvento'] ?>" 
                                               class="btn btn-sm btn-outline-info <?= $inativo ? 'disabled' : '' ?>">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>

                                            <button class="btn btn-sm btn-outline-<?= $inativo ? 'success':'danger' ?> btn-toggle-status"
                                                    data-id="<?= $e['idEvento'] ?>"
                                                    data-status="<?= $inativo ? 'ativo' : 'inativo' ?>">
                                                <i class="fas fa-<?= $inativo ? 'redo' : 'times' ?>"></i>
                                                <?= $inativo ? 'Reativar' : 'Inativar' ?>
                                            </button>

                                        </td>
                                    <?php endif; ?>
                                </tr>

                            <?php endforeach; ?>
                        <?php else: ?>

                            <tr>
                                <td colspan="<?= $permiteEditar ? 7 : 6 ?>" class="text-center">
                                    Nenhum evento encontrado.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>

            </div>
        </section>
    </div>
</div>

<script src="<?= ASSETS_URL ?>/plugins/jquery/jquery.min.js"></script>
<script src="<?= ASSETS_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/dist/js/adminlte.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.colVis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script src="<?= ASSETS_URL ?>/js/script_lista-eventos.js?v=<?= time() ?>"></script>

</body>
</html>
