<?php
session_start();
require_once '../Data/config.php';

$msgSuccess = false;
$msgError   = '';
$messages_recu = [];
$agents = [];

if (isset($_SESSION['agent_ncode'])) {

    // Suppression de compte
    if (isset($_POST['delete_account'])) {
        $db->prepare("DELETE FROM messages WHERE expediteur = ? OR receveur = ?")
           ->execute([$_SESSION['agent_ncode'], $_SESSION['agent_ncode']]);
        $db->prepare("DELETE FROM users WHERE ncode = ?")
           ->execute([$_SESSION['agent_ncode']]);
        session_unset();
        session_destroy();
        header('Location: interface.php');
        exit;
    }

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
    <title>ShadowComm — Spy Agency</title>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* ── RESET & VARIABLES ── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --blue:   #2563eb;
            --dark:   #07090d;
            --text:   #dde6f0;
            --muted:  #64748b;
            --border: rgba(255,255,255,0.07);
        }
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

        /* ── HERO ── */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background:
                radial-gradient(ellipse at 50% 50%, rgba(37,99,235,0.07) 0%, transparent 65%),
                linear-gradient(180deg, rgba(37,99,235,0.03) 0%, transparent 50%),
                var(--dark);
        }
        .hero-content {
            padding: 0 2rem;
            max-width: 680px;
            margin-top: 4rem;
        }

        .hero-tag {
            color: var(--muted);
            font-size: .85rem;
            letter-spacing: 1px;
            margin-top: 1.8rem;
            margin-bottom: 0;
        }

        .hero h1 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 5.8rem;
            font-weight: 700;
            line-height: .95;
            color: #fff;
            margin-bottom: 2rem;
            text-transform: uppercase;
        }

        .hero h1 span {
            color: var(--blue);
        }

        .hero-btns {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
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

        .btn-outline {
            color: #fff;
            padding: .82rem 2.1rem;
            border: 2px solid rgba(255, 255, 255, .55);
            border-radius: 30px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: all .3s;
            display: inline-block;
        }

        .btn-outline:hover {
            background: #fff;
            color: #000;
            border-color: #fff;
            transform: translateY(-2px);
        }

        /* ── FEATURES STRIP ── */
        .features {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            border-top: 1px solid var(--border);
        }

        .feat {
            padding: 2.4rem 2rem;
            border-right: 1px solid var(--border);
        }

        .feat:last-child {
            border-right: none;
        }

        .feat h3 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: .55rem;
        }

        .feat h3 em {
            color: var(--blue);
            font-style: normal;
            margin-right: .3rem;
        }

        .feat p {
            font-size: .78rem;
            color: var(--muted);
            line-height: 1.65;
        }

        /* ── CENTRE DE COMMUNICATION ── */
        .comm-section {
            padding: 5.5rem 4.5rem;
            border-top: 1px solid var(--border);
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

        /* ── ACCÈS VERROUILLÉ ── */
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

        /* ── BOUTONS NAV CONNECTÉ ── */
        .btn-nav-sm {
            padding: .5rem 1.2rem;
            font-size: .7rem;
        }

        .btn-danger {
            color: #fca5a5;
            padding: .5rem 1.2rem;
            border: 1px solid rgba(220, 38, 38, .5);
            border-radius: 30px;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: all .3s;
            display: inline-block;
            background: none;
            cursor: pointer;
            font-family: 'Open Sans', sans-serif;
        }

        .btn-danger:hover {
            background: rgba(220, 38, 38, .15);
            border-color: #dc2626;
            color: #fff;
        }

        .agent-badge {
            font-size: .72rem;
            color: var(--muted);
        }

        .agent-badge strong {
            color: var(--blue);
        }

        /* ── MODAL CONFIRMATION ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .75);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: #0d1117;
            border: 1px solid rgba(220, 38, 38, .4);
            border-radius: 12px;
            padding: 2.5rem;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }

        .modal-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .modal-box h3 {
            font-family: 'Rajdhani', sans-serif;
            color: #fff;
            font-size: 1.5rem;
            margin-bottom: .7rem;
        }

        .modal-box p {
            font-size: .8rem;
            color: var(--muted);
            line-height: 1.7;
            margin-bottom: 1.8rem;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .modal-highlight-danger {
            color: #fca5a5;
        }

        .modal-highlight-blue {
            color: var(--blue);
        }

        .btn-confirm-delete {
            background: #dc2626;
            color: #fff;
            border: none;
            border-radius: 30px;
            padding: .75rem 1.8rem;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: background .3s;
        }

        .btn-confirm-delete:hover {
            background: #b91c1c;
        }

        .btn-cancel {
            background: none;
            color: var(--muted);
            border: 1px solid var(--border);
            border-radius: 30px;
            padding: .75rem 1.8rem;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all .3s;
        }

        .btn-cancel:hover {
            color: #fff;
            border-color: rgba(255, 255, 255, .3);
        }

        .modal-form {
            margin: 0;
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
            .hero-content { padding: 0 2rem; }
            .hero h1      { font-size: 3.8rem; }
            .features     { grid-template-columns: repeat(2, 1fr); }
            .comm-section { padding: 3.5rem 2rem; }
            .comm-grid    { grid-template-columns: 1fr; }
            footer        { padding: 1.5rem 2rem; flex-direction: column; gap: .8rem; text-align: center; }
        }

        @media (max-width: 600px) {
            nav ul        { display: none; }
            .features     { grid-template-columns: 1fr; }
            .hero h1      { font-size: 2.8rem; }
            .comm-section { padding: 3rem 1.5rem; }
        }
    </style>
</head>

<body>

<nav>
    <a href="#home" class="logo">
        <div class="logo-icon">🔍</div>
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
            <li><button class="btn-danger" onclick="openModal()">Supprimer mon compte</button></li>
        <?php else: ?>
            <li><a href="login.php" class="btn-primary btn-nav-sm">Connexion</a></li>
        <?php endif; ?>
    </ul>
</nav>

<!-- ═══════════════════ MODAL SUPPRESSION ═══════════════════ -->
<?php if (isset($_SESSION['agent_ncode'])): ?>
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">⚠️</div>
        <h3>Supprimer mon compte</h3>
        <p>Cette action est <strong class="modal-highlight-danger">irréversible</strong>. Votre compte agent <strong class="modal-highlight-blue"><?= htmlspecialchars($_SESSION['agent_ncode']) ?></strong> et tous vos messages seront définitivement supprimés.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal()">Annuler</button>
            <form method="POST" action="interface.php" class="modal-form">
                <button type="submit" name="delete_account" class="btn-confirm-delete">Oui, supprimer</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ HERO ═══════════════════ -->
<section class="hero" id="home">
    <div class="hero-content">
        <h1>Shadow<br><span>Comm</span></h1>
        <div class="hero-btns">
            <?php if (isset($_SESSION['agent_ncode'])): ?>
                <a href="centredecom.php" class="btn-primary">Centre de Communication</a>
                <a href="listeagents.php" class="btn-outline">Voir les agents</a>
            <?php else: ?>
                <a href="register.php" class="btn-primary">Inscription</a>
                <a href="login.php" class="btn-outline">Connexion</a>
            <?php endif; ?>
        </div>
        <p class="hero-tag">Confidentialité et fiabilité sont nos points forts.</p>
    </div>
</section>

<!-- ═══════════════════ FEATURES STRIP ═══════════════════ -->
<section class="features" id="services">
    <div class="feat">
        <h3><em>01.</em> Fiabilité</h3>
        <p>Chaque mission est exécutée avec précision.</p>
    </div>
    <div class="feat">
        <h3><em>02.</em> Confidentialité</h3>
        <p>Nous ne conservons rien.</p>
    </div>
    <div class="feat">
        <h3><em>03.</em> Transparence</h3>
        <p>Nos tarifs sont transparents et adaptés à chaque niveau de mission.</p>
    </div>
    <div class="feat">
        <h3><em>04.</em> Efficacité</h3>
        <p>Nous menons chaque mission à son terme et fournissons un rapport complet.</p>
    </div>
</section>


<!-- ═══════════════════ FOOTER ═══════════════════ -->
<footer>
    <div class="footer-logo">ShadowComm</div>
    <div class="footer-copy">&copy; <?= date('Y') ?> ShadowComm — Agence d'Espionnage. Tous droits réservés.</div>
    <div class="footer-classified">⬛ Classified — Accès Restreint</div>
</footer>

<script>
function openModal()  { document.getElementById('deleteModal').classList.add('active'); }
function closeModal() { document.getElementById('deleteModal').classList.remove('active'); }
// Fermer en cliquant en dehors du modal
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('deleteModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
});
</script>
</body>
</html>

