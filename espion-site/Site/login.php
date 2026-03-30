<?php
session_start();
require_once '../Data/config.php';

// Déjà connecté → redirection
if (isset($_SESSION['agent_ncode'])) {
    header('Location: interface.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $password    = $_POST['password'] ?? '';

    if (!$identifiant || !$password) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Recherche par ncode OU email
        $stmt = $db->prepare("SELECT ncode, nom, prenom, grade, specialite, passwords FROM users WHERE ncode = ? OR email = ?");
        $stmt->execute([$identifiant, $identifiant]);
        $agent = $stmt->fetch();

        if ($agent && password_verify($password, $agent['passwords'])) {
            // Connexion réussie
            $_SESSION['agent_ncode']    = $agent['ncode'];
            $_SESSION['agent_nom']      = $agent['nom'] ?? $agent['ncode'];
            $_SESSION['agent_grade']    = $agent['grade'];
            $_SESSION['agent_specialite'] = $agent['specialite'];

            // Mise à jour last_login
            $db->prepare("UPDATE users SET last_login = NOW() WHERE ncode = ?")
               ->execute([$agent['ncode']]);

            header('Location: interface.php');
            exit;
        } else {
            $error = "Code agent / email ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShadowComm — Connexion Agent</title>
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
            background: rgba(7, 9, 13, 0.95);
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
            color: var(--blue);
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

        .nav-back {
            color: var(--muted);
            font-size: .78rem;
            letter-spacing: .5px;
            transition: color .3s;
        }

        .nav-back:hover {
            color: #fff;
        }

        /* ── MAIN ── */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem;
            background:
                radial-gradient(ellipse at 50% 40%, rgba(37, 99, 235, 0.06) 0%, transparent 65%),
                var(--dark);
        }

        /* ── CARD ── */
        .card {
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 2.8rem 3rem;
        }

        .card-kicker {
            font-size: .65rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--blue);
            margin-bottom: .6rem;
        }

        .card-title {
            font-family: 'Rajdhani', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: .4rem;
        }

        .card-sub {
            font-size: .78rem;
            color: var(--muted);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .divider-line {
            width: 40px;
            height: 2px;
            background: var(--blue);
            margin-bottom: 2rem;
        }

        /* ── FORMULAIRE ── */
        .form-group {
            margin-bottom: 1.3rem;
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: .68rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: .4rem;
        }

        .form-group input {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 6px;
            padding: .8rem 1rem;
            color: #fff;
            font-family: 'Open Sans', sans-serif;
            font-size: .85rem;
            transition: border-color .3s;
            width: 100%;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--blue);
        }

        .form-group input::placeholder {
            color: var(--muted);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 2.8rem;
        }

        .toggle-password {
            position: absolute;
            right: .85rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1;
            padding: 0;
            transition: color .3s;
        }

        .toggle-password:hover {
            color: #fff;
        }

        /* ── ALERTE ERREUR ── */
        .alert-error {
            background: rgba(220, 38, 38, .1);
            border: 1px solid rgba(220, 38, 38, .4);
            border-radius: 6px;
            padding: .9rem 1.1rem;
            margin-bottom: 1.5rem;
            font-size: .78rem;
            color: #fca5a5;
        }

        /* ── BOUTON SUBMIT ── */
        .btn-submit {
            width: 100%;
            background: var(--blue);
            color: #fff;
            border: none;
            border-radius: 30px;
            padding: 1rem;
            font-family: 'Open Sans', sans-serif;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: background .3s, transform .2s;
            margin-top: .3rem;
        }

        .btn-submit:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: .75rem;
            color: var(--muted);
        }

        .register-link a {
            color: var(--blue);
            transition: color .3s;
        }

        .register-link a:hover {
            color: #93c5fd;
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
        @media (max-width: 640px) {
            nav    { padding: 1rem 1.5rem; }
            .card  { padding: 2rem 1.5rem; }
            footer { padding: 1.2rem 1.5rem; flex-direction: column; gap: .5rem; text-align: center; }
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
    <a href="interface.php" class="nav-back">← Retour à l'accueil</a>
</nav>

<main>
    <div class="card">
        <p class="card-kicker">Accès Sécurisé — Classifié</p>
        <h1 class="card-title">Authentification</h1>
        <p class="card-sub">Identifiez-vous avec votre code agent ou votre email pour accéder au système.</p>
        <div class="divider-line"></div>

        <?php if ($error): ?>
        <div class="alert-error">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label>Code Agent ou Email</label>
                <input type="text" name="identifiant" placeholder="agent007 ou agent@shadowcomm.net"
                       value="<?= htmlspecialchars($_POST['identifiant'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()" title="Afficher / Masquer">&#128065;</button>
                </div>
            </div>
            <button type="submit" class="btn-submit">Accéder au système</button>
        </form>

        <p class="register-link">Pas encore agent ? <a href="register.php">Rejoindre l'agence</a></p>
    </div>
</main>

<footer>
    <div class="footer-logo">ShadowComm</div>
    <div class="footer-classified">⬛ Classified — Accès Restreint</div>
</footer>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const btn   = document.querySelector('.toggle-password');
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '&#128064;';
    } else {
        input.type = 'password';
        btn.innerHTML = '&#128065;';
    }
}
</script>
</body>
</html>
