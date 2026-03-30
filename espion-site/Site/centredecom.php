<?php
session_start();
require_once '../Data/config.php';

/* ── CHIFFREMENT CÉSAR (décalage fixe) ── */
define('CAESAR_SHIFT', 7);

function caesar_encrypt(string $text): string {
    $shift  = CAESAR_SHIFT;
    $result = '';
    foreach (str_split($text) as $char) {
        if (ctype_alpha($char)) {
            $base   = ctype_upper($char) ? ord('A') : ord('a');
            $result .= chr((ord($char) - $base + $shift) % 26 + $base);
        } else {
            $result .= $char;
        }
    }
    return $result;
}

function caesar_decrypt(string $text): string {
    $shift  = 26 - (CAESAR_SHIFT % 26);
    $result = '';
    foreach (str_split($text) as $char) {
        if (ctype_alpha($char)) {
            $base   = ctype_upper($char) ? ord('A') : ord('a');
            $result .= chr((ord($char) - $base + $shift) % 26 + $base);
        } else {
            $result .= $char;
        }
    }
    return $result;
}

$msgSuccess = false;
$msgError   = '';

/* ── ENVOI SUR LE CANAL ── */
if (isset($_SESSION['agent_ncode']) && isset($_POST['send_message'])) {
    $contenu = trim($_POST['contenu'] ?? '');
    if (!$contenu) {
        $msgError = 'Le message ne peut pas être vide.';
    } else {
        $encrypted = caesar_encrypt($contenu);
        $ins = $db->prepare("INSERT INTO canal (expediteur, contenu) VALUES (?, ?)");
        $ins->execute([$_SESSION['agent_ncode'], $encrypted]);
        $msgSuccess = true;
    }
}

/* ── LECTURE DU CANAL (50 derniers messages) ── */
$messages = $db->query(
    "SELECT expediteur, contenu, created_at FROM canal ORDER BY created_at DESC LIMIT 50"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShadowComm — Centre de Communication</title>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* ── RESET ── */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ── VARIABLES ── */
        :root {
            --blue:   #2563eb;
            --dark:   #07090d;
            --text:   #dde6f0;
            --muted:  #64748b;
            --border: rgba(255, 255, 255, 0.07);
        }

        /* ── BASE ── */
        body {
            font-family: 'Open Sans', sans-serif;
            background: var(--dark);
            color: var(--text);
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
        }

        /* ── NAVIGATION ── */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.2rem 4.5rem;
            background: rgba(7, 9, 13, 0.92);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--border);
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: .8rem;
        }

        .logo-icon {
            width: 44px;
            height: 44px;
            border: 2px solid var(--blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--blue);
        }

        .logo-name {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 2px;
            color: #fff;
            display: block;
            line-height: 1.1;
        }

        .logo-sub {
            font-size: .55rem;
            color: var(--muted);
            letter-spacing: 3px;
            text-transform: uppercase;
            display: block;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 2.4rem;
            align-items: center;
        }

        nav ul a {
            color: var(--muted);
            font-size: .8rem;
            letter-spacing: .5px;
            transition: color .3s;
        }

        nav ul a:hover {
            color: #fff;
        }

        .btn-primary {
            background: var(--blue);
            color: #fff;
            padding: .85rem 2.1rem;
            border-radius: 30px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: background .3s, transform .2s;
            display: inline-block;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        /* ── UTILITAIRES NAV ── */
        .btn-nav-sm {
            padding: .5rem 1.2rem;
            font-size: .7rem;
        }

        .agent-badge {
            font-size: .72rem;
            color: var(--muted);
        }

        .agent-badge strong {
            color: var(--blue);
        }

        /* ── PAGE ── */
        .page-wrapper {
            padding-top: 5rem;
            min-height: calc(100vh - 72px);
        }

        .comm-section {
            padding: 5.5rem 4.5rem;
        }

        .comm-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .kicker {
            font-size: .68rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--blue);
            margin-bottom: .8rem;
        }

        .sec-title {
            font-family: 'Rajdhani', sans-serif;
            font-size: 2.7rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1rem;
        }

        .divider {
            width: 48px;
            height: 2px;
            background: var(--blue);
            margin: 1rem auto 0;
        }

        /* ── GRILLE DE COM ── */
        .comm-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            max-width: 1100px;
            margin: 0 auto;
        }

        .comm-box {
            background: rgba(255, 255, 255, .02);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.8rem;
        }

        .comm-box-title {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1.2rem;
            letter-spacing: 1px;
            padding-bottom: .7rem;
            border-bottom: 1px solid var(--border);
        }

        .msg-box-info {
            float: right;
            font-size: .65rem;
            color: var(--muted);
            font-family: 'Open Sans', sans-serif;
            font-weight: 400;
        }

        .msg-box-info strong {
            color: var(--blue);
        }

        /* ── FORMULAIRE ── */
        .comm-form-group {
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
        }

        .comm-form-group label {
            font-size: .65rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: .35rem;
        }

        .comm-form-group select,
        .comm-form-group textarea {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 6px;
            padding: .7rem 1rem;
            color: #fff;
            font-family: 'Open Sans', sans-serif;
            font-size: .82rem;
            transition: border-color .3s;
            width: 100%;
            resize: vertical;
        }

        .comm-form-group select:focus,
        .comm-form-group textarea:focus {
            outline: none;
            border-color: var(--blue);
        }

        .comm-form-group select option {
            background: #0d1117;
        }

        .btn-comm {
            width: 100%;
            background: var(--blue);
            color: #fff;
            border: none;
            border-radius: 30px;
            padding: .85rem;
            font-family: 'Open Sans', sans-serif;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: background .3s, transform .2s;
            margin-top: .3rem;
        }

        .btn-comm:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        /* ── ALERTES ── */
        .comm-success {
            background: rgba(34, 197, 94, .1);
            border: 1px solid rgba(34, 197, 94, .35);
            border-radius: 6px;
            padding: .7rem 1rem;
            font-size: .78rem;
            color: #4ade80;
            margin-bottom: 1rem;
        }

        .comm-error {
            background: rgba(220, 38, 38, .1);
            border: 1px solid rgba(220, 38, 38, .4);
            border-radius: 6px;
            padding: .7rem 1rem;
            font-size: .78rem;
            color: #fca5a5;
            margin-bottom: 1rem;
        }

        /* ── LISTE DE MESSAGES ── */
        .msg-list {
            display: flex;
            flex-direction: column;
            gap: .75rem;
            max-height: 380px;
            overflow-y: auto;
            padding-right: .3rem;
        }

        .msg-list::-webkit-scrollbar       { width: 4px; }
        .msg-list::-webkit-scrollbar-track { background: rgba(255, 255, 255, .03); }
        .msg-list::-webkit-scrollbar-thumb { background: var(--blue); border-radius: 4px; }

        .msg-item {
            background: rgba(255, 255, 255, .03);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: .9rem 1.1rem;
            transition: border-color .3s;
        }

        .msg-item:hover {
            border-color: rgba(37, 99, 235, .45);
        }

        .msg-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .45rem;
        }

        .msg-from {
            font-size: .68rem;
            color: var(--blue);
            font-weight: 600;
            letter-spacing: .5px;
        }

        .msg-date {
            font-size: .6rem;
            color: var(--muted);
        }

        .msg-body {
            font-size: .78rem;
            color: var(--text);
            line-height: 1.6;
        }

        .no-msg {
            font-size: .78rem;
            color: var(--muted);
            text-align: center;
            padding: 2rem 0;
            font-style: italic;
        }

        /* ── ACCES VERROUILLE ── */
        .comm-locked {
            text-align: center;
            padding: 3rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            max-width: 420px;
            margin: 0 auto;
        }

        .lock-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .comm-locked h3 {
            font-family: 'Rajdhani', sans-serif;
            color: #fff;
            font-size: 1.5rem;
            margin-bottom: .5rem;
        }

        .comm-locked p {
            font-size: .8rem;
            color: var(--muted);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        /* ── FOOTER ── */
        footer {
            border-top: 1px solid var(--border);
            padding: 2rem 4.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-logo {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 2px;
        }

        .footer-copy {
            font-size: .72rem;
            color: var(--muted);
        }

        .footer-classified {
            font-size: .6rem;
            color: rgba(37, 99, 235, .5);
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        /* ── CANAL PUBLIC ── */
        .canal-layout {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            align-items: start;
        }

        .canal-feed {
            display: flex;
            flex-direction: column;
            gap: .75rem;
            max-height: 600px;
            overflow-y: auto;
            padding-right: .4rem;
        }

        .canal-feed::-webkit-scrollbar       { width: 4px; }
        .canal-feed::-webkit-scrollbar-track { background: rgba(255, 255, 255, .03); }
        .canal-feed::-webkit-scrollbar-thumb { background: var(--blue); border-radius: 4px; }

        .msg-encrypted {
            font-family: 'Courier New', Courier, monospace;
            font-size: .78rem;
            color: #4ade80;
            letter-spacing: .5px;
            line-height: 1.6;
            word-break: break-all;
        }

        .msg-plain {
            font-size: .78rem;
            color: var(--text);
            line-height: 1.6;
        }

        .hidden {
            display: none;
        }

        .btn-decrypt {
            margin-top: .5rem;
            background: transparent;
            border: 1px solid rgba(37, 99, 235, .4);
            color: var(--blue);
            font-family: 'Open Sans', sans-serif;
            font-size: .62rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: .3rem .8rem;
            border-radius: 20px;
            cursor: pointer;
            transition: all .3s;
        }

        .btn-decrypt:hover {
            background: rgba(37, 99, 235, .15);
            border-color: var(--blue);
        }

        .cipher-tag {
            display: inline-block;
            font-size: .55rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, .3);
            border-radius: 10px;
            padding: .1rem .5rem;
            margin-left: .5rem;
            vertical-align: middle;
        }

        .canal-empty {
            font-size: .78rem;
            color: var(--muted);
            text-align: center;
            padding: 3rem 0;
            font-style: italic;
        }

        .shift-info {
            font-size: .65rem;
            color: var(--muted);
            text-align: center;
            margin-top: 1rem;
            letter-spacing: .5px;
        }

        .shift-info strong {
            color: var(--blue);
        }

        .comm-locked-inline {
            text-align: center;
            padding: 2rem 1rem;
        }

        .comm-locked-inline p {
            font-size: .8rem;
            color: var(--muted);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 1024px) {
            nav             { padding: 1rem 2rem; }
            .comm-section   { padding: 3.5rem 2rem; }
            .canal-layout   { grid-template-columns: 1fr; }
            footer          { padding: 1.5rem 2rem; flex-direction: column; gap: .8rem; text-align: center; }
        }

        @media (max-width: 600px) {
            nav ul          { display: none; }
            .comm-section   { padding: 3rem 1.5rem; }
        }
    </style>
</head>

<body>

<nav>
    <a href="interface.php" class="logo">
        <div class="logo-icon">&#128269;</div>
        <div>
            <span class="logo-name">ShadowComm</span>
            <span class="logo-sub">Spy Agency</span>
        </div>
    </a>
    <ul>
        <li><a href="listeagents.php">01. Agents</a></li>
        <li><a href="centredecom.php">02. Centre de Communication</a></li>
        <?php if (isset($_SESSION['agent_ncode'])): ?>
        <li class="agent-badge">Agent : <strong><?= htmlspecialchars($_SESSION['agent_ncode']) ?></strong></li>
        <li><a href="logout.php" class="btn-primary btn-nav-sm">Déconnexion</a></li>
        <?php else: ?>
        <li><a href="login.php" class="btn-primary btn-nav-sm">Connexion</a></li>
        <?php endif; ?>
    </ul>
</nav>

<div class="page-wrapper">
<section class="comm-section">
    <div class="comm-header">
        <p class="kicker">Canal Public — Chiffrement César +<?= CAESAR_SHIFT ?></p>
        <h2 class="sec-title">Centre de Communication</h2>
        <div class="divider"></div>
    </div>

    <div class="canal-layout">

        <!-- FORMULAIRE D'ENVOI -->
        <div class="comm-box">
            <p class="comm-box-title">📡 Nouvelle Transmission</p>

            <?php if (!isset($_SESSION['agent_ncode'])): ?>
            <div class="comm-locked-inline">
                <div class="lock-icon">🔒</div>
                <p>Connectez-vous pour émettre sur le canal ShadowComm.</p>
                <a href="login.php" class="btn-primary">Se connecter</a>
            </div>
            <?php else: ?>

            <?php if ($msgSuccess): ?>
            <div class="comm-success">✓ Transmission chiffrée envoyée sur le canal.</div>
            <?php elseif ($msgError): ?>
            <div class="comm-error">⚠ <?= htmlspecialchars($msgError) ?></div>
            <?php endif; ?>

            <form method="POST" action="centredecom.php">
                <div class="comm-form-group">
                    <label>Message en clair</label>
                    <textarea name="contenu" rows="7"
                        placeholder="Rédigez votre message... Il sera chiffré automatiquement avant envoi."
                        required><?= htmlspecialchars($_POST['contenu'] ?? '') ?></textarea>
                </div>
                <button type="submit" name="send_message" class="btn-comm">
                    🔐 Chiffrer &amp; Émettre
                </button>
            </form>
            <p class="shift-info">Algorithme : César &mdash; décalage <strong>+<?= CAESAR_SHIFT ?></strong></p>

            <?php endif; ?>
        </div>

        <!-- CANAL PUBLIC -->
        <div class="comm-box">
            <p class="comm-box-title">
                &#128251; Canal ShadowComm
                <span class="cipher-tag">César +<?= CAESAR_SHIFT ?></span>
            </p>

            <?php if (empty($messages)): ?>
            <p class="canal-empty">Aucune transmission sur le canal.</p>
            <?php else: ?>
            <div class="canal-feed">
                <?php foreach ($messages as $i => $msg): ?>
                <?php $pair = 'msg-' . $i; $plain = caesar_decrypt($msg['contenu']); ?>
                <div class="msg-item">
                    <div class="msg-meta">
                        <span class="msg-from"><?= htmlspecialchars($msg['expediteur']) ?></span>
                        <span class="msg-date"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></span>
                    </div>
                    <p class="msg-encrypted" data-pair="<?= $pair ?>">
                        <?= htmlspecialchars($msg['contenu']) ?>
                    </p>
                    <p class="msg-plain hidden" data-pair="<?= $pair ?>">
                        <?= htmlspecialchars($plain) ?>
                    </p>
                    <button class="btn-decrypt" onclick="toggleDecrypt('<?= $pair ?>', this)">🔓 Déchiffrer</button>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>
</section>
</div>

<footer>
    <div class="footer-logo">ShadowComm</div>
    <div class="footer-copy">&copy; <?= date('Y') ?> ShadowComm — Agence d'Espionnage. Tous droits réservés.</div>
    <div class="footer-classified">&#11035; Classified — Accès Restreint</div>
</footer>


<script>
    function toggleDecrypt(pair, btn) {
        document.querySelectorAll('[data-pair="' + pair + '"]').forEach(function(el) {
            el.classList.toggle('hidden');
        });
        if (btn.textContent.indexOf('Dé') !== -1) {
            btn.textContent = '🔒 Rechiffrer';
        } else {
            btn.textContent = '🔓 Déchiffrer';
        }
    }
</script>

</body>
</html>