<?php
ob_start();
include_once "../../../../bd/config.php";

// Récupérer l'id du produit
$id_produit = 0;
if (isset($_POST['id_produit'])) {
    $id_produit = (int)$_POST['id_produit'];
} elseif (isset($_GET['id'])) {
    $id_produit = (int)$_GET['id'];
}

if ($id_produit === 0) {
    die("<p style='color:red'>Produit introuvable.</p>");
}

// Récupérer le produit et ses types
try {
    $connection = new PDO($dsn, $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT p.*, sc.nom_sous_categorie
            FROM PRODUIT p
            INNER JOIN SOUS_CATEGORIE sc ON p.id_sous_categorie = sc.id_sous_categorie
            WHERE p.id_produit = :id";
    $stmt = $connection->prepare($sql);
    $stmt->execute([':id' => $id_produit]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produit) die("<p style='color:red'>Produit introuvable.</p>");

    $sql2 = "SELECT * FROM type_produit WHERE id_produit = :id";
    $stmt2 = $connection->prepare($sql2);
    $stmt2->execute([':id' => $id_produit]);
    $types = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $error) {
    echo "<pre style='color:red'>ERREUR : " . $error->getMessage() . "</pre>";
    die();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de commande</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="commande.css">
    <!-- SDK FedaPay -->
    <script src="https://cdn.fedapay.com/checkout.js?v=1.1.7" defer></script>
</head>

<body>
    <h1>Page De Commande</h1>
    <hr size="2" color="gray" width="60%" align="center">

    <form method="POST" action="tratement.php?id=<?= $id_produit ?>" id="commandeForm">
        <div class="gr-gauche">
            <h4><i class="fa-solid fa-user"></i> Identité</h4>
            <div class="flex-input">
                <label for="nom">NOM COMPLET :</label>
                <input type="text" id="nom" placeholder="Grah Désiré Jean Ivan" name="nom" required>
            </div>
            <div class="flex-input">
                <label for="email">EMAIL :</label>
                <input type="email" id="email" placeholder="desire@gmail.com" name="email" required>
            </div>
            <div class="flex-input">
                <label for="tel">TÉLÉPHONE :</label>
                <input type="tel" id="tel" placeholder="+225 07 69 19 37 53" name="telephone" required>
            </div>

            <h4><i class="fa-solid fa-location-dot"></i> Adresse de livraison</h4>
            <div class="flex-input">
                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" placeholder="Angré Djorogobité 1" name="adresse" required>
            </div>
            <div class="flex-input">
                <label for="ville">Ville</label>
                <input type="text" id="ville" placeholder="ABIDJAN" name="ville">
            </div>
            <div class="flex-input">
                <label for="code">Code Postal</label>
                <input type="number" id="code" placeholder="1000" name="code_postal">
            </div>

            <h4><i class="fa-solid fa-credit-card"></i> PAIEMENT</h4>

            <!-- Option 1 : Paiement avant livraison via FedaPay -->
            <div class="flex-input">
                <label>
                    <input type="radio" name="mode_paiement" value="fedapay" id="radio_fedapay">
                    <i class="fa-solid fa-bolt"></i> Paiement Avant Livraison (FedaPay)
                </label>
            </div>

            <!-- Option 2 : Paiement à la livraison -->
            <div class="flex-input">
                <label>
                    <input type="radio" name="mode_paiement" value="apres_livraison" id="radio_livraison">
                    <i class="fa-solid fa-truck"></i> Paiement À La Livraison
                </label>
            </div>

            <div class="flex-input">
                <label>
                    <input type="radio" name="mode_paiement" value="en_boutique" id="radio_boutique">
                    <i class="fa-solid fa-shop"></i> Paiement En Boutiques
                </label>
            </div>
        </div>

        <div class="gr-droite">
            <div class="conten">
                <?php if (!empty($types)): ?>
                    <?php foreach ($types as $type): ?>
                        <div class="type">
                            <label style="color: purple;"><?= htmlspecialchars($type['nom_type']) ?></label>
                            <input type="radio" name="type" value="<?= (int)$type['id_type'] ?>"
                                data-prix="<?= $type['prix'] ?>" data-prix-gros="<?= $type['prix_gros'] ?>">
                        </div>
                        <label>Prix :</label>
                        <h4 class="prix-type"><?= number_format($type['prix'], 0, ',', ' ') ?> FCFA</h4>
                        <label>Prix En Gros :</label>
                        <h4 class="prix-gros"><?= number_format($type['prix_gros'], 0, ',', ' ') ?> FCFA</h4>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: purple; text-align: center; ">Aucun type disponible pour ce produit.</p>
                <?php endif; ?>

                <div class="ligne">
                    <h4>Quantité :</h4>
                    <button type="button" class="moins"> - </button>
                    <span id="quantite">1</span>
                    <button type="button" class="plus"> + </button>
                </div>

                <div class="carte-image">
                    <img src="../../images/electricite/<?= htmlspecialchars($produit['image']) ?>"
                        alt="<?= htmlspecialchars($produit['nom_produit']) ?>">
                    <h4 id="prix_total"><?= number_format($produit['prix'], 0, ',', ' ') ?> FCFA</h4>
                </div>

                <input type="hidden" name="id_produit" value="<?= (int)$produit['id_produit'] ?>">
                <input type="hidden" name="quantite" id="quantite_hidden" value="1">
                <!-- Reçoit l'ID de transaction FedaPay après paiement réussi -->
                <input type="hidden" name="transaction_id" id="transaction_id" value="">

                <div class="btn">
                    <button class="bt" type="submit">Confirmer</button>
                </div>
            </div>
        </div>
    </form>

    <script>
        let quantite = 1;
        let prix = <?= $produit['prix'] ?>;
        const quantiteSpan = document.getElementById('quantite');
        const quantiteHidden = document.getElementById('quantite_hidden');
        const prixTotal = document.getElementById('prix_total');

        function updatePrix() {
            prixTotal.textContent = (prix * quantite).toLocaleString('fr-FR') + ' FCFA';
        }

        document.querySelector('.plus').addEventListener('click', () => {
            quantite++;
            quantiteSpan.textContent = quantite;
            quantiteHidden.value = quantite;
            updatePrix();
        });

        document.querySelector('.moins').addEventListener('click', () => {
            if (quantite > 1) {
                quantite--;
                quantiteSpan.textContent = quantite;
                quantiteHidden.value = quantite;
                updatePrix();
            }
        });

        // Changer le prix quand on sélectionne un type
        document.querySelectorAll('input[name="type"]').forEach(radio => {
            radio.addEventListener('change', e => {
                prix = parseFloat(e.target.dataset.prix);
                updatePrix();
            });
        });

        // ✅ Interception du formulaire
        document.getElementById('commandeForm').addEventListener('submit', function(e) {
            const modePaiement = document.querySelector('input[name="mode_paiement"]:checked');

            if (!modePaiement) {
                e.preventDefault();
                alert('Veuillez choisir un mode de paiement.');
                return;
            }

            // Paiement à la livraison → soumettre directement sans widget
            if (modePaiement.value === 'apres_livraison' || modePaiement.value === 'en_boutique') return

            // Paiement FedaPay → ouvrir le widget
            e.preventDefault();

            const nom = document.getElementById('nom').value;
            const email = document.getElementById('email').value;
            const tel = document.getElementById('tel').value;
            const montantTotal = prix * quantite;

            FedaPay.init({
                public_key: 'pk_live_y2lBbYV3Y3UpCjXp3XL7omqx',
                transaction: {
                    amount: montantTotal,
                    description: '<?= addslashes(htmlspecialchars($produit['nom_produit'])) ?>',
                    currency: {
                        iso: 'XOF'
                    }
                },
                customer: {
                    email: email,
                    lastname: nom,
                    phone_number: {
                        number: tel,
                        country: 'CI'
                    }
                },
                onComplete: function(transaction) {
                    if (transaction.reason === FedaPay.TRANSACTION_APPROVED) {
                        document.getElementById('transaction_id').value = transaction.id;
                        document.getElementById('commandeForm').submit();
                    } else {
                        alert(' Paiement annulé ou échoué. Veuillez réessayer.');
                    }
                }
            }).open();
        });
    </script>
</body>

</html>