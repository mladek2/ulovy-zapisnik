<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>

<footer class="bg-white border-top mt-5 py-4">
    <div class="container text-center small text-muted">
        Úlový zápisník &copy; <?= date('Y') ?>  
        • Vyvinul <strong>Jiří Mládek</strong> ve spolupráci s <strong>ChatGPT</strong> 🧠🐝<br>

        <?php if (!$isLoggedIn): ?>
            <a href="login.php">Přihlášení</a> |
            <a href="register.php">Registrace</a> |
        <?php endif; ?>

        <a href="gdpr.php">Zásady ochrany soukromí</a>
    </div>
</footer>
