<?php
session_start();
require_once __DIR__ . '/config/database.php';

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm'] ?? '';

if ($password !== $confirm) {
    die("Hesla se neshodují.");
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires >= NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Neplatný nebo expirovaný token.");
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$update = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
$update->execute([$hash, $user['id']]);

echo "✅ Heslo bylo úspěšně změněno. <a href='login.php'>Přihlásit se</a>";
