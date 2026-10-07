<?php
// XAMPP alapbeállítások. Ha van MySQL-jelszavad, itt add meg.
$databaseHost = '127.0.0.1';
$databaseName = 'pofazo3000';
$databaseUser = 'root';
$databasePassword = '';

date_default_timezone_set('Europe/Budapest');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
    'path' => '/',
]);
session_start();
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; img-src 'self' data:; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function jsonResponse($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function validToken($token)
{
    return is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

// A több bájton tárolt magyar betűk és emojik is egy karakternek számítanak.
function textLength($text)
{
    return preg_match_all('/./us', $text);
}

function cleanText($text)
{
    if (!is_string($text) || !preg_match('//u', $text)) {
        return '';
    }
    return preg_replace('/^\s+|\s+$/u', '', $text);
}

function database()
{
    global $databaseHost, $databaseName, $databaseUser, $databasePassword;
    return new PDO(
        "mysql:host=$databaseHost;dbname=$databaseName;charset=utf8mb4",
        $databaseUser,
        $databasePassword,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false]
    );
}
