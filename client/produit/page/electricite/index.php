<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electricite – PREMMAR</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <section class="container">
        <div class="haut">
            <header>
                <nav>
                    <div class="align-nav">
                        <div class="skip">
                            <img src="./image-electrique/logo0.png" class="afrique" alt="logo">
                            <p>PREMMAR PRODUIT</p>
                        </div>
                    </div>
                </nav>
                <nav>
                    <div class="block">
                        <div class="serach">
                            <input type="search" placeholder=" Recherche">
                        </div>
                    </div>

                    <div class="align-nav">
                        <ul>
                            <a href="../../../page-accueil/index.php"><i class="fa-solid fa-house"></i> Accueil</a>
                            <a href="../../../../bd/cathegorie.php"><i class="fa-solid fa-folder"></i> Catégories</a>
                            <a href="../electricite/index.php"><i class="fa-solid fa-box"></i> Produit</a>
                            <a href="../electricite/commande.php"><i class="fa-solid fa-shopping-cart"></i> Paniers</a>
                        </ul>
                    </div>

                    <h1 style="margin-top: 70px;">ELECTRICITE</h1>
                </nav>
            </header>
        </div>

        <?php
        include_once "../../../../bd/config.php";

        // ID de la catégorie Electricite = 1
        $id_categorie = 1;

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
                    <div class="carte">

                        <div class="carte-image">
                            <img src="../../images/electricite/<?= htmlspecialchars($produit['image']) ?>"
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

                                <a href="commande.php?id=<?= (int)$produit['id_produit'] ?>" class="btn-voir">
                                    <i class="fa-solid fa-cart-shopping"></i> Commander
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

</html>