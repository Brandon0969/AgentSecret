<?php
require_once '../Data/config.php';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ncode     = trim($_POST['ncode']     ?? '');
    $nom       = trim($_POST['nom']       ?? '');
    $prenom    = trim($_POST['prenom']    ?? '');
    $age       = trim($_POST['age']       ?? '');
    $specialite= trim($_POST['specialite']?? '');
    $grade     = trim($_POST['grade']     ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = $_POST['password']       ?? '';
    $confirm   = $_POST['confirm']        ?? '';

    // Validation
    if (!$ncode)   $errors[] = "Le code agent est obligatoire.";
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
    if (strlen($password) < 6) $errors[] = "Le mot de passe doit faire au moins 6 caractères.";
    if ($password !== $confirm) $errors[] = "Les mots de passe ne correspondent pas.";
    if (!in_array($specialite, ['agent_double','agent_informateur','cyber_espion'])) $errors[] = "Spécialité invalide.";
    if (!in_array($grade, ['expert','confirme','novice'])) $errors[] = "Grade invalide.";

    if (empty($errors)) {
        // Vérification unicité ncode et email
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE ncode = ? OR email = ?");
        $stmt->execute([$ncode, $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Ce code agent ou cet email est déjà utilisé.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (ncode, nom, prenom, age, specialite, grade, email, passwords) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ncode, $nom ?: null, $prenom ?: null, $age ?: null, $specialite, $grade, $email, $hash]);
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShadowComm — Rejoindre l'Agence</title>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        :root{--blue:#2563eb;--dark:#07090d;--text:#dde6f0;--muted:#64748b;--border:rgba(255,255,255,0.07);}
        body{font-family:'Open Sans',sans-serif;background:var(--dark);color:var(--text);min-height:100vh;display:flex;flex-direction:column;}
        a{text-decoration:none;}
        /* NAV */
        nav{display:flex;align-items:center;justify-content:space-between;padding:1.3rem 4.5rem;border-bottom:1px solid var(--border);background:rgba(7,9,13,0.95);}
        .logo{display:flex;align-items:center;gap:.8rem;}
        .logo-icon{width:42px;height:42px;border:2px solid var(--blue);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--blue);}
        .logo-name{font-family:'Rajdhani',sans-serif;font-size:1.45rem;font-weight:700;letter-spacing:2px;color:#fff;display:block;line-height:1.1;}
        .logo-sub{font-size:.55rem;color:var(--muted);letter-spacing:3px;text-transform:uppercase;display:block;}
        .nav-back{color:var(--muted);font-size:.78rem;letter-spacing:.5px;transition:color .3s;}
        .nav-back:hover{color:#fff;}
        /* MAIN */
        main{flex:1;display:flex;align-items:center;justify-content:center;padding:3rem 1.5rem;background:radial-gradient(ellipse at 50% 30%, rgba(37,99,235,0.06) 0%, transparent 65%), var(--dark);}
        .card{width:100%;max-width:580px;background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:12px;padding:2.8rem 3rem;}
        .card-kicker{font-size:.65rem;letter-spacing:4px;text-transform:uppercase;color:var(--blue);margin-bottom:.6rem;}
        .card-title{font-family:'Rajdhani',sans-serif;font-size:2.2rem;font-weight:700;color:#fff;margin-bottom:.4rem;}
        .card-sub{font-size:.78rem;color:var(--muted);margin-bottom:2rem;line-height:1.6;}
        .divider-line{width:40px;height:2px;background:var(--blue);margin-bottom:2rem;}
        /* FORM */
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
        .form-group{margin-bottom:1.2rem;display:flex;flex-direction:column;}
        .form-group label{font-size:.68rem;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);margin-bottom:.4rem;}
        .form-group input,.form-group select{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:6px;padding:.75rem 1rem;color:#fff;font-family:'Open Sans',sans-serif;font-size:.82rem;transition:border-color .3s;width:100%;}
        .form-group input:focus,.form-group select:focus{outline:none;border-color:var(--blue);}
        .form-group select option{background:#0d1117;color:#fff;}
        .password-wrapper{position:relative;}
        .password-wrapper input{padding-right:2.8rem;}
        .toggle-password{position:absolute;right:.85rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:1rem;line-height:1;padding:0;transition:color .3s;}
        .toggle-password:hover{color:#fff;}
        /* MESSAGES */
        .alert-error{background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.4);border-radius:6px;padding:1rem 1.2rem;margin-bottom:1.5rem;}
        .alert-error p{font-size:.78rem;color:#fca5a5;margin-bottom:.25rem;}
        .alert-error p:last-child{margin-bottom:0;}
        .alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.35);border-radius:6px;padding:1.5rem 1.2rem;text-align:center;}
        .alert-success h3{font-family:'Rajdhani',sans-serif;color:#4ade80;font-size:1.4rem;margin-bottom:.4rem;}
        .alert-success p{font-size:.8rem;color:var(--muted);}
        /* BUTTON */
        .btn-submit{width:100%;background:var(--blue);color:#fff;border:none;border-radius:30px;padding:1rem;font-family:'Open Sans',sans-serif;font-size:.75rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:background .3s,transform .2s;margin-top:.5rem;}
        .btn-submit:hover{background:#1d4ed8;transform:translateY(-2px);}
        .login-link{text-align:center;margin-top:1.5rem;font-size:.75rem;color:var(--muted);}
        .login-link a{color:var(--blue);transition:color .3s;}
        .login-link a:hover{color:#93c5fd;}
        /* FOOTER */
        footer{border-top:1px solid var(--border);padding:1.5rem 4.5rem;display:flex;justify-content:space-between;align-items:center;}
        .footer-logo{font-family:'Rajdhani',sans-serif;font-size:1.1rem;font-weight:700;color:#fff;letter-spacing:2px;}
        .footer-classified{font-size:.6rem;color:rgba(37,99,235,.5);letter-spacing:3px;text-transform:uppercase;}
        @media(max-width:640px){nav{padding:1rem 1.5rem;}.card{padding:2rem 1.5rem;}.form-row{grid-template-columns:1fr;}footer{padding:1.2rem 1.5rem;flex-direction:column;gap:.5rem;text-align:center;}}
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
        <p class="card-kicker">Recrutement — Classifié</p>
        <h1 class="card-title">Rejoindre l'Agence</h1>
        <p class="card-sub">Remplissez le formulaire ci-dessous. Votre dossier sera examiné par nos officiers supérieurs.</p>
        <div class="divider-line"></div>

        <?php if ($success): ?>
        <div class="alert-success">
            <h3>✓ Dossier enregistré</h3>
            <p>Bienvenue dans l'agence. <a href="login.php" style="color:var(--blue);">Connectez-vous</a> pour accéder à votre espace agent.</p>
        </div>

        <?php else: ?>

        <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <?php foreach ($errors as $e): ?>
            <p>⚠ <?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-row">
                <div class="form-group">
                    <label>Code Agent *</label>
                    <input type="text" name="ncode" placeholder="ex: agent007" value="<?= htmlspecialchars($_POST['ncode'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" placeholder="agent@shadowcomm.net" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" placeholder="Nom de famille" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" placeholder="Prénom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Âge</label>
                    <input type="number" name="age" placeholder="Âge" min="18" max="99" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Spécialité *</label>
                    <select name="specialite" required>
                        <option value="">-- Choisir --</option>
                        <option value="agent_double"      <?= ($_POST['specialite'] ?? '')==='agent_double'      ? 'selected':'' ?>>Agent Double</option>
                        <option value="agent_informateur" <?= ($_POST['specialite'] ?? '')==='agent_informateur' ? 'selected':'' ?>>Agent Informateur</option>
                        <option value="cyber_espion"      <?= ($_POST['specialite'] ?? '')==='cyber_espion'      ? 'selected':'' ?>>Cyber-Espion</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Grade *</label>
                    <select name="grade" required>
                        <option value="">-- Choisir --</option>
                        <option value="novice"   <?= ($_POST['grade'] ?? '')==='novice'   ? 'selected':'' ?>>Novice</option>
                        <option value="confirme" <?= ($_POST['grade'] ?? '')==='confirme' ? 'selected':'' ?>>Confirmé</option>
                        <option value="expert"   <?= ($_POST['grade'] ?? '')==='expert'   ? 'selected':'' ?>>Expert</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mot de passe *</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" placeholder="Min. 6 caractères" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', this)" title="Afficher / Masquer">&#128065;</button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Confirmer le mot de passe *</label>
                <div class="password-wrapper">
                    <input type="password" name="confirm" id="confirm" placeholder="Répéter le mot de passe" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm', this)" title="Afficher / Masquer">&#128065;</button>
                </div>
            </div>
            <button type="submit" class="btn-submit">Soumettre le dossier</button>
        </form>

        <p class="login-link">Déjà agent ? <a href="login.php">Se connecter</a></p>

        <?php endif; ?>
    </div>
</main>

<footer>
    <div class="footer-logo">ShadowComm</div>
    <div class="footer-classified">⬛ Classified — Accès Restreint</div>
</footer>

</body>
</html>
