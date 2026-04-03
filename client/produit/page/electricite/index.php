<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cathegories</title>
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
                            <img src="../electricite/image-electrique/logo0.png" class="afrique" alt="afrique">
                            <p>PREMMAR PRODUIT</p>
                        </div>
                    </div>
                </nav>
                <nav>
                    <div class="align-nav">
                        <ul>
                            <li><a href="../page-accueil/index.html">Accueil</a></li>
                            <li><a href="../boutique/index.html">Cathegories</a></li>
                            <li><a href="../page-accueil/index.html">Produit</a></li>
                            <li><a href="../page-accueil/index.html">Paniers</a></li>
                        </ul>
                    </div>
                    <div class="block">
                        <div class="serach">
                            <input type="search" placeholder=" recherche">
                        </div>
                        <h1 style="margin-top: 70px;">Electricite</h1>
                    </div>
                </nav>
            </header>
        </div>

        <?php

        include_once "../../../../bd/config.php";

        try {

            $connection = new PDO($dsn, $username, $password);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


            $sql = "SELECT p.*, sc.nom_sous_categorie 
                FROM PRODUIT p
                INNER JOIN SOUS_CATEGORIE sc 
                ON p.id_sous_categorie = sc.id_sous_categorie";

            $statement = $connection->prepare($sql);
            $statement->execute();
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