<?php

require_once __DIR__ . '/../app/core/init.php';

$dataInicio = $_GET['dataInicio'] ?? null;
$dataFim = $_GET['dataFim'] ?? null;

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if($pagina < 1){
    $pagina = 1;
}

$limite = 5;
$offset = ($pagina - 1) * $limite;

$params = [];

$sqlBase = "
FROM asy_eventos e
LEFT JOIN asy_usuarios u 
    ON u.idUsuario = e.idResponsavel
";

if ($dataInicio && $dataFim) {

    $where = "WHERE e.statusEvento = 'ativo' AND DATE(e.dataHoraInicio) BETWEEN :inicio AND :fim";
    $params['inicio'] = $dataInicio;
    $params['fim'] = $dataFim;

} else {

    $where = "WHERE e.dataHoraInicio >= NOW() AND e.statusEvento = 'ativo'";

}

# TOTAL EVENTOS
$sqlTotal = "SELECT COUNT(*) $sqlBase $where";

$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute($params);

$totalEventos = $stmtTotal->fetchColumn();

$totalPaginas = ceil($totalEventos / $limite);

# EVENTOS
$sql = "
SELECT
    e.nomeEvento,
    e.dataHoraInicio,
    u.nomeCompletoUsuario AS responsavel
$sqlBase
$where
ORDER BY e.dataHoraInicio ASC
LIMIT $limite OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];

foreach ($eventos as $evento) {

    $data = new DateTime($evento['dataHoraInicio']);

    $dia = $data->format('d');
    $mes = $meses[(int)$data->format('n') - 1];
    $hora = $data->format('H:i');

    echo '
    <div style="display:flex; align-items:center; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.2);">

        <div style="width:50px;text-align:center;margin-right:10px;">
            <div style="font-size:22px;font-weight:bold;">'.$dia.'</div>
            <div style="font-size:12px;">'.$mes.'</div>
        </div>

        <div style="flex:1;">

            <div style="font-size:14px;font-weight:600;">
                '.strtoupper(htmlspecialchars($evento['nomeEvento'])).'
            </div>

            <div style="font-size:12px;">
                <i class="far fa-clock"></i> '.$hora.'
            </div>

            <div style="font-size:12px;opacity:0.8;">
                <i class="fas fa-user"></i> '.htmlspecialchars($evento['responsavel'] ?? 'Responsável não definido').'
            </div>

        </div>

    </div>';
}

if(!$eventos){
    echo '<div style="padding:10px;">Nenhum evento encontrado</div>';
}

# PAGINAÇÃO
if($totalPaginas > 1){

    echo '<div class="paginacao-eventos mt-3">';

    # ANTERIOR
    if($pagina > 1){
        echo '<button class="btn-page" onclick="mudarPagina('.($pagina-1).')">‹</button>';
    }

    # BOTÕES NUMERADOS
    for($i = 1; $i <= $totalPaginas; $i++){

        $active = ($i == $pagina) ? 'active' : '';

        echo '<button 
                class="btn-page '.$active.'" 
                onclick="mudarPagina('.$i.')">
                '.$i.'
              </button>';
    }

    # PRÓXIMO
    if($pagina < $totalPaginas){
        echo '<button class="btn-page" onclick="mudarPagina('.($pagina+1).')">›</button>';
    }

    echo '</div>';
}