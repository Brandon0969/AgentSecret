<?php
session_start();
require_once '../Data/config.php';

$msgSuccess = false;
$msgError   = '';
$messages_recu = [];
$agents = [];

if (isset($_SESSION['agent_ncode'])) {
    $stmt = $db->prepare("SELECT ncode FROM users WHERE ncode != ? ORDER BY ncode");
    $stmt->execute([$_SESSION['agent_ncode']]);
    $agents = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (isset($_POST['send_message'])) {
        $receveur = trim($_POST['receveur'] ?? '');
        $contenu  = trim($_POST['contenu']  ?? '');
        if (!$receveur || !$contenu) {
            $msgError = 'Destinataire et message requis.';
        } else {
            $ins = $db->prepare("INSERT INTO messages (expediteur, receveur, contenu) VALUES (?, ?, ?)");
            $ins->execute([$_SESSION['agent_ncode'], $receveur, $contenu]);
            $msgSuccess = true;
        }
    }

    $m = $db->prepare("SELECT expediteur, contenu, created_at FROM messages WHERE receveur = ? ORDER BY created_at DESC LIMIT 10");
    $m->execute([$_SESSION['agent_ncode']]);
    $messages_recu = $m->fetchAll();
}
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

        /* ── RESPONSIVE ── */
        @media (max-width: 1024px) {
            nav           { padding: 1rem 2rem; }
            .comm-section { padding: 3.5rem 2rem; }
            .comm-grid    { grid-template-columns: 1fr; }
            footer        { padding: 1.5rem 2rem; flex-direction: column; gap: .8rem; text-align: center; }
        }

        @media (max-width: 600px) {
            nav ul        { display: none; }
            .comm-section { padding: 3rem 1.5rem; }
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
        <p class="kicker">Transmission Securisee - Chiffree</p>
        <h2 class="sec-title">Centre de Communication</h2>
        <div class="divider"></div>
    </div>

    <?php if (!isset($_SESSION['agent_ncode'])): ?>
    <div class="comm-locked">
        <div class="lock-icon">&#128274;</div>
        <h3>Acces Restreint</h3>
        <p>Vous devez etre authentifie pour acceder au centre de communication des agents.</p>
        <a href="login.php" class="btn-primary">Se connecter</a>
    </div>

    <?php else: ?>
    <div class="comm-grid">

        <!-- COMPOSER UN MESSAGE -->
        <div class="comm-box">
            <p class="comm-box-title">� Nouvelle Transmission</p>

            <?php if ($msgSuccess): ?>
            <div class="comm-success">✓ Message transmis avec succès.</div>
            <?php elseif ($msgError): ?>
            <div class="comm-error">⚠ <?= htmlspecialchars($msgError) ?></div>
            <?php endif; ?>

            <form method="POST" action="centredecom.php">
                <div class="comm-form-group">
                    <label>Destinataire</label>
                    <select name="receveur" required>
                        <option value="">— Sélectionner un agent —</option>
                        <?php foreach ($agents as $ag): ?>
                        <option value="<?= htmlspecialchars($ag) ?>"
                            <?= (($_POST['receveur'] ?? '') === $ag) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ag) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="comm-form-group">
                    <label>Message chiffré</label>
                    <textarea name="contenu" rows="5" placeholder="Rédigez votre transmission..." required><?= htmlspecialchars($_POST['contenu'] ?? '') ?></textarea>
                </div>
                <button type="submit" name="send_message" class="btn-comm">Envoyer la transmission</button>
            </form>
        </div>

        <!-- MESSAGES REÇUS -->
        <div class="comm-box">
            <p class="comm-box-title">📥 Messages Reçus
                <span class="msg-box-info">Connecté : <strong><?= htmlspecialchars($_SESSION['agent_ncode']) ?></strong></span>
            </p>

            <?php if (empty($messages_recu)): ?>
            <p class="no-msg">Aucune transmission reçue.</p>
            <?php else: ?>
            <div class="msg-list">
                <?php foreach ($messages_recu as $msg): ?>
                <div class="msg-item">
                    <div class="msg-meta">
                        <span class="msg-from">De : <?= htmlspecialchars($msg['expediteur']) ?></span>
                        <span class="msg-date"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></span>
                    </div>
                    <p class="msg-body"><?= htmlspecialchars($msg['contenu']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>
    <?php endif; ?>
</section>
</div>

<footer>
    <div class="footer-logo">ShadowComm</div>
    <div class="footer-copy">&copy; <?= date('Y') ?> ShadowComm — Agence d'Espionnage. Tous droits reservés.</div>
    <div class="footer-classified">&#11035; Classified — Acces Restreint</div>
</footer>

</body>
</html>