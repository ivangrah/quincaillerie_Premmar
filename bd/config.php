<?php
// Détection automatique local vs production
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    // Config locale LAMPP
    $host = 'localhost';
    $dbname = 'quincailleriie_db';
    $user = 'root';
    $pass = 'root';
    $port = '3306';
} else {
    // Config InfinityFree
    $host = 'sql303.infinityfree.com';
    $dbname = 'if0_41689812_quincailleriie';
    $user = 'if0_41689812';
    $pass = '591tfN4lXh4Xw';
    $port = '3306';
}

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
$username = $user;
$password = $pass;

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    date_default_timezone_set('Africa/Abidjan');
    $pdo->exec("SET time_zone = '+00:00'");
} catch (PDOException $e) {
    die("Erreur connexion : " . $e->getMessage());
}
