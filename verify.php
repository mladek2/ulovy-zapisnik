<?php
require_once __DIR__ . '/config/database.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    exit('Neplatný ověřovací odkaz.');
}

// Ověříme, jestli token existuje
$stmt = $pdo->prepare("SELECT id FROM users WHERE verify_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    exit('Ověřovací odkaz je neplatný nebo již použit.');
}

// Pokud byl token nalezen, zobrazíme potvrzovací tlačítko
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verify_token = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);
    echo "✅ Účet byl ověřen. <a href='login.php'>Přihlásit se</a>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="favicon.png">
    <title>Ověření účtu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2>Potvrzení registrace</h2>
    <p>Chcete-li aktivovat svůj účet, potvrďte kliknutím na tlačítko níže:</p>
    <form method="post">
        <button type="submit" class="btn btn-success">Aktivovat účet</button>
    </form>
</body>
</html>
