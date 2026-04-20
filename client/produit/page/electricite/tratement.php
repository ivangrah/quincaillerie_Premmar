<?php
ob_start();
include_once "../../../../bd/config.php";
include_once "config_secret.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. RÉCUPÉRER LES DONNÉES DU FORMULAIRE
if (!isset($_POST['id_produit'], $_POST['nom'], $_POST['mode_paiement'])) {
    die("Données du formulaire manquantes.");
}

$nom           = htmlspecialchars(trim($_POST['nom']));
$telephone     = htmlspecialchars(trim($_POST['telephone']));
$email         = htmlspecialchars(trim($_POST['email']));
$adresse       = htmlspecialchars(trim($_POST['adresse']));
$id_produit    = (int)$_POST['id_produit'];
$quantite      = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;
$mode_form     = $_POST['mode_paiement'];

// 2. RÉCUPÉRER LE PRIX
$stmt = $pdo->prepare("SELECT prix, nom_produit FROM PRODUIT WHERE id_produit = ?");
$stmt->execute([$id_produit]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) die("Produit inexistant.");

$prix = (float)$produit['prix'];
$montant_total = $prix * $quantite;
$numero_cmd = "CMD" . time();

// --- TRADUCTION POUR MODE_PAIEMENT (ENUM) ---
$mode_pour_bd = match ($mode_form) {
    'geniuspay'   => 'avant_livraison',
    'en_boutique' => 'en_boutique',
    default       => 'apres_livraison',
};

// 3. ENREGISTRER LE CLIENT
$stmt_cli = $pdo->prepare("INSERT INTO CLIENT (nom, telephone, email, adresse) VALUES (?, ?, ?, ?)");
$stmt_cli->execute([$nom, $telephone, $email, $adresse]);
$id_client = $pdo->lastInsertId();

// 4. ENREGISTRER LA COMMANDE
$sql_cmd = "INSERT INTO COMMANDE (numero_commande, montant_total, mode_paiement, statut_commande, id_client, id_produit, quantite) 
VALUES (?, ?, ?, 'en_attente', ?, ?, ?)";
$stmt_cmd = $pdo->prepare($sql_cmd);

// On envoie les 6 valeurs correspondant aux 6 points d'interrogation
$stmt_cmd->execute([
    $numero_cmd,
    $montant_total,
    $mode_pour_bd,
    $id_client,
    $id_produit,
    $quantite
]);
$id_commande = $pdo->lastInsertId();

// 5. VÉRIFICATION MONTANT MINIMUM GENIUSPAY
if ($mode_form === 'geniuspay' && $montant_total < 200) {
    $pdo->prepare("DELETE FROM COMMANDE WHERE id_commande = ?")->execute([$id_commande]);
    die("Le montant total (" . $montant_total . " FCFA) est inférieur au minimum de 200 FCFA requis par GeniusPay.");
}

/* ============================================================
   CAS : GENIUSPAY (LOGIQUE DE REDIRECTION)
   ============================================================ */
if ($mode_form === 'geniuspay') {
    $api_url = "https://pay.genius.ci/api/v1/merchant/payments";

    $payload = [
        "amount"      => $montant_total,
        "currency"    => "XOF",
        "description" => "Commande n°" . $numero_cmd . " - " . $produit['nom_produit'],
        "customer"    => [
            "name"  => $nom,
            "email" => $email,
            "phone" => $telephone
        ],
        "success_url" => "http://premmar.infinityfreeapp.com/projet_quincaillerie/client/produit/page/electricite/confirmation.php?id_cmd=" . $id_commande,
        "error_url"   => "http://premmar.infinityfreeapp.com/projet_quincaillerie/client/produit/page/electricite/commande.php?id=" . $id_produit
    ];

    $ch = curl_init($api_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            "X-API-Key: " . GENIUSPAY_PUBLIC_KEY,
            "X-API-Secret: " . GENIUSPAY_SECRET,
            "Content-Type: application/json",
            "Accept: application/json"
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 30
    ]);

    $response_json = curl_exec($ch);
    $http_code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response = json_decode($response_json, true);

    if ($http_code === 201 || (isset($response['success']) && $response['success'] === true)) {
        ob_end_clean();
        $url_redirection = $response['data']['checkout_url'] ?? $response['data']['payment_url'];
        header("Location: " . $url_redirection);
        exit();
    } else {
        $pdo->prepare("DELETE FROM COMMANDE WHERE id_commande = ?")->execute([$id_commande]);
        echo "<h3>Erreur GeniusPay</h3>";
        echo "Réponse brute : <pre>" . htmlspecialchars($response_json) . "</pre>";
        die();
    }
}

ob_end_clean();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Commande enregistrée</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="tratement.css">
</head>

<body>


    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="animated-card 
                  p-5 
                  text-center 
                  bg-white 
                  border border-success border-2 
                  rounded-5 
                  shadow-lg">


                    <h1 class="text-success">Merci, <?= htmlspecialchars($nom) ?> !</h1>

                    <p class="text-secondary">Votre commande <strong><?= $numero_cmd ?></strong> a bien été enregistrée.</p>
                    <p class="text-secondary">Mode choisi : <strong><?= ($mode_form === 'en_boutique') ? 'Paiement en boutique' : 'Paiement à la livraison' ?></strong></p>

                    <a href="/projet_quincaillerie/client/page-accueil/index.php"><button class="btn btn-success btn-lg px-5 shadow-sm"><i class="fa-solid fa-house"></i> Retour à l'accueil</button></a>

                    </button>

                </div>
            </div>
        </div>
    </div>


</body>

</html>