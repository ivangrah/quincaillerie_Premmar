<?php 

$dsn = 'mysql:host=localhost;dbname=quincailleriie_db;charset=utf8';
$username = 'root';
$password = 'root';
try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

?>