<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plomberie – PREMMAR</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <section class="container">
        <div class="haut">
            <header>
                <nav>
                    <div class="align-nav">
                        <a href="../../../page-accueil/index.php" class="skip">
                            <img src="./image-electrique/logo0.png" class="afrique" alt="logo">
                            <p>PREMMAR PRODUIT</p>
                        </a>
                    </div>
                </nav>
                <nav>
                    <div class="block">
                        <div class="serach">
                                 <input type="search" id="searchInput" placeholder=" Recherche" oninput="filtrerProduits()">
                        </div>
                    </div>

                    <div class="align-nav">
                        <ul>
                            <a href="../../../page-accueil/index.php"><i class="fa-solid fa-house"></i> Accueil</a>
                            <a href="../../../../bd/cathegorie.php"><i class="fa-solid fa-folder"></i> Catégories</a>
                            <a href="../plomberie/index.php"><i class="fa-solid fa-box"></i> Produit</a>
                            <a href="../plomberie/commande.php"><i class="fa-solid fa-shopping-cart"></i> Paniers</a>
                        </ul>
                    </div>

                    <h1 style="margin-top: 70px;">PLOMBERIE</h1>
                </nav>
            </header>

                 <div class="line-flex">
            <a href="../electricite/index.php">Electricite</a>
            <a href="../outillage/index.php">Outillage</a>
            <a href="../serrurerie/index.php">Serrurerie</a>
            <a href="../visserie/index.php">Visserie</a>
            <a href="../peinture-colle-et-produit-chimique/index.php">Peinture colle et produit chimique</a>
            <a href="../rouleaux/index.php">Rouleaux</a>
              <a href="../cables/index.php">Cables</a>
                <a href="../adhesifs/index.php">Adhesifs</a>
            
        </div>
        </div>

        <?php
session_start();

$count = 0;

if (isset($_SESSION['panier'])) {
    $count = count($_SESSION['panier']);
}


        include_once "../../../../bd/config.php";

        // ID de la catégorie Plomberie = 2
        $id_categorie = 2;

        try {
            $sql = "SELECT p.*, sc.nom_sous_categorie 
                    FROM PRODUIT p
                    INNER JOIN SOUS_CATEGORIE sc ON p.id_sous_categorie = sc.id_sous_categorie
                    WHERE p.id_categorie = ?";

            $statement = $pdo->prepare($sql);
            $statement->execute([$id_categorie]);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $error) {
            echo "<pre style='color:red; padding:20px;'>ERREUR : " . $error->getMessage() . "</pre>";
            die();
        }
        ?>

        <div class="produits-grid">

            <?php if (empty($result)): ?>
                <div class="vide">
                    <i class="fa-solid fa-box-open"></i>
                    <p>Aucun produit disponible.</p>
                </div>

            <?php else: ?>
                <?php foreach ($result as $produit): ?>
                    <div class="carte" id="carte">

                        <div class="carte-image">
                            <img src="../../images/plomberie/<?= htmlspecialchars($produit['image']) ?>"
                                alt="<?= htmlspecialchars($produit['nom_produit']) ?>">
                        </div>

                        <div class="carte-body">

                            <span class="carte-sous-cat">
                                <i class="fa-solid fa-folder"></i>
                                <?= htmlspecialchars($produit['nom_sous_categorie']) ?>
                            </span>

                            <h3 class="carte-nom"><?= htmlspecialchars($produit['nom_produit']) ?></h3>
                            <p class="carte-desc"><?= htmlspecialchars($produit['description']) ?></p>

                            <div class="carte-footer">
                                <span class="carte-prix">
                                    <?= number_format($produit['prix'], 0, ',', ' ') ?> <small>FCFA</small>
                                </span>
                                  <a href="../../../../bd/panier.php?id=<?= $produit['id_produit'] ?>" class="btn-voir">
                                       <i class="fa-solid fa-cart-shopping"></i>
                                        Ajouter
                                    </a>
                               
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>

        <?php include_once "../../../../bd/footer.php"; ?>
    </section>
</body>

<script>

const addToCartButtons = document.querySelectorAll('.add-to-cart');

addToCartButtons.forEach(button => {

    button.addEventListener('click', function () {

        // récupère la carte du produit
        const card = this.closest('.carte');

        // récupère le badge de CETTE carte
        const cartCount = card.querySelector('.cartCount');

        let count = parseInt(cartCount.textContent);

        if (count < 10) {

            count++;

            cartCount.textContent = count;

            alert(count + " produit(s) ajouté(s) au panier !");

        } else {

            alert("Limite atteinte ! 10 produits maximum.");

        }

    });

});


function filtrerProduits() {
    const terme = document.getElementById('searchInput').value.toLowerCase().trim();
    const cartes = document.querySelectorAll('.carte');
    let visible = 0;

    cartes.forEach(carte => {
        const nom = carte.querySelector('.carte-nom')?.textContent.toLowerCase() || '';
        const desc = carte.querySelector('.carte-desc')?.textContent.toLowerCase() || '';
        const sousCat = carte.querySelector('.carte-sous-cat')?.textContent.toLowerCase() || '';

        const correspond = nom.includes(terme) || desc.includes(terme) || sousCat.includes(terme);

        carte.style.display = correspond ? '' : 'none';
        if (correspond) visible++;
    });

    // Afficher message si aucun résultat
    let msgVide = document.getElementById('msg-recherche');
    if (!msgVide) {
        msgVide = document.createElement('div');
        msgVide.id = 'msg-recherche';
        msgVide.className = 'vide';
        msgVide.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i><p>Aucun produit trouvé.</p>';
        document.querySelector('.produits-grid').appendChild(msgVide);
    }
    msgVide.style.display = visible === 0 && terme !== '' ? 'flex' : 'none';
}

// Recherche aussi au clic sur Entrée
document.getElementById('searchInput').addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        e.target.value = '';
        filtrerProduits();
    }
});
 




</script>

</html>