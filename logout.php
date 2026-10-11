<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Érvénytelen kérés. A chat kijelentkezés gombját használd.');
}
if (isset($_SESSION['user_id'])) {
    try {
        $pdo = database();
        $query = $pdo->prepare('UPDATE users SET last_seen = DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY) WHERE id = ?');
        $query->execute([$_SESSION['user_id']]);
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
    }
}
$_SESSION = [];
setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
session_destroy();
header('Location: login.php');
exit;
