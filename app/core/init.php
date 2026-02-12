<?php

date_default_timezone_set('America/Sao_Paulo');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ================================================
// 🔧 CONFIGURAÇÃO GLOBAL DO PROJETO
// ================================================

// Caminhos físicos (no servidor)
define('BASE_PATH', dirname(__DIR__, 2));        // /var/www/html/asy
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public_html');
define('ASSETS_PATH', PUBLIC_PATH . '/assets');
define('CONFIG_PATH', APP_PATH . '/config.php');
define('PROCESS_PATH', APP_PATH . '/processamento');
define('PROXY_PATH', PUBLIC_PATH . '/proxy');

// ================================================
// 🌐 URLs BASE
// ================================================

// Detecta automaticamente o host e protocolo
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Como o Apache está configurado com Alias /asy -> public_html
define('BASE_URL', "{$protocolo}://{$host}/asy"); // ex: http://srv880732.hstgr.cloud/asy
define('PUBLIC_URL', BASE_URL);                    // PUBLIC_PATH já é público
define('ASSETS_URL', PUBLIC_URL . '/assets');    // pasta de assets
define('PAGES_URL', BASE_URL . '/pages');
define('ADMIN_URL', PAGES_URL . '/admin');
define('ASYEAD_URL', PAGES_URL . '/asy-ead');
define('PROXY_URL', BASE_URL . '/proxy');

// ================================================
// ⚙️ CARREGA CONFIG
// ================================================
$config = require CONFIG_PATH;

// ================================================
// 🔒 INICIALIZA SESSÃO SEGURA
// ================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    // Impede ataques de fixação de sessão
    session_regenerate_id(true);
}

// ================================================
// 💾 CONEXÃO PDO GLOBAL
// ================================================
try {
    $dsn = "mysql:host={$config['host']};dbname={$config['db']};charset={$config['charset']}";
    $GLOBALS['pdo'] = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco: " . $e->getMessage());
}
