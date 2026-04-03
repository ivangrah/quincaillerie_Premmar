<?php

include_once "../../../../bd/config.php";

$id_produit = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_produit === 0) {
    die("<p style='color:red'>Produit introuvable.</p>");
}

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

    if (!$produit) {
        die("<p style='color:red'>Produit introuvable.</p>");
    }

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
</head>

<body>



    <h1>Page De Commande</h1>
    <hr size="1" color="gray" width="60%" align="center" style="margin-left: 250px;">


    <form method="POST" action="traitement.php">




        <div class="gr-gauche">
            <h4><i class="fa-solid fa-user"></i> Identité</h4>
            <hr size="1" width="300px" color="gray" align="left">

            <div class="align-vertical-input-client">
                <div class="flex-input">
                    <label for="nom">NOM COMPLET :</label>
                    <input type="text" id="nom" placeholder="Grah Désiré Jean Ivan" name="nom">
                </div>
                <div class="flex-input">
                    <label for="email">EMAIL :</label>
                    <input type="email" id="email" placeholder="desire@gmail.com" name="email">
                </div>
                <div class="flex-input">
                    <label for="tel">TÉLÉPHONE :</label>

                    <input type="tel" id="tel" placeholder="+225 07 69 19 37 53" name="telephone">
                </div>
            </div>

            <hr size="1" width="300px" color="gray" align="left">
            <h4><i class="fa-solid fa-location-dot"></i> Adresse de livraison</h4>
            <hr size="1" width="300px" color="gray" align="left">

            <div class="align-vertical-input-adresse">
                <div class="flex-input">
                    <label for="adresse">Adresse</label>
                    <input type="text" id="adresse" placeholder="Angré Djorogobité 1" name="adresse">
                </div>
                <div class="flex-input">
                    <label for="ville">Ville</label>
                    <input type="text" id="ville" placeholder="ABIDJAN" name="ville">
                </div>
                <div class="flex-input">
                    <label for="code">Code Postal</label>
                    <input type="number" id="code" placeholder="1000" name="code_postal">
                </div>
            </div>

            <h4><i class="fa-solid fa-credit-card"></i> PAIEMENT</h4>
            <hr size="1" width="300px" color="gray" align="left">

            <div class="align-vertical-input-paiement">
                <div class="flex-input">
                    <label for="choix">Paiement Avant Livraison</label>
                    <select name="mode_paiement" id="choix">
                        <option value=""> Mode de paiement </option>
                        <option value="wave">Wave</option>
                        <option value="orange">Orange Money</option>
                        <option value="mtn">MTN Money</option>
                        <option value="moov">Moov Money</option>
                    </select>
                </div>
                <div class="flex-input">
                    <label for="apres">Paiement A La Livraison</label>

                    <input type="radio" id="apres" name="paiement_apres">

                </div>
                    <div class="btn">
                        <a href="index.php"><button class="bt" type="button">Retour</button></a>
                    </div>
                </div>
            </div>

        </div>


        <div class="gr-droite">
            <div class="conten">


                <?php if (!empty($types)): ?>
                    <?php foreach ($types as $type): ?>
                        <div class="type">
                            <label><?= htmlspecialchars($type['nom_type']) ?></label>
                            <input type="radio" name="type" value="<?= (int)$type['id_type'] ?>">
                        </div>
                        <label>Prix :</label>
                        <h4><?= number_format($type['prix'], 0, ',', ' ') ?> FCFA</h4>
                        <label>Prix En Gros :</label>
                        <h4><?= number_format($type['prix_gros'], 0, ',', ' ') ?> FCFA</h4>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun type disponible pour ce produit.</p>
                <?php endif; ?>


                <div class="text">
                    <div class="ligne">
                        <h4>Sous-Catégorie :</h4>
                        <h4><?= htmlspecialchars($produit['nom_sous_categorie']) ?></h4>
                    </div>
                    <div class="ligne">
                        <h4>Description :</h4>
                        <h4><?= htmlspecialchars($produit['description']) ?></h4>
                    </div>
                    <div class="ligne">
                        <h4>Quantité :</h4>
                        <h4>
                            <button type="button" class="special"> - </button>
                            <span id="quantite">1</span>
                            <button type="button" class="special"> + </button>
                        </h4>
                    </div>
                    <div class="ligne">
                        <h4>Contenant :</h4>
                        <h4><?= htmlspecialchars($produit['contenant']) ?></h4>
                    </div>
                </div>


                <div class="carte-image">
                    <img src="../../images/electricite/<?= htmlspecialchars($produit['image']) ?>"
                        alt="<?= htmlspecialchars($produit['nom_produit']) ?>">
                    <h4><?= number_format($produit['prix'], 0, ',', ' ') ?> FCFA</h4>
                </div>


                <input type="hidden" name="id_produit" value="<?= (int)$produit['id_produit'] ?>">

                <div class="btn">
                    <button class="bt" type="submit">Confirmer</button>
                </div>

            </div>
        </div>

    </form>
</body>
<script>
    const btnPlus = document.querySelector('.ligne button:last-child');
    const btnMoins = document.querySelector('.ligne button:first-child');
    const quantiteSpan = document.getElementById('quantite');

    const prix = <?= $produit['prix'] ?>;

    let quantite = 1;

    btnPlus.addEventListener('click', () => {
        quantite++;

        quantiteSpan.textContent = quantite;

        const total = quantite * prix;

        document.querySelector('.carte-image h4')
            .textContent = total.toLocaleString('fr-FR') + ' FCFA';
    });

    btnMoins.addEventListener('click', () => {

        if (quantite > 1) {

            quantite--;

            quantiteSpan.textContent = quantite;

            const total = quantite * prix;

            document.querySelector('.carte-image h4')
                .textContent = total.toLocaleString('fr-FR') + ' FCFA';
        }

    });
</script>

</html>