<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mdp = $_POST['password'] ?? '';

    if ($mdp === 'ivanovitch') { 
        $_SESSION['role'] = 'admin';
        header('Location: liste.php');
        exit;
    } else {
        $error = 'Mot de passe incorrect, réessayez.';
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe</title>
    <link rel="stylesheet" href="ident.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <section id="container">
        <i class="fa-solid fa-user" style="color: gray; font-size: 2rem; border: none; border-radius: 360px; 
    box-shadow: 2px 1px 1px 1px rgb(161, 160, 160); padding: 15px;"></i>
        <h1>ADMIN</h1><br>

        <form method="POST">
            <input type="password" name="password" placeholder="Entrez votre mot de passe" required id="texte"><br><br>
            <button type="submit" id="button">Confirmer</button>
        </form>

        <?php if ($error): ?>
            <p id="h3" style="display:block"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </section>
</body>

</html>