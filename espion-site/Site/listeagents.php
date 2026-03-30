<?php
session_start();
require_once '../Data/config.php';

// Filtres GET
$filtreGrade     = $_GET['grade']     ?? '';
$filtreSpec      = $_GET['specialite'] ?? '';
$recherche       = trim($_GET['q']    ?? '');

// Construction de la requête dynamique
$where  = [];
$params = [];

if ($filtreGrade && in_array($filtreGrade, ['expert','confirme','novice'])) {
    $where[]  = "grade = ?";
    $params[] = $filtreGrade;
}
if ($filtreSpec && in_array($filtreSpec, ['agent_double','agent_informateur','cyber_espion'])) {
    $where[]  = "specialite = ?";
    $params[] = $filtreSpec;
}
if ($recherche) {
    $where[]  = "(ncode LIKE ? OR nom LIKE ? OR prenom LIKE ?)";
    $like     = '%' . $recherche . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql = "SELECT ncode, nom, prenom, age, specialite, grade, created_at FROM users";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY grade DESC, ncode ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$agents = $stmt->fetchAll();
$total  = count($agents);

// Labels lisibles
$gradeLabel = ['expert' => 'Expert', 'confirme' => 'Confirmé', 'novice' => 'Novice'];
$specLabel  = ['agent_double' => 'Agent Double', 'agent_informateur' => 'Informateur', 'cyber_espion' => 'Cyber-Espion'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShadowComm — Liste des Agents</title>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* ── RESET ── */
        * {
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            text-decoration: none;
        }

        /* ── NAVIGATION ── */
        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.3rem 4.5rem;
            border-bottom: 1px solid var(--border);
            background: rgba(7, 9, 13, 0.97);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: .8rem;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            border: 2px solid var(--blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .logo-name {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.45rem;
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

        .nav-right {
            display: flex;
            align-items: center;
            gap: 1.8rem;
        }

        .nav-link {
            color: var(--muted);
            font-size: .78rem;
            letter-spacing: .5px;
            transition: color .3s;
        }

        .nav-link:hover {
            color: #fff;
        }

        .nav-agent {
            color: var(--blue);
            font-size: .78rem;
            letter-spacing: .5px;
        }

        .btn-nav {
            background: var(--blue);
            color: #fff;
            padding: .45rem 1.2rem;
            border-radius: 30px;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: background .3s;
        }

        .btn-nav:hover {
            background: #1d4ed8;
        }

        /* ── EN-TÊTE DE PAGE ── */
        .page-header {
            padding: 3rem 4.5rem 2rem;
            border-bottom: 1px solid var(--border);
        }

        .page-kicker {
            font-size: .65rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--blue);
            margin-bottom: .5rem;
        }

        .page-title {
            font-family: 'Rajdhani', sans-serif;
            font-size: 2.8rem;
            font-weight: 700;
            color: #fff;
            line-height: 1;
        }

        .page-sub {
            font-size: .8rem;
            color: var(--muted);
            margin-top: .5rem;
        }

        .header-line {
            width: 40px;
            height: 2px;
            background: var(--blue);
            margin: .8rem 0 0;
        }

        /* ── BARRE DE FILTRES ── */
        .filters-bar {
            padding: 1.5rem 4.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-input {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 6px;
            padding: .6rem 1rem;
            color: #fff;
            font-family: 'Open Sans', sans-serif;
            font-size: .8rem;
            transition: border-color .3s;
            min-width: 220px;
        }

        .filter-input:focus {
            outline: none;
            border-color: var(--blue);
        }

        .filter-input::placeholder {
            color: var(--muted);
        }

        .filter-select {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 6px;
            padding: .6rem 1rem;
            color: #fff;
            font-family: 'Open Sans', sans-serif;
            font-size: .8rem;
            transition: border-color .3s;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--blue);
        }

        .filter-select option {
            background: #0d1117;
        }

        .btn-filter {
            background: var(--blue);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: .6rem 1.4rem;
            font-family: 'Open Sans', sans-serif;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background .3s;
        }

        .btn-filter:hover {
            background: #1d4ed8;
        }

        .btn-reset {
            background: transparent;
            color: var(--muted);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 6px;
            padding: .6rem 1.1rem;
            font-family: 'Open Sans', sans-serif;
            font-size: .75rem;
            cursor: pointer;
            transition: all .3s;
        }

        .btn-reset:hover {
            color: #fff;
            border-color: rgba(255, 255, 255, .3);
        }

        .result-count {
            margin-left: auto;
            font-size: .72rem;
            color: var(--muted);
        }

        .result-count strong {
            color: var(--blue);
        }

        /* ── TABLEAU ── */
        main {
            flex: 1;
            padding: 2rem 4.5rem 3rem;
        }

        .agents-table {
            width: 100%;
            border-collapse: collapse;
        }

        .agents-table thead th {
            font-size: .62rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            padding: .8rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .agents-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .2s;
        }

        .agents-table tbody tr:hover {
            background: rgba(255, 255, 255, .025);
        }

        .agents-table tbody td {
            padding: 1rem;
            font-size: .82rem;
            color: var(--text);
            vertical-align: middle;
        }

        .td-ncode {
            font-family: 'Rajdhani', sans-serif;
            font-size: .95rem;
            font-weight: 700;
            color: var(--blue);
        }

        .td-name {
            font-weight: 600;
            color: #fff;
        }

        .td-name small {
            display: block;
            font-size: .7rem;
            color: var(--muted);
            font-weight: 400;
        }

        .td-muted {
            color: var(--muted);
            font-size: .75rem;
        }

        .text-na {
            color: var(--muted);
            font-style: italic;
        }

        /* ── BADGES ── */
        .badge {
            display: inline-block;
            padding: .25rem .75rem;
            border-radius: 20px;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .badge-expert   { background: rgba(234, 179, 8, .12);   color: #fbbf24; border: 1px solid rgba(234, 179, 8, .3); }
        .badge-confirme { background: rgba(37, 99, 235, .15);   color: #60a5fa; border: 1px solid rgba(37, 99, 235, .35); }
        .badge-novice   { background: rgba(100, 116, 139, .12); color: #94a3b8; border: 1px solid rgba(100, 116, 139, .3); }
        .badge-double   { background: rgba(239, 68, 68, .1);    color: #f87171; border: 1px solid rgba(239, 68, 68, .25); }
        .badge-info     { background: rgba(168, 85, 247, .1);   color: #c084fc; border: 1px solid rgba(168, 85, 247, .25); }
        .badge-cyber    { background: rgba(20, 184, 166, .1);   color: #2dd4bf; border: 1px solid rgba(20, 184, 166, .25); }

        /* ── ETAT VIDE ── */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--muted);
        }

        .empty-state .empty-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .empty-state p {
            font-size: .85rem;
        }

        /* ── FOOTER ── */
        footer {
            border-top: 1px solid var(--border);
            padding: 1.5rem 4.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-logo {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 2px;
        }

        .footer-classified {
            font-size: .6rem;
            color: rgba(37, 99, 235, .5);
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 1024px) {
            nav, .page-header, .filters-bar, main, footer { padding-left: 2rem; padding-right: 2rem; }
            .filters-bar  { flex-direction: column; align-items: stretch; }
            .result-count { margin-left: 0; }
        }

        @media (max-width: 700px) {
            .agents-table thead          { display: none; }
            .agents-table tbody tr       { display: block; padding: 1rem; margin-bottom: .5rem; border: 1px solid var(--border); border-radius: 8px; }
            .agents-table tbody td       { display: block; padding: .25rem 0; border: none; }
            .agents-table tbody td::before {
                content: attr(data-label) ' : ';
                font-size: .6rem;
                letter-spacing: 1.5px;
                text-transform: uppercase;
                color: var(--muted);
                margin-right: .3rem;
            }
        }
    </style>
</head>
<body>


<nav>
    <a href="interface.php" class="logo">
        <div class="logo-icon">🔍</div>
        <div>
            <span class="logo-name">ShadowComm</span>
            <span class="logo-sub">Spy Agency</span>
        </div>
    </a>
    <div class="nav-right">
        <a href="interface.php" class="nav-link">← Accueil</a>
        <?php if (isset($_SESSION['agent_ncode'])): ?>
            <span class="nav-agent">👤 <?= htmlspecialchars($_SESSION['agent_ncode']) ?></span>
        <?php else: ?>
            <a href="login.php" class="btn-nav">Connexion</a>
        <?php endif; ?>
    </div>
</nav>

<!-- EN-TÊTE DE PAGE -->
<div class="page-header">
    <p class="page-kicker">Fichiers Classifiés — Base Opérationnelle</p>
    <h1 class="page-title">Registre des Agents</h1>
    <p class="page-sub">Tous les agents enregistrés dans le système ShadowComm.</p>
    <div class="header-line"></div>
</div>

<!-- BARRE DE FILTRES -->
<form class="filters-bar" method="GET" action="listeagents.php">
    <input
        class="filter-input"
        type="text"
        name="q"
        placeholder="🔎  Rechercher par code agent, nom ou prénom…"
        value="<?= htmlspecialchars($recherche) ?>">

    <select class="filter-select" name="grade">
        <option value="">Tous les grades</option>
        <option value="expert"   <?= $filtreGrade === 'expert'   ? 'selected' : '' ?>>Expert</option>
        <option value="confirme" <?= $filtreGrade === 'confirme' ? 'selected' : '' ?>>Confirmé</option>
        <option value="novice"   <?= $filtreGrade === 'novice'   ? 'selected' : '' ?>>Novice</option>
    </select>

    <select class="filter-select" name="specialite">
        <option value="">Toutes les spécialités</option>
        <option value="agent_double"      <?= $filtreSpec === 'agent_double'      ? 'selected' : '' ?>>Agent Double</option>
        <option value="agent_informateur" <?= $filtreSpec === 'agent_informateur' ? 'selected' : '' ?>>Informateur</option>
        <option value="cyber_espion"      <?= $filtreSpec === 'cyber_espion'      ? 'selected' : '' ?>>Cyber-Espion</option>
    </select>

    <button type="submit" class="btn-filter">Filtrer</button>
    <?php if ($filtreGrade || $filtreSpec || $recherche): ?>
    <a href="listeagents.php" class="btn-reset">✕ Réinitialiser</a>
    <?php endif; ?>

    <div class="result-count"><strong><?= $total ?></strong> agent<?= $total > 1 ? 's' : '' ?> trouvé<?= $total > 1 ? 's' : '' ?></div>
</form>

<!-- TABLEAU DES AGENTS -->
<main>
    <?php if (empty($agents)): ?>
    <div class="empty-state">
        <div class="empty-icon">🕵️</div>
        <p>Aucun agent ne correspond aux critères de recherche.</p>
    </div>
    <?php else: ?>
    <table class="agents-table">
        <thead>
            <tr>
                <th>Code Agent</th>
                <th>Identité</th>
                <th>Âge</th>
                <th>Spécialité</th>
                <th>Grade</th>
                <th>Enregistré le</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($agents as $a):
            $specKey = match($a['specialite']) {
                'agent_double'      => 'double',
                'agent_informateur' => 'info',
                'cyber_espion'      => 'cyber',
                default             => 'info'
            };
        ?>
            <tr>
                <td data-label="Code Agent" class="td-ncode"><?= htmlspecialchars($a['ncode']) ?></td>
                <td data-label="Identité" class="td-name">
                    <?php if ($a['nom'] || $a['prenom']): ?>
                        <?= htmlspecialchars(trim($a['prenom'] . ' ' . $a['nom'])) ?>
                        <small><?= htmlspecialchars($a['ncode']) ?></small>
                    <?php else: ?>
                        <span class="text-na">Non renseigné</span>
                    <?php endif; ?>
                </td>
                <td data-label="Âge"><?= $a['age'] ? htmlspecialchars($a['age']) . ' ans' : '<span class="td-muted">—</span>' ?></td>
                <td data-label="Spécialité">
                    <span class="badge badge-<?= $specKey ?>">
                        <?= htmlspecialchars($specLabel[$a['specialite']] ?? $a['specialite']) ?>
                    </span>
                </td>
                <td data-label="Grade">
                    <span class="badge badge-<?= htmlspecialchars($a['grade']) ?>">
                        <?= htmlspecialchars($gradeLabel[$a['grade']] ?? $a['grade']) ?>
                    </span>
                </td>
                <td data-label="Enregistré le" class="td-muted">
                    <?= $a['created_at'] ? date('d/m/Y', strtotime($a['created_at'])) : '—' ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</main>

<footer>
    <div class="footer-logo">ShadowComm</div>
    <div class="footer-classified">⬛ Classified — Accès Restreint</div>
</footer>

</body>
</html>
