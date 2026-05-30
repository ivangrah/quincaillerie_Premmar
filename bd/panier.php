<?php
session_start();
include_once "config.php";

/* =======================
   AJOUT PRODUIT
======================= */
if(isset($_GET['id'])){

    $id = $_GET['id'];

    if(!isset($_SESSION['panier'])){
        $_SESSION['panier'] = [];
    }

    if(isset($_SESSION['panier'][$id])){
        $_SESSION['panier'][$id]++;
    } else {
        $_SESSION['panier'][$id] = 1;
    }

    header("Location: panier.php");
    exit;
}

/* =======================
   SUPPRESSION PRODUIT
======================= */
if(isset($_GET['remove'])){

    $id = $_GET['remove'];

    unset($_SESSION['panier'][$id]);

    header("Location: panier.php");
    exit;
}

/* =======================
   COMPTEUR PANIER
======================= */
$count = 0;

if(isset($_SESSION['panier'])){
    $count = array_sum($_SESSION['panier']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panier</title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="panier.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
</head>

<body>

<div style="font-family:'Poppins',sans-serif; background:#1a0a2e; min-height:120px;">

    <div class="pm-topbar">
        <div style="display:flex;gap:16px;align-items:center;">
            <span><i class="fa fa-map-marker-alt"></i> Abidjan, Côte d'Ivoire</span>
            <span style="color:rgba(243,161,37,0.3)">|</span>
            <span><i class="fa fa-phone"></i> +225 07 08 20 30 05</span>
        </div>
    </div>

    <div class="pm-header">

        <a class="pm-logo" href="#">
            <div class="pm-logo-circle">P</div>
            <div class="pm-logo-text">
                <span class="pm-logo-name">PREMMAR</span>
                <span class="pm-logo-sub">Boutiques</span>
            </div>
        </a>

        <div class="pm-divider"></div>

        <nav class="pm-nav" id="pmNav">
            <a href="" class="pm-active">Accueil</a>
            <a href="#">Catégories</a>
            <a href="#">Produits</a>
            <a href="#">Prestations</a>
        </nav>

        <div class="pm-actions">

            <div class="pm-search">
                <i class="fa fa-search"></i>
                <input type="text" placeholder="Rechercher…">
            </div>

            <div class="pm-icon-btn">
                <i class="fa fa-house"></i>
            </div>

            <div class="pm-icon-btn pm-cart">
                <a href="../client/produit/page/electricite/index.php">
                     <i class="fa fa-shopping-bag"></i>
                </a>
               
                <span class="pm-badge"><?= $count ?></span>
            </div>

            <div class="pm-burger" id="pmBurger">
                <span></span><span></span><span></span>
            </div>

        </div>
    </div>

    <div class="pm-goldline"></div>

    <div class="pm-banner">
        <span class="pm-banner-dot"></span>
        <span class="pm-banner-text">Bienvenue sur PREMMAR BOUTIQUES</span>
        <span class="pm-banner-dot"></span>
        <span class="pm-banner-text">Quincaillerie & Matériaux</span>
        <span class="pm-banner-dot"></span>
    </div>

</div>

  <!-- JAVASCRIPT -->
  

    <script>
const burger = document.getElementById('pmBurger');
const nav = document.getElementById('pmNav');

burger.addEventListener('click', () => {

    nav.classList.toggle('open');

    const spans = burger.querySelectorAll('span');

    if (nav.classList.contains('open')) {
        spans[0].style.transform = 'rotate(45deg) translate(5px,5px)';
        spans[1].style.opacity = '0';
        spans[2].style.transform = 'rotate(-45deg) translate(5px,-5px)';
    } else {
        spans.forEach(s => {
            s.style.transform = '';
            s.style.opacity = '';
        });
    }
});

  </script>


<?php
$total = 0;

if(isset($_SESSION['panier']) && !empty($_SESSION['panier'])){

    foreach($_SESSION['panier'] as $id_produit => $qty){

        $sql = "SELECT * FROM PRODUIT WHERE id_produit = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_produit]);
        $produit = $stmt->fetch(PDO::FETCH_ASSOC);

        $prixTotal = $produit['prix'] * $qty;
        $total += $prixTotal;
?>

 <div class="item">

    <img src="../client/produit/images/<?= htmlspecialchars($produit['dossier']) ?>/<?= htmlspecialchars($produit['image']) ?>"
     alt="<?= htmlspecialchars($produit['nom_produit']) ?>">

    <div class="item-content">

        <h3><?= htmlspecialchars($produit['nom_produit']) ?></h3>

        <p><?= htmlspecialchars($produit['description']) ?></p>

        <p>Prix unitaire : <?= htmlspecialchars($produit['prix']) ?> FCFA</p>
        <p>Quantité : <?= htmlspecialchars($qty) ?></p>
        <p><strong>Total : <?= htmlspecialchars($prixTotal) ?> FCFA</strong></p>

    </div>

    <div class="item-actions">

        <a class="remove" href="panier.php?remove=<?= $id_produit ?>">
             Supprimer
        </a>

        <a class="order" href="../client/produit/page/electricite/commande.php?id=<?= $id_produit ?>">
            Commander
        </a>

    </div>

</div>
<?php
    }

    echo "<h2>Total général : $total FCFA</h2>";

} else {
    echo "<p style='opacity: 0.3; text-align: center; margin-top: 50px; font-size:50px;'>Panier vide</p>";
    echo " <div>
    <i class='fa-solid fa-trash' id='panier-vide' style='font-size:200px;color:#ccc;'></i>
    </div> " ;
}

?>

</body>
</html>

<?php
$count = 0;

if(isset($_SESSION['panier'])){
    $count = array_sum($_SESSION['panier']);
}
?>

