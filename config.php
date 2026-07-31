<?php
/**
 * Runtime configuration.
 *
 * Keep secrets in server environment variables, .env, or config.local.php.
 * Local secret files are intentionally ignored by Git.
 */
$dotenvFile = __DIR__ . '/.env';
$dotenv = is_file($dotenvFile)
    ? (parse_ini_file($dotenvFile, false, INI_SCANNER_RAW) ?: [])
    : [];

$env = static function (string $name, string $default = '') use ($dotenv): string {
    $value = getenv($name);
    if ($value !== false) {
        return $value;
    }

    return array_key_exists($name, $dotenv) ? (string) $dotenv[$name] : $default;
};

$envBool = static function (string $name, bool $default = true) use ($env): bool {
    $value = $env($name, $default ? 'true' : 'false');
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
};

$config = [
    'smtp_host'       => $env('SMTP_HOST', 'smtp.gmail.com'),
    'smtp_port'       => (int) $env('SMTP_PORT', '587'),
    'smtp_timeout'    => (int) $env('SMTP_TIMEOUT', '8'),
    'smtp_encryption' => strtolower($env('SMTP_ENCRYPTION', 'tls')),
    'smtp_auth'       => $envBool('SMTP_AUTH', true),
    'smtp_username'   => $env('SMTP_USERNAME', 'rakhis@elitemart.co.in'),
    'smtp_password'   => $env('SMTP_PASSWORD', ''),
    'mail_from'       => $env('MAIL_FROM', 'rakhis@elitemart.co.in'),
    'mail_from_name'  => $env('MAIL_FROM_NAME', 'Vdesiconnect'),
    'admin_email'     => $env('ADMIN_EMAIL', 'rakhis@elitemart.co.in'),
    'admin_username'  => $env('ADMIN_USERNAME', 'admin'),
    'admin_password'  => $env('ADMIN_PASSWORD', ''),
];

$localConfigFile = __DIR__ . '/config.local.php';
if (is_file($localConfigFile)) {
    $localConfig = require $localConfigFile;
    if (is_array($localConfig)) {
        $config = array_replace($config, $localConfig);
        }
}

return $config;
