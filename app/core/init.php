<?php

if (!class_exists(\Delight\Auth\Auth::class)) {
    die('Auth NÃO carregado');
}

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']), // true em produção
    'httponly' => true,
    'samesite' => 'Strict'
]);

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
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

$isLocal = str_contains($host, 'localhost');

$subdir = $isLocal ? '/asy' : '';

$protocolo = $isLocal ? 'http' : 'https';


// Como o Apache está configurado com Alias /asy -> public_html
define('BASE_URL', "{$protocolo}://{$host}{$subdir}");
define('PUBLIC_URL', BASE_URL);
define('ASSETS_URL', BASE_URL . '/assets');
define('PAGES_URL', BASE_URL . '/pages');
define('ADMIN_URL', PAGES_URL . '/admin');
define('ASYEAD_URL', PAGES_URL . '/asy-ead');
define('PROXY_URL', BASE_URL . '/proxy');

// ================================================
// ⚙️ CARREGA CONFIG
// ================================================
$config = require CONFIG_PATH;

require_once BASE_PATH . '/vendor/autoload.php';

// ================================================
// SEGURANÇA
// ================================================

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$csp = "default-src 'self'; ";
$csp .= "script-src 'self' https://cdn.jsdelivr.net; ";
$csp .= "style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; ";
$csp .= "img-src 'self' data:; ";
$csp .= "font-src 'self' https://cdn.jsdelivr.net; ";
$csp .= "connect-src 'self' https://cdn.jsdelivr.net; ";
$csp .= "frame-ancestors 'none'; ";
$csp .= "base-uri 'self'; ";
$csp .= "form-action 'self';";

header("Content-Security-Policy: $csp");
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

// ================================================
// 💾 CONFIGURAÇÃO DE AUTENTICAÇÃO
// ================================================

require_once BASE_PATH . '/vendor/autoload.php';

$auth = new \Delight\Auth\Auth($pdo);

$GLOBALS['auth'] = $auth;

// ================================================
// 🔐 CSRF TOKEN
// ================================================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
