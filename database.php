<?php
// Připojení k databázi na Wedosu

$host = 'md419.wedos.net';
$dbname = 'd378503_uly';
$user = 'w378503_uly';
$pass = 'eWn9AB78';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Chyba připojení k databázi: " . $e->getMessage());
}
