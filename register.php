<?php
session_start();
require_once __DIR__ . '/config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $captcha = strtoupper(trim($_POST['captcha'] ?? ''));
    $chovatel_number = trim($_POST['chovatel_number'] ?? '');

    // Validace
   if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
    $error = 'Vyplňte prosím všechna povinná pole.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Neplatný e-mail.';
} elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
    $error = 'Heslo musí mít min. 8 znaků, obsahovat malé i velké písmeno a číslo.';
} elseif ($password !== $confirm_password) {
    $error = 'Hesla se neshodují.';
} elseif ($captcha !== ($_SESSION['captcha_code'] ?? '')) {
    $error = 'Špatně opsaný bezpečnostní kód.';
} else {
    // Ověření, zda e‑mail už existuje
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $emailExists = $stmt->fetchColumn();

    if ($emailExists > 0) {
        $error = 'Tento e‑mail je již zaregistrován.';
    } else {
        // OK → uložit uživatele
        $verify_token = bin2hex(random_bytes(32));
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, chovatel_number, verify_token, is_verified)
                               VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->execute([$name, $email, $hashed_password, $chovatel_number, $verify_token]);

        // Odeslání ověřovacího e‑mailu
        $verifyLink = "https://" . $_SERVER['HTTP_HOST'] . "/verify.php?token=$verify_token";
        $subject = "Aktivace účtu – Úlový zápisník";
        $message = "
Dobrý den,

děkujeme za registraci do aplikace Úlový zápisník.

Pro aktivaci účtu klikněte na následující odkaz:
$verifyLink

Pokud jste se neregistroval(a), tento e-mail prosím ignorujte.

Toto je automaticky generovaný e-mail – neodpovídejte na něj.
";
        $headers = "From: Úlový zápisník <ucty@ulovyzapisnik.cz>\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        mail($email, $subject, $message, $headers);

        $success = 'Registrace proběhla úspěšně. Zkontrolujte e‑mail pro potvrzení účtu.';
    }
}}

?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="favicon.png">
    <title>Registrace – Úlový zápisník</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container mt-5">
    <h2>Registrace do Úlového zápisníku 🐝</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="mb-3">
            <label for="name" class="form-label">Jméno</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Heslo</label>
            <input type="password" name="password" class="form-control" required>
            <div class="form-text">Minimálně 8 znaků, malé + velké písmeno a číslice.</div>
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">Heslo znovu</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="chovatel_number" class="form-label">Číslo chovatele (nepovinné)</label>
            <input type="text" name="chovatel_number" class="form-control">
        </div>

        <div class="mb-3">
            <label for="captcha" class="form-label">Bezpečnostní kód</label><br>
            <img src="captcha.php" alt="captcha"><br>
            <input type="text" name="captcha" class="form-control mt-2" required>
        </div>

        <button type="submit" class="btn btn-primary">Zaregistrovat se</button>
    </form>
</div>
</body>
</html>
