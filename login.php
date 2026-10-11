<?php
require 'config.php';
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanText($_POST['username'] ?? '');
    if (!validToken($_POST['csrf_token'] ?? null)) {
        $error = 'A munkamenet lejárt. Frissítsd az oldalt, és próbáld újra.';
    } elseif (textLength($username) < 2 || textLength($username) > 50 || preg_match('/[\x00-\x1F\x7F]/u', $username)) {
        $error = 'Válassz egy 2–50 karakteres nevet, sortörés nélkül.';
    } else {
        try {
            $pdo = database();
            // Minden belépés külön vendégprofilt kap: más nevét nem lehet átvenni.
            $query = $pdo->prepare('INSERT INTO users (username, last_seen) VALUES (?, UTC_TIMESTAMP())');
            $query->execute([$username]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: index.php');
            exit;
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $error = 'Az adatbázis nem elérhető. Indítsd el a MySQL-t, és importáld a database.sql fájlt.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#121319">
    <title>Belépés · Pofázó 3000</title>
    <link rel="icon" href="logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <header class="login-top"><a class="brand" href="login.php"><img src="logo.svg" alt="" width="38" height="38">Pofázó <span>3000</span></a><span class="small muted">Kevesebb csend. Több kapcsolat.</span></header>
    <main class="login-layout">
        <section class="login-intro">
            <span class="eyebrow"><span class="dot"></span> A KOMMUNIKÁCIÓS KÖZPONT ÜZEMKÉSZ</span>
            <h1>A jó beszélgetés<br>egy <span>szia</span> szóval<br>kezdődik.</h1>
            <p>Egy közös tér a nagy ötleteknek, a kis kérdéseknek<br class="desktop-break"> és mindennek, amit el kell pofázni.</p>
            <div class="intro-note"><span class="mini-icon">#</span><div><strong>Általános társalgó</strong><span>Mindenki egy helyen. Minden szó számít.</span></div></div>
            <div class="orbit" aria-hidden="true"><span>helló.</span><span>szia!</span><span>👋</span></div>
        </section>
        <section class="login-card">
            <span class="card-symbol" aria-hidden="true">↗</span>
            <h2>Van mondanivalód?</h2>
            <p>Akkor jó helyen jársz. Foglalj helyet a társalgóban!</p>
            <form method="post" action="login.php">
                <input type="hidden" name="csrf_token" value="<?= escape($_SESSION['csrf_token']) ?>">
                <label for="username">Hogy szólíthatunk?</label>
                <input id="username" name="username" type="text" minlength="2" maxlength="50" required autocomplete="nickname" placeholder="A felhasználóneved" value="<?= escape($username) ?>" autofocus>
                <span class="field-help">2–50 karakter. A beceneved is tökéletes.</span>
                <?php if ($error): ?><p class="form-error" role="alert"><?= escape($error) ?></p><?php endif; ?>
                <button class="primary-button" type="submit">Irány a társalgó <span aria-hidden="true">→</span></button>
            </form>
            <div class="login-footnote"><span aria-hidden="true">◇</span> Nincs jelszó. Nincs regisztráció. Csak a duma.</div>
        </section>
    </main>
    <footer class="login-footer"><span>Pofázó 3000 © <?= date('Y') ?></span><span>Embereknek készült. Beszélgetésre hangolva.</span><span class="version">v1.0 <span class="dot"></span></span></footer>
</body>
</html>
