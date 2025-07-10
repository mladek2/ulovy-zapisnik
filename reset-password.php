<?php
session_start();
require_once __DIR__ . '/config/database.php';

$token = $_GET['token'] ?? '';

$stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires >= NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Neplatný nebo expirovaný odkaz.");
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="favicon.png">
    <title>Obnova hesla</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container mt-5">
    <h2>Obnova hesla</h2>
    <form action="update-password.php" method="post">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <div class="mb-3">
            <label for="password">Nové heslo</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="confirm">Zopakujte heslo</label>
            <input type="password" name="confirm" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Nastavit nové heslo</button>
    </form>
</div>
</body>
</html>
