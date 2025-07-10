<?php
ob_start();
session_start();
require_once __DIR__ . '/config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $captcha = strtoupper(trim($_POST['captcha'] ?? ''));

    if ($captcha !== ($_SESSION['captcha_code'] ?? '')) {
        $error = 'Špatně opsaný bezpečnostní kód.';
    } else {
        $stmt = $pdo->prepare("SELECT id, password_hash, is_verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Nesprávný e‑mail nebo heslo.';
        } elseif (!password_verify($password, $user['password_hash'])) {
            $error = 'Nesprávný e‑mail nebo heslo.';
        } elseif (!$user['is_verified']) {
            $error = 'Účet nebyl ověřen. Zkontrolujte prosím e‑mail.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="favicon.png">
    <title>Přihlášení – Úlový zápisník</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4">Přihlášení do Úlového zápisníku 🐝</h2>

        <?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error) ?><br>
        <small>
            Zapomněli jste heslo?
            <a href="forgot-password.php">Obnovit heslo pomocí e-mailu</a>
        </small>
    </div>
<?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" name="email" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Heslo</label>
                <input type="password" class="form-control" name="password" required>
            </div>
        <div class="mb-3">
    <label for="captcha">Opište kód z obrázku</label><br>
    <img src="captcha.php" alt="captcha" class="mb-2"><br>
    <input type="text" name="captcha" class="form-control" required>
</div>

            <button type="submit" class="btn btn-primary">Přihlásit se</button>
        </form>
    </div>
</body>
</html>
