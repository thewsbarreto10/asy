<?php

$totalEventos = $pdo->query("
  SELECT COUNT(*) FROM asy_eventos WHERE statusEvento = 'ativo'
")->fetchColumn(); // ← Pega apenas o valor (número) da contagem

$totalUsuarios = $pdo->query("
  SELECT COUNT(*) FROM asy_usuarios
")->fetchColumn(); // ← Pega apenas o valor (número) da contagem

$totalCursos = $pdo->query("
  SELECT COUNT(*) FROM asy_cursos WHERE statusCurso = 'ativo'
")->fetchColumn(); // ← Pega apenas o valor (número) da contagem

?>