<?php

// Carrega o arquivo .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue;
        }
        
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
        putenv(sprintf('%s=%s', trim($key), trim($value)));
    }
}

// Define constantes para uso em todo o sistema
define('WP_PATH', getenv('WP_PATH'));
define('WP_URL', getenv('WP_URL'));
define('WP_ADMIN_USER', getenv('WP_ADMIN_USER'));
define('WP_ADMIN_PASSWORD', getenv('WP_ADMIN_PASSWORD'));

define('DB_HOST', getenv('DB_HOST'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASSWORD', getenv('DB_PASSWORD'));

define('BACKUP_PATH', getenv('BACKUP_PATH'));
define('BACKUP_RETENTION_DAYS', getenv('BACKUP_RETENTION_DAYS'));

define('API_URL', getenv('API_URL'));
define('FRONTEND_URL', getenv('FRONTEND_URL'));

define('JWT_SECRET', getenv('JWT_SECRET'));
define('API_TOKEN', getenv('API_TOKEN'));

// Função para verificar se todas as configurações necessárias estão definidas
function checkConfig() {
    $requiredEnvVars = [
        'WP_PATH',
        'WP_URL',
        'WP_ADMIN_USER',
        'WP_ADMIN_PASSWORD',
        'DB_HOST',
        'DB_NAME',
        'DB_USER',
        'DB_PASSWORD',
        'BACKUP_PATH',
        'BACKUP_RETENTION_DAYS',
        'API_URL',
        'FRONTEND_URL',
        'JWT_SECRET',
        'API_TOKEN'
    ];

    $missingVars = [];
    foreach ($requiredEnvVars as $var) {
        if (!getenv($var)) {
            $missingVars[] = $var;
        }
    }

    if (!empty($missingVars)) {
        throw new Exception('Variáveis de ambiente necessárias não encontradas: ' . implode(', ', $missingVars));
    }

    // Verifica se o diretório do WordPress existe
    if (!is_dir(WP_PATH)) {
        throw new Exception('Diretório do WordPress não encontrado: ' . WP_PATH);
    }

    // Verifica se o diretório de backup existe e é gravável
    if (!is_dir(BACKUP_PATH)) {
        if (!mkdir(BACKUP_PATH, 0755, true)) {
            throw new Exception('Não foi possível criar o diretório de backup: ' . BACKUP_PATH);
        }
    } elseif (!is_writable(BACKUP_PATH)) {
        throw new Exception('Diretório de backup não é gravável: ' . BACKUP_PATH);
    }
}

// Verifica as configurações ao carregar o arquivo
try {
    checkConfig();
} catch (Exception $e) {
    die('Erro de configuração: ' . $e->getMessage());
} 