<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Úlový zápisník – Vítejte</title>
    <link rel="icon" type="image/x-icon" href="favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<!--
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">🐝 Úlový zápisník</a>
        <div class="d-flex">
            <?php if ($isLoggedIn): ?>
                <a href="logout.php" class="btn btn-outline-danger">Odhlásit se</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-primary me-2">Přihlášení</a>
                <a href="register.php" class="btn btn-primary">Registrace</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
-->
<div class="container mt-5">
    <h1 class="mb-4">Vítejte v aplikaci <strong>Úlový zápisník</strong> 🐝</h1>

    <p class="lead">
        Tento webový nástroj slouží pro snadné a přehledné vedení záznamů o úlech a včelařských kontrolách.
    </p>

    <div class="row mt-4">
        <div class="col-md-6">
            <h5>📋 Co aplikace umí:</h5>
            <ul>
                <li>✅ Vést evidenci stanovišť a jednotlivých úlů</li>
                <li>✅ Přidávat a zobrazovat kontroly úlů (varroa, matka, zásoby...)</li>
                <li>✅ Sledovat zásahy, krmení, léčení</li>
                <li>✅ Přehledný dashboard a mobilní podpora</li>
            </ul>
        </div>
        <div class="col-md-6">
            <h5>🔐 Přihlášený uživatel získá:</h5>
            <ul>
                <li>👤 Vlastní účet s ochranou heslem</li>
                <li>📈 Přístup ke všem svým úlům a kontrolám</li>
                <li>📝 Možnost exportu dat (např. pro SVS)</li>
            </ul>
        </div>
    </div>

    <hr class="my-4">

    
<?php include 'footer.php'; ?>
    <p class="text-muted small">Aplikace běží na PHP + MySQL • Vyvinuto pro české včelaře 🐝</p>
</div>

</body>
</html>
