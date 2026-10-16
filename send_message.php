<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    jsonResponse(['error' => 'Csak POST kérés engedélyezett.'], 405);
}
if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'A belépés lejárt.'], 401);
}
if (!validToken($_POST['csrf_token'] ?? null)) {
    jsonResponse(['error' => 'A munkamenet lejárt. Frissítsd az oldalt.'], 403);
}
$message = cleanText($_POST['message'] ?? '');
if ($message === '' || textLength($message) > 500 || strpos($message, "\0") !== false) {
    jsonResponse(['error' => 'Az üzenet 1–500 karakter hosszú lehet, és nem állhat csak szóközökből.'], 422);
}
try {
    $pdo = database();
    $query = $pdo->prepare('INSERT INTO messages (user_id, message, created_at) VALUES (?, ?, UTC_TIMESTAMP())');
    $query->execute([$_SESSION['user_id'], $message]);
    jsonResponse(['success' => true], 201);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    jsonResponse(['error' => 'Az üzenet nem ment el. Ellenőrizd a kapcsolatot, és próbáld újra.'], 503);
}
