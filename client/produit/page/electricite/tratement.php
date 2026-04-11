<?php
ob_start();
include_once "../../../../bd/config.php";



// VALIDATION DES CHAMPS


if (!isset($_POST['nom'], $_POST['telephone'], $_POST['adresse'], $_POST['email'], $_POST['id_produit'], $_POST['mode_paiement'])) {
    die("Veuillez remplir tous les champs obligatoires.");
}

$nom           = htmlspecialchars(trim($_POST['nom']));
$telephone     = htmlspecialchars(trim($_POST['telephone']));
$adresse       = htmlspecialchars(trim($_POST['adresse']));
$email         = htmlspecialchars(trim($_POST['email']));
$id_produit    = (int)$_POST['id_produit'];
$quantite      = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;
$mode_paiement = $_POST['mode_paiement']; // 'fedapay' ou 'apres_livraison'

$mode_stock = ($mode_paiement === 'fedapay') ? 'avant_livraison' : 'apres_livraison';

// ENREGISTREMENT CLIENT


$stmt = $pdo->prepare("INSERT INTO CLIENT (nom, telephone, adresse, email) VALUES (?, ?, ?, ?)");
$stmt->execute([$nom, $telephone, $adresse, $email]);
$id_client = $pdo->lastInsertId();

// =====================================================================
// RÉCUPÉRER LE PRODUIT
// =====================================================================

$stmt = $pdo->prepare("SELECT prix, nom_produit FROM PRODUIT WHERE id_produit = ?");
$stmt->execute([$id_produit]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

$prix          = $produit['prix'] ?? 0;
$montant_total = $prix * $quantite;
$numero_cmd    = "CMD" . time();

// =====================================================================
// CRÉER LA COMMANDE
// =====================================================================

$stmt = $pdo->prepare("INSERT INTO COMMANDE (numero_commande, montant_total, mode_paiement, statut_commande, id_client) VALUES (?, ?, ?, 'en_attente', ?)");
$stmt->execute([$numero_cmd, $montant_total, $mode_stock, $id_client]);
$id_commande = $pdo->lastInsertId();

// =====================================================================
// HEAD HTML commun (lien vers le CSS externe)
// =====================================================================

function html_head(string $title): string
{
    return '<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>' . $title . ' — PREMMAR BOUTIQUES</title>
  <link rel="stylesheet" href="tratement.css">
</head>';
}

 
// PAIEMENT FEDAPAY


if ($mode_paiement === 'fedapay') {

    // Transaction manquante 
    if (!isset($_POST['transaction_id']) || empty($_POST['transaction_id'])) {

        $pdo->prepare("DELETE FROM COMMANDE WHERE id_commande = ?")->execute([$id_commande]);
        ob_end_clean();

        echo html_head('Paiement refusé');
?>


        <body class="page-error">
            <div class="card">
                <p class="brand">PREMMAR BOUTIQUES</p>

                <div class="icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="15" y1="9" x2="9" y2="15" />
                        <line x1="9" y1="9" x2="15" y2="15" />
                    </svg>
                </div>

                <h1>Transaction manquante</h1>
                <p class="subtitle">
                    Aucune transaction FedaPay n'a été reçue.<br>
                    Veuillez <strong>réessayer</strong> votre paiement.
                </p>

                <a class="btn-retour" href="javascript:history.back()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                    Réessayer
                </a>
            </div>
        </body>

        </html>
    <?php
        exit();
    }

    // ── Vérification FedaPay côté serveur ────────────────────────────
    require 'config-secret.php';

    \Stripe\Stripe::setApiKey($stripe_secret_key);
    $transaction_id = (int)$_POST['transaction_id'];

    $ch = curl_init("https://api.fedapay.com/v1/transactions/{$transaction_id}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer $fedapay_secret",
            "Content-Type: application/json",
        ],
    ]);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $statut = $response['v1/transaction']['status'] ?? 'failed';

    // ── Paiement approuvé ────────────────────────────────────────────
    if ($statut === 'approved') {

        $pdo->prepare("UPDATE COMMANDE SET statut_commande = 'payee' WHERE id_commande = ?")
            ->execute([$id_commande]);
        ob_end_clean();

        echo html_head('Paiement confirmé');
    ?>

        <body class="page-success">
            <div class="card">
                <p class="brand">PREMMAR BOUTIQUES</p>

                <div class="icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </div>

                <h1>Paiement confirmé !</h1>
                <p class="subtitle">
                    Votre paiement FedaPay a été <strong>validé avec succès</strong>.<br>
                    Merci pour votre confiance.
                </p>

                <div class="details">
                    <div class="details-row">
                        <span class="label">N° commande</span>
                        <span class="value"><?= htmlspecialchars($numero_cmd) ?></span>
                    </div>
                    <div class="details-row">
                        <span class="label">Montant payé</span>
                        <span class="value montant"><?= number_format($montant_total, 0, ',', ' ') ?> FCFA</span>
                    </div>
                    <div class="details-row">
                        <span class="label">Reçu envoyé à</span>
                        <span class="value"><?= htmlspecialchars($email) ?></span>
                    </div>
                    <div class="details-row">
                        <span class="label">Mode de paiement</span>
                        <span class="value">FedaPay</span>
                    </div>
                </div>

                <a class="btn-retour" href="index.php">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                    Retour à l'accueil
                </a>
            </div>
        </body>

        </html>
    <?php
        exit();
    }

    // ── Paiement refusé ──────────────────────────────────────────────
    $pdo->prepare("DELETE FROM COMMANDE WHERE id_commande = ?")->execute([$id_commande]);
    ob_end_clean();

    echo html_head('Paiement refusé');
    ?>

    <body class="page-error">
        <div class="card">
            <p class="brand">PREMMAR BOUTIQUES</p>

            <div class="icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="15" y1="9" x2="9" y2="15" />
                    <line x1="9" y1="9" x2="15" y2="15" />
                </svg>
            </div>

            <h1>Paiement refusé</h1>
            <p class="subtitle">
                Votre paiement FedaPay n'a pas pu être validé.<br>
                Statut reçu : <strong><?= htmlspecialchars($statut) ?></strong>
            </p>

            <div class="details">
                <div class="details-row">
                    <span class="label">N° tentative</span>
                    <span class="value"><?= htmlspecialchars($numero_cmd) ?></span>
                </div>
                <div class="details-row">
                    <span class="label">Montant concerné</span>
                    <span class="value montant"><?= number_format($montant_total, 0, ',', ' ') ?> FCFA</span>
                </div>
            </div>

            <a class="btn-retour" href="javascript:history.back()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Réessayer
            </a>
        </div>
    </body>

    </html>
<?php
    exit();
}

// =====================================================================
// PAIEMENT À LA LIVRAISON
// =====================================================================

ob_end_clean();

echo html_head('Commande enregistrée');
?>

<body class="page-delivery">
    <div class="card">
        <p class="brand">PREMMAR BOUTIQUES</p>

        <div class="icon-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="1" y="3" width="15" height="13" rx="2" />
                <path d="M16 8h4l3 5v3h-7V8z" />
                <circle cx="5.5" cy="18.5" r="2.5" />
                <circle cx="18.5" cy="18.5" r="2.5" />
            </svg>
        </div>

        <h1>Commande enregistrée !</h1>
        <p class="subtitle">
            Votre commande a bien été prise en compte.<br>
            Vous serez <strong>contacté pour la livraison</strong>.
        </p>

        <div class="details">
            <div class="details-row">
                <span class="label">N° commande</span>
                <span class="value"><?= htmlspecialchars($numero_cmd) ?></span>
            </div>
            <div class="details-row">
                <span class="label">Montant total</span>
                <span class="value montant"><?= number_format($montant_total, 0, ',', ' ') ?> FCFA</span>
            </div>
            <div class="details-row">
                <span class="label">Mode de paiement</span>
                <span class="value">À la livraison</span>
            </div>
            <div class="details-row">
                <span class="label">Adresse</span>
                <span class="value"><?= htmlspecialchars($adresse) ?></span>
            </div>
            <div class="details-row">
                <span class="label">Contact</span>
                <span class="value"><?= htmlspecialchars($telephone) ?></span>
            </div>
            
        </div>

        <a class="btn-retour" href="index.php">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                <polyline points="9 22 9 12 15 12 15 22" />
            </svg>
            Retour à l'accueil
        </a>
    </div>
</body>

</html>