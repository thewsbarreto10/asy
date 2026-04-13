<?php
require_once __DIR__ . '../../../app/core/init.php';        // inicializa config e session
require_once __DIR__ . '../../../app/core/sessao_segura.php'; // valida sessão
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sobre Nós - Telenovo Telecom</title>
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/dist/css/adminlte.min.css">
  <style>
    .about-section {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      margin-bottom: 4rem;
    }
    .about-section .text {
      flex: 1;
      padding: 2rem;
    }
    .about-section .image {
      flex: 1;
      text-align: center;
    }
    .about-section .image img {
      width: 100%;
      max-width: 500px;
      border-radius: 1rem;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    .icon-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 2rem;
      margin-top: 2rem;
    }
    .icon-box {
      text-align: center;
      padding: 1.5rem;
      border-radius: 1rem;
      background: #f8f9fa;
      transition: 0.3s;
    }
    .icon-box:hover {
      background: #007bff;
      color: #fff;
      transform: translateY(-5px);
    }
    .icon-box i {
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
    }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php
require_once __DIR__ . '../../../app/views/navbar.php';
require_once __DIR__ . '../../../app/views/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Sobre Nós</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Início</a></li>
            <li class="breadcrumb-item active">Sobre Nós</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <!-- Conteúdo principal -->
  <section class="content">
    <div class="container-fluid">

      <!-- Carrossel -->
      <div id="carouselExampleIndicators" class="carousel slide mb-5" data-ride="carousel">
        <ol class="carousel-indicators">
          <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
          <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
          <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner rounded">
          <div class="carousel-item active">
            <img class="d-block w-100" src="assets/img/carousel1.jpg" alt="PABX em nuvem">
            <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-2">
              <h5>PABX em Nuvem</h5>
              <p>Comunicação moderna e flexível para sua empresa.</p>
            </div>
          </div>
          <div class="carousel-item">
            <img class="d-block w-100" src="assets/img/carousel2.jpg" alt="CFTV e segurança">
            <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-2">
              <h5>CFTV Inteligente</h5>
              <p>Monitoramento e segurança com alta performance.</p>
            </div>
          </div>
          <div class="carousel-item">
            <img class="d-block w-100" src="assets/img/carousel3.jpg" alt="Automação e IA">
            <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-2">
              <h5>Automação com IA</h5>
              <p>Bots de WhatsApp e soluções inteligentes para o seu negócio.</p>
            </div>
          </div>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </a>
      </div>

      <!-- Seção 1 -->
      <div class="about-section">
        <div class="text">
          <h2><i class="fas fa-network-wired text-primary"></i> Quem Somos</h2>
          <p>
            A <strong>Telenovo Telecom</strong> é uma empresa especializada em soluções completas de comunicação e tecnologia.
            Oferecemos <strong>PABX em nuvem</strong>, <strong>CFTV</strong>, <strong>telefonia IP</strong>,
            <strong>bots de WhatsApp com IA</strong> e <strong>cabeamento estruturado</strong>.
          </p>
          <p>
            Nosso objetivo é simplificar a comunicação corporativa, proporcionando inovação e confiabilidade em cada serviço prestado.
          </p>
        </div>
        <div class="image">
          <img src="assets/img/about1.jpg" alt="Equipe Telenovo">
        </div>
      </div>

      <!-- Seção 2 (imagem invertida) -->
      <div class="about-section flex-row-reverse">
        <div class="text">
          <h2><i class="fas fa-lightbulb text-primary"></i> Nossa Missão</h2>
          <p>
            Fornecer soluções tecnológicas acessíveis, modernas e seguras que impulsionem o crescimento de empresas em todo o Brasil.
          </p>
          <p>
            Buscamos sempre alinhar tecnologia e atendimento humano para entregar uma experiência diferenciada a cada cliente.
          </p>
        </div>
        <div class="image">
          <img src="assets/img/about2.jpg" alt="Missão Telenovo">
        </div>
      </div>

      <!-- Ícones / Serviços -->
      <div class="card card-outline card-primary">
        <div class="card-body">
          <h3 class="text-center mb-4"><i class="fas fa-cogs"></i> Nossas Soluções</h3>
          <div class="icon-grid">
            <div class="icon-box">
              <i class="fas fa-cloud"></i>
              <h5>PABX em Nuvem</h5>
              <p>Flexibilidade e economia com telefonia hospedada.</p>
            </div>
            <div class="icon-box">
              <i class="fas fa-video"></i>
              <h5>CFTV</h5>
              <p>Segurança e monitoramento 24h com câmeras IP.</p>
            </div>
            <div class="icon-box">
              <i class="fas fa-robot"></i>
              <h5>Bots com IA</h5>
              <p>Atendimento automatizado e inteligente no WhatsApp.</p>
            </div>
            <div class="icon-box">
              <i class="fas fa-network-wired"></i>
              <h5>Cabeamento Estruturado</h5>
              <p>Infraestrutura robusta e organizada para sua rede.</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<!-- Scripts -->
<script src="<?= ASSETS_URL ?>/plugins/jquery/jquery.min.js"></script>
<script src="<?= ASSETS_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= ASSETS_URL ?>/js/logout.js"></script>

</body>
</html>
