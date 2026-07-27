<?php
/**
 * Runtime configuration.
 *
 * Keep secrets in server environment variables or config.local.php. The local
 * file is intentionally ignored by Git and may return an array overriding any
 * of the values below.
 */
$env = static function (string $name, string $default = ''): string {
    $value = getenv($name);
    return $value === false ? $default : $value;
};

$config = [
    'smtp_host'       => $env('SMTP_HOST', 'smtpout.secureserver.net'),
    'smtp_port'       => (int) $env('SMTP_PORT', '465'),
    'smtp_encryption' => strtolower($env('SMTP_ENCRYPTION', 'ssl')),
    'smtp_username'   => $env('SMTP_USERNAME', 'rakhis@vdesiconnect.com'),
    'smtp_password'   => $env('SMTP_PASSWORD', ''),
    'mail_from'       => $env('MAIL_FROM', 'rakhis@vdesiconnect.com'),
    'mail_from_name'  => $env('MAIL_FROM_NAME', 'Vdesiconnect'),
    'admin_email'     => $env('ADMIN_EMAIL', 'rakhis@elitemart.co.in'),
];

$localConfigFile = __DIR__ . '/config.local.php';
if (is_file($localConfigFile)) {
    $localConfig = require $localConfigFile;
    if (is_array($localConfig)) {
        $config = array_replace($config, $localConfig);
    }
}

return $config;
