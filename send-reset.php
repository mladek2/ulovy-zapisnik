<?php
session_start();
require_once __DIR__ . '/config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));

    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE LOWER(email) = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Uživatel s tímto e-mailem neexistuje.";
    } else {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // platnost 1 hodina

        // Uloží token do tabulky users
        $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $update->execute([$token, $expires, $user['id']]);

        $resetLink = "https://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=$token";

        $subject = "Obnova hesla – Úlový zápisník";
        $message = "Dobrý den,

obdrželi jsme požadavek na obnovení hesla pro váš účet v aplikaci Úlový zápisník.

Pro obnovení hesla klikněte na následující odkaz (platí 1 hodinu):
$resetLink

Pokud jste požadavek nezadali, tento e‑mail prosím ignorujte.

Toto je automaticky generovaný e‑mail – neodpovídejte na něj.";

        $headers = "From: Úlový zápisník <ucty@ulovyzapisnik.cz>\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        mail($email, $subject, $message, $headers);

        $success = "E‑mail pro obnovu hesla byl odeslán.";
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Obnova hesla – Úlový zápisník</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container mt-5">
    <h2>Obnova hesla 🔐</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="email" class="form-label">Zadejte svůj e‑mail</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Odeslat odkaz pro obnovu hesla</button>
    </form>
</div>
</body>
</html>
