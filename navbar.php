<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="logo.png" alt="Logo" style="height: 28px; margin-right: 8px;">
            <span>🐝 Úlový zápisník</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($isLoggedIn): ?>
                     <li class="nav-item"><a class="nav-link" href="locations.php">Stanoviště</a></li>
                    <li class="nav-item"><a class="nav-link" href="hives.php">Úly</a></li>
                    <li class="nav-item"><a class="nav-link" href="inspections.php">Kontroly</a></li>
                    <li class="nav-item"><a class="nav-link" href="create-inspection.php">Nová kontrola</a></li>
                    <li class="nav-item"><a class="nav-link" href="user-preferences.php">Upravit předvolby</a></li>
                <?php endif; ?>
            </ul>

            <div class="d-flex">
                <?php if ($isLoggedIn): ?>
                    <a href="edit-user.php" class="btn btn-outline-secondary me-2">Upravit účet</a>
                    <a href="logout.php" class="btn btn-outline-danger">Odhlásit se</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary me-2">Přihlásit</a>
                    <a href="register.php" class="btn btn-primary">Registrovat</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
