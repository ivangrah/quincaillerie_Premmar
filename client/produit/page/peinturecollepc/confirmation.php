<?php
session_start();

ob_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once "../../../../bd/config.php";
include_once "config_secret.php";

if (empty($_GET['token'])) {
    die("Lien de confirmation invalide.");
}

$token = htmlspecialchars(trim($_GET['token']));

try {
    // ✅ Ajout de cl.adresse et c.mode_paiement dans le SELECT
    $stmt = $pdo->prepare("
        SELECT c.*, cl.email, cl.nom AS nom_client, cl.adresse, cl.telephone, p.nom_produit
        FROM COMMANDE c
        JOIN CLIENT cl ON c.id_client = cl.id_client
        LEFT JOIN PRODUIT p ON c.id_produit = p.id_produit
        WHERE c.token_confirmation = ?
    ");
    $stmt->execute([$token]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        die("Commande introuvable. Token invalide ou déjà utilisé.");
    }

    if ($commande['statut_commande'] !== 'payee') {

        $pdo->prepare("UPDATE COMMANDE SET statut_commande = 'payee' WHERE id_commande = ?")
            ->execute([$commande['id_commande']]);

        $stmt2 = $pdo->prepare("
            SELECT c.*, cl.email, cl.nom AS nom_client, cl.adresse, cl.telephone, p.nom_produit
            FROM COMMANDE c
            JOIN CLIENT cl ON c.id_client = cl.id_client
            LEFT JOIN PRODUIT p ON c.id_produit = p.id_produit
            WHERE c.token_confirmation = ?
        ");
        $stmt2->execute([$token]);
        $commande = $stmt2->fetch(PDO::FETCH_ASSOC);

        // Traduction mode paiement
        $mode_label = match($commande['mode_paiement']) {
            'avant_livraison'  => 'Paiement avant livraison (GeniusPay)',
            'apres_livraison'  => 'Paiement à la livraison',
            'en_boutique'      => 'Paiement en boutique',
            default            => $commande['mode_paiement']
        };

        // Délai de livraison selon mode
        $delai = match($commande['mode_paiement']) {
            'avant_livraison' => '24 à 48 heures ouvrables',
            'apres_livraison' => '24 à 48 heures ouvrables',
            'en_boutique'     => 'À récupérer en boutique à votre convenance',
            default           => '2 à 3 jours ouvrables'
        };

        // ── Email admin ──────────────────────────────────────────────────
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
                <p><strong>Client :</strong> "      . htmlspecialchars($commande['nom_client']) . "</p>
                <p><strong>Email :</strong> "       . htmlspecialchars($commande['email']) . "</p>
                <p><strong>Téléphone :</strong> "   . htmlspecialchars($commande['telephone'] ?? 'N/A') . "</p>
                <p><strong>Adresse :</strong> "     . htmlspecialchars($commande['adresse'] ?? 'N/A') . "</p>
                <p><strong>N° Commande :</strong> " . htmlspecialchars($commande['numero_commande']) . "</p>
                <p><strong>Produit :</strong> "     . htmlspecialchars($commande['nom_produit'] ?? 'Non spécifié') . "</p>
                <p><strong>Quantité :</strong> "    . htmlspecialchars($commande['quantite'] ?? '1') . "</p>
                <p><strong>Mode :</strong> "        . $mode_label . "</p>
                <p><strong>Montant :</strong> "     . number_format($commande['montant_total'], 0, ',', ' ') . " FCFA</p>
            </body></html>";
            $mail->send();
        } catch (Exception $e) {
            error_log("Erreur email admin : " . $mail->ErrorInfo);
        }

        // ── Email client ─────────────────────────────────────────────────
        $mailClient = new PHPMailer(true);
        try {
            $mailClient->isSMTP();
            $mailClient->Host       = 'smtp.gmail.com';
            $mailClient->SMTPAuth   = true;
            $mailClient->Username   = 'premmar.service.ci@gmail.com';
            $mailClient->Password   = 'hgfejzrqwraikzhk';
            $mailClient->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mailClient->Port       = 587;
            $mailClient->CharSet    = 'UTF-8';

            $mailClient->setFrom('premmar.service.ci@gmail.com', 'PREMMAR Boutiques');
            $mailClient->addAddress($commande['email'], $commande['nom_client']);
            $mailClient->isHTML(true);
            $mailClient->Subject = " Merci pour votre commande " . $commande['numero_commande'] . " — PREMMAR Boutiques";
            $mailClient->Body    = "
<!DOCTYPE html>
<html lang='fr'>
<body style='margin:0; padding:0; background:#f4f4f4; font-family: Arial, sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f4f4; padding: 32px 0;'>
<tr><td align='center'>
<table width='600' cellpadding='0' cellspacing='0' style='background:#fff; border-radius:10px; overflow:hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08);'>

  <!-- HEADER -->
  <tr>
    <td style='background: linear-gradient(135deg, #f97316, #ea580c); padding: 36px 40px; text-align:center;'>
      <h1 style='color:#fff; margin:0; font-size:26px; letter-spacing:1px;'>🛒 PREMMAR Boutiques</h1>
      <p style='color:#ffe4cc; margin:8px 0 0; font-size:14px;'>Votre quincaillerie de confiance à Abidjan</p>
    </td>
  </tr>

  <!-- MESSAGE D'ACCUEIL -->
  <tr>
    <td style='padding: 36px 40px 0;'>
      <h2 style='color:#1a1a1a; margin:0 0 12px;'>Bonjour " . htmlspecialchars($commande['nom_client']) . " </h2>
      <p style='color:#555; font-size:15px; line-height:1.7; margin:0;'>
        Nous avons bien reçu votre commande et votre paiement a été confirmé avec succès.<br>
        Toute l'équipe PREMMAR vous remercie pour votre confiance et met tout en œuvre pour vous satisfaire.
      </p>
    </td>
  </tr>

  <!-- RÉCAPITULATIF COMMANDE -->
  <tr>
    <td style='padding: 28px 40px 0;'>
      <h3 style='color:#f97316; font-size:16px; margin:0 0 16px; border-bottom: 2px solid #f97316; padding-bottom:8px;'>
         Récapitulatif de votre commande
      </h3>
      <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;'>
        <tr style='background:#fff7ed;'>
          <td style='padding:12px 16px; font-size:14px; color:#666; width:45%;'>N° Commande</td>
          <td style='padding:12px 16px; font-size:14px; color:#1a1a1a; font-weight:bold;'>" . htmlspecialchars($commande['numero_commande']) . "</td>
        </tr>
        <tr>
          <td style='padding:12px 16px; font-size:14px; color:#666;'>Produit</td>
          <td style='padding:12px 16px; font-size:14px; color:#1a1a1a;'>" . htmlspecialchars($commande['nom_produit'] ?? 'Non spécifié') . "</td>
        </tr>
        <tr style='background:#fff7ed;'>
          <td style='padding:12px 16px; font-size:14px; color:#666;'>Quantité</td>
          <td style='padding:12px 16px; font-size:14px; color:#1a1a1a;'>" . htmlspecialchars($commande['quantite'] ?? '1') . "</td>
        </tr>
        <tr>
          <td style='padding:12px 16px; font-size:14px; color:#666;'>Mode de paiement</td>
          <td style='padding:12px 16px; font-size:14px; color:#1a1a1a;'>" . $mode_label . "</td>
        </tr>
        <tr style='background:#fff7ed;'>
          <td style='padding:12px 16px; font-size:14px; color:#666;'>Date de commande</td>
          <td style='padding:12px 16px; font-size:14px; color:#1a1a1a;'>" . date('d/m/Y à H:i', strtotime($commande['date_commande'])) . "</td>
        </tr>
        <tr style='background:#f97316;'>
          <td style='padding:14px 16px; font-size:15px; color:#fff; font-weight:bold;'>Montant total payé</td>
          <td style='padding:14px 16px; font-size:18px; color:#fff; font-weight:bold;'>" . number_format($commande['montant_total'], 0, ',', ' ') . " FCFA</td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- ADRESSE DE LIVRAISON -->
  <tr>
    <td style='padding: 28px 40px 0;'>
      <h3 style='color:#f97316; font-size:16px; margin:0 0 16px; border-bottom: 2px solid #f97316; padding-bottom:8px;'>
         Adresse de livraison
      </h3>
      <p style='color:#444; font-size:14px; background:#f9f9f9; padding:14px 18px; border-radius:6px; margin:0;'>
        " . htmlspecialchars($commande['adresse'] ?? 'Non renseignée') . "
      </p>
    </td>
  </tr>

  <!-- DÉLAI DE LIVRAISON -->
  <tr>
    <td style='padding: 28px 40px 0;'>
      <h3 style='color:#f97316; font-size:16px; margin:0 0 16px; border-bottom: 2px solid #f97316; padding-bottom:8px;'>
         Délai de livraison estimé
      </h3>
      <p style='color:#444; font-size:14px; background:#fff7ed; padding:14px 18px; border-radius:6px; border-left: 4px solid #f97316; margin:0;'>
        " . $delai . "
      </p>
    </td>
  </tr>

  <!-- MESSAGE DE CLÔTURE -->
  <tr>
    <td style='padding: 28px 40px;'>
      <p style='color:#555; font-size:14px; line-height:1.7; margin:0 0 24px;'>
        Si vous avez la moindre question concernant votre commande, notre équipe est disponible pour vous aider.
        N'hésitez pas à nous contacter à l'adresse ci-dessous.
      </p>
      <table width='100%' cellpadding='0' cellspacing='0'>
        <tr>
          <td align='center'>
            <a href='http://premmar.infinityfreeapp.com/projet_quincaillerie/client/page-accueil/index.php'
               style='display:inline-block; background:#f97316; color:#fff; padding:14px 36px; border-radius:6px; text-decoration:none; font-weight:bold; font-size:15px;'>
              Retour à la boutique →
            </a>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- FOOTER -->
  <tr>
    <td style='background:#1a1a1a; padding:24px 40px; text-align:center;'>
      <p style='color:#aaa; font-size:12px; margin:0 0 6px;'>
         <a href='mailto:premmar.service.ci@gmail.com' style='color:#f97316; text-decoration:none;'>premmar.service.ci@gmail.com</a>
      </p>
      <p style='color:#aaa; font-size:12px; margin:0 0 6px;'>
         Angré Djorogobité 2, près du petit marché — Abidjan, Côte d'Ivoire
      </p>
      <p style='color:#666; font-size:11px; margin:12px 0 0;'>
        © 2025 PREMMAR Boutiques — Tous droits réservés
      </p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>";
            $mailClient->send();
        } catch (Exception $e) {
            error_log("Erreur email client : " . $mailClient->ErrorInfo);
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
        <p>Un email de confirmation a été envoyé à votre adresse.</p>
        <a href="/projet_quincaillerie/client/produit/page/electricite/index.php" class="btn">
            <i class="fa-solid fa-arrow-left"></i> Retour à la boutique
        </a>
    </div>
</body>
</html>
