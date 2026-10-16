<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    jsonResponse(['error' => 'Csak GET kérés engedélyezett.'], 405);
}
if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'A belépés lejárt.'], 401);
}
$userId = $_SESSION['user_id'];
// A session zárolását elengedjük, hogy a küldésnek ne kelljen várnia.
session_write_close();
$after = filter_input(INPUT_GET, 'after', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
if ($after === false || $after === null) {
    jsonResponse(['error' => 'Érvénytelen üzenetazonosító.'], 422);
}
try {
    $pdo = database();
    $query = $pdo->prepare('UPDATE users SET last_seen = UTC_TIMESTAMP() WHERE id = ?');
    $query->execute([$userId]);
    // Lapozva kérjük le az előzményeket, így egy régebbi üzenet sem marad ki.
    $query = $pdo->prepare('SELECT messages.id, user_id, username, message, created_at FROM messages JOIN users ON users.id = messages.user_id WHERE messages.id > ? ORDER BY messages.id ASC LIMIT 100');
    $query->execute([$after]);
    $messages = $query->fetchAll();
    foreach ($messages as &$message) {
        $message['id'] = (int) $message['id'];
        $message['user_id'] = (int) $message['user_id'];
        $message['created_at'] = str_replace(' ', 'T', $message['created_at']) . 'Z';
    }
    unset($message);
    $users = $pdo->query('SELECT id, username, (last_seen >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 SECOND)) AS online FROM users ORDER BY online DESC, username ASC, id ASC')->fetchAll();
    foreach ($users as &$user) {
        $user['id'] = (int) $user['id'];
        $user['online'] = (bool) $user['online'];
    }
    unset($user);
    jsonResponse(['messages' => $messages, 'users' => $users, 'has_more' => count($messages) === 100]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    jsonResponse(['error' => 'Nem érjük el az adatbázist. Ellenőrizd a MySQL-t és a config.php beállításait.'], 503);
}
