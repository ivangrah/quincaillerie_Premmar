<?php
ob_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once "../../../../bd/config.php"; // timezone + $pdo initialisés ici
include_once "config_secret.php";

// ✅ Récupérer id_commande depuis l'URL OU depuis la session (double sécurité)
// GeniusPay peut modifier l'URL de retour et perdre les paramètres GET
session_start();

if (isset($_GET['id_cmd']) && (int)$_GET['id_cmd'] > 0) {
    // Cas normal : id_cmd présent dans l'URL
    $id_commande = (int)$_GET['id_cmd'];
} elseif (isset($_SESSION['id_commande_pending']) && (int)$_SESSION['id_commande_pending'] > 0) {
    // Cas de secours : id_cmd perdu dans l'URL, on le récupère depuis la session
    $id_commande = (int)$_SESSION['id_commande_pending'];
} else {
    die("Référence de commande manquante.");
}

// Nettoyer la session après récupération
unset($_SESSION['id_commande_pending']);

try {
    $stmt = $pdo->prepare("
        SELECT c.*, cl.email, cl.nom AS nom_client, p.nom_produit
        FROM COMMANDE c
        JOIN CLIENT cl ON c.id_client = cl.id_client
        LEFT JOIN PRODUIT p ON c.id_produit = p.id_produit
        WHERE c.id_commande = ?
    ");
    $stmt->execute([$id_commande]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        die("Commande introuvable dans le système.");
    }

    // Marquer comme payée une seule fois
    if ($commande['statut_commande'] !== 'payee') {

        $pdo->prepare("UPDATE COMMANDE SET statut_commande = 'payee' WHERE id_commande = ?")
            ->execute([$id_commande]);

        // Recharger depuis la BDD pour avoir les données fraîches
        $stmt2 = $pdo->prepare("
            SELECT c.*, cl.email, cl.nom AS nom_client, p.nom_produit
            FROM COMMANDE c
            JOIN CLIENT cl ON c.id_client = cl.id_client
            LEFT JOIN PRODUIT p ON c.id_produit = p.id_produit
            WHERE c.id_commande = ?
        ");
        $stmt2->execute([$id_commande]);
        $commande = $stmt2->fetch(PDO::FETCH_ASSOC);

        // Envoi email de notification à l'admin
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'premmar.service.ci@gmail.com';
            $mail->Password   = 'hgfejzrqwraikzhk';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('premmar.service.ci@gmail.com', 'Premmar Service');
            $mail->addAddress('premmar.service.ci@gmail.com');
            $mail->isHTML(true);
            $mail->Subject = "Nouvelle commande payée : " . $commande['numero_commande'];
            $mail->Body    = "
            <html><body style='font-family: Arial, sans-serif;'>
                <h2 style='color: #f97316;'>Nouvelle commande reçue !</h2>
                <p><strong>Client :</strong> "    . htmlspecialchars($commande['nom_client']) . "</p>
                <p><strong>Email :</strong> "     . htmlspecialchars($commande['email']) . "</p>
                <p><strong>N° Commande :</strong> " . htmlspecialchars($commande['numero_commande']) . "</p>
                <p><strong>Produit :</strong> "   . htmlspecialchars($commande['nom_produit'] ?? 'Non spécifié') . "</p>
                <p><strong>Quantité :</strong> "  . htmlspecialchars($commande['quantite'] ?? '1') . "</p>
                <p><strong>Montant :</strong> "   . number_format($commande['montant_total'], 0, ',', ' ') . " FCFA</p>
            </body></html>";
            $mail->send();
        } catch (Exception $e) {
            error_log("Erreur PHPMailer : " . $mail->ErrorInfo);
        }
    }
} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}

ob_end_clean();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Confirmé – PREMMAR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="confirmation.css">
</head>

<body>
    <div class="card">

        <div class="icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>

        <h1>Paiement Réussi !</h1>
        <p>Merci <strong><?= htmlspecialchars($commande['nom_client']) ?></strong>, votre transaction a été validée.</p>

        <div class="details">
            <p><strong>N° Commande</strong> <?= htmlspecialchars($commande['numero_commande']) ?></p>
            <hr>
            <p><strong>Produit</strong> <?= htmlspecialchars($commande['nom_produit'] ?? 'Produit non spécifié') ?></p>
            <p><strong>Quantité</strong> <?= htmlspecialchars($commande['quantite'] ?? '1') ?></p>
            <hr>
            <p><strong>Montant payé</strong>
                <span class="montant"><?= number_format($commande['montant_total'], 0, ',', ' ') ?> FCFA</span>
            </p>
            <p><strong>Statut</strong> <span class="statut-ok">Confirmé (Payé)</span></p>
            <p><strong>Date</strong> <?= date('d/m/Y à H:i', strtotime($commande['date_commande'])) ?></p>
        </div>

        <p>Un email de confirmation a été envoyé à l'administration.</p>

        <a href="/projet_quincaillerie/client/produit/page/electricite/index.php" class="btn">
            <i class="fa-solid fa-arrow-left"></i> Retour à la boutique
        </a>

    </div>
</body>

</html>