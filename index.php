<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#121319">
    <meta name="csrf-token" content="<?= escape($_SESSION['csrf_token']) ?>">
    <meta name="user-id" content="<?= (int) $_SESSION['user_id'] ?>">
    <meta name="username" content="<?= escape($_SESSION['username']) ?>">
    <title>Általános társalgó · Pofázó 3000</title>
    <link rel="icon" href="logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body class="chat-page">
    <header class="topbar">
        <a class="brand" href="index.php"><img src="logo.svg" alt="" width="34" height="34">Pofázó <span>3000</span></a>
        <span class="top-tagline">A kommunikáció új dimenziója.</span>
        <div class="top-user"><span class="dot"></span><span><?= escape($_SESSION['username']) ?></span><span class="muted">/ online</span></div>
    </header>
    <div class="workspace">
        <button class="sidebar-overlay" id="sidebar-overlay" aria-label="Menü bezárása" hidden></button>
        <aside class="sidebar" id="sidebar" aria-label="Társalgó és felhasználók">
            <div class="workspace-title"><span class="workspace-icon">P.</span><div><strong>A mi kis közösségünk</strong><span>Itt mindig van miről beszélni.</span></div><span class="sparkle" aria-hidden="true">✧</span></div>
            <div class="section-label">TÁRSALGÓ <span>01</span></div>
            <a class="channel active" href="index.php" aria-current="page"><span class="hash">#</span> Általános társalgó <span class="channel-dot"></span></a>
            <div class="section-label people-label">A TÁRSASÁG <span id="user-count">—</span></div>
            <div class="user-list" id="user-list"><p class="muted small">A társaság betöltése…</p></div>
            <div class="sidebar-note"><span aria-hidden="true">✦</span><strong>A jó dumához te is kellesz.</strong><p>Ossz meg egy ötletet, kérdezz,<br>vagy csak köszönj be!</p></div>
            <div class="profile"><span class="avatar self-avatar" id="profile-avatar"></span><div class="profile-name"><strong><?= escape($_SESSION['username']) ?></strong><span><span class="dot"></span> Beszélgetésre kész</span></div><form action="logout.php" method="post"><input type="hidden" name="csrf_token" value="<?= escape($_SESSION['csrf_token']) ?>"><button class="icon-button" title="Kijelentkezés" aria-label="Kijelentkezés" type="submit">↪</button></form></div>
        </aside>
        <main class="chat-main">
            <header class="chat-header">
                <button class="icon-button menu-button" id="menu-button" aria-controls="sidebar" aria-expanded="false" aria-label="Oldalsáv megnyitása">☰</button>
                <span class="header-hash" aria-hidden="true">#</span><div class="chat-heading"><h1>Általános társalgó</h1><p>Nagy ötletek, apró kérdések, kötetlen duma.</p></div>
                <span class="online-badge"><span class="dot"></span><span id="online-count">—</span> online</span>
            </header>
            <div class="room-notice"><span aria-hidden="true">✧</span><span>Egy hely, ahol mindenki szóhoz jut. <strong>Örülünk, hogy itt vagy!</strong></span><span class="notice-label">JÓ FEJNEK LENNI ÉR</span></div>
            <section class="conversation" id="conversation" aria-label="Beszélgetés" tabindex="0">
                <div class="welcome"><div class="welcome-icon" aria-hidden="true">#</div><span class="eyebrow">ITT KEZDŐDIK A BESZÉLGETÉS</span><h2>Közös tér. Jó társaság.</h2><p>Üdv az Általános társalgóban!<br>Itt a gondolatokból beszélgetések lesznek.</p><span class="welcome-pill"><span class="dot"></span> A duma helye, 0–24.</span></div>
                <div id="messages" role="log" aria-label="Üzenetek" aria-live="polite" aria-relevant="additions"></div>
                <p class="empty-message" id="empty-message">A beszélgetés betöltése…</p>
            </section>
            <button id="new-messages" class="new-messages" hidden>Új üzenetek ↓</button>
            <div class="composer-area">
                <p class="chat-feedback" id="feedback" role="status" aria-live="polite"></p>
                <form class="composer" id="message-form">
                    <label class="sr-only" for="message">Üzenet az Általános társalgóba</label>
                    <textarea id="message" name="message" rows="2" placeholder="Na, mi a helyzet? Írj valamit…" required></textarea>
                    <div class="composer-bottom"><span class="composer-room"><span aria-hidden="true">#</span> Általános társalgó</span><span class="character-count" id="character-count">0 / 500</span><button class="send-button" id="send-button" type="submit" disabled>Küldés <span aria-hidden="true">↗</span></button></div>
                </form>
                <div class="composer-hints"><span><kbd>Enter</kbd> küldés <span class="hint-divider">·</span> <kbd>Shift + Enter</kbd> új sor</span><span id="connection-status" class="connection-status" role="status"><span class="dot"></span><span id="connection-text">Kapcsolódás…</span></span></div>
            </div>
        </main>
    </div>
    <noscript><div class="noscript">Az automatikus chathez engedélyezd a JavaScriptet a böngészőben.</div></noscript>
</body>
</html>
