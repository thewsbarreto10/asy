<?php

$env = parse_ini_file(__DIR__ . '/config.env');

return [
    'host'    => $env['DB_HOST'] ?? '',
    'db'      => $env['DB_NAME'] ?? '',
    'user'    => $env['DB_USER'] ?? '',
    'pass'    => $env['DB_PASS'] ?? '',
    'charset' => $env['DB_CHARSET'] ?? ''
];
