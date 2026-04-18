<?php
$host = 'sql303.infinityfree.com';
$dbname = 'if0_41689812_quincailleriie';
$user = 'if0_41689812';
$pass = '591tfN4lXh4Xw';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur connexion : " . $e->getMessage());
}
?>

