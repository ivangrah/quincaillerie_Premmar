<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- FONT AWESOME -->
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- CSS -->
  <link rel="stylesheet" href="style.css">

  <title>PREMMAR</title>
</head>

<body>

  <div style="font-family:'Poppins',sans-serif; background:#1a0a2e; min-height:120px;">

    <!-- TOPBAR -->
    <div class="pm-topbar">

      <div style="display:flex;gap:16px;align-items:center;">

        <span>
          <i class="fa fa-map-marker-alt"></i>
          Abidjan, Côte d'Ivoire
        </span>

        <span style="color:rgba(243,161,37,0.3)">|</span>

        <span>
          <i class="fa fa-phone"></i>
          +225 07 08 20 30 05
        </span>

      </div>

    </div>

    <!-- HEADER -->
    <div class="pm-header">

      <!-- LOGO -->
      <a class="pm-logo" href="#">

        <div class="pm-logo-circle">P</div>

        <div class="pm-logo-text">
          <span class="pm-logo-name">PREMMAR</span>
          <span class="pm-logo-sub">Boutiques</span>
        </div>

      </a>

      <div class="pm-divider"></div>

      <!-- NAVIGATION -->
      <nav class="pm-nav" id="pmNav">

        <a href="../client/page-accueil/index.php" >Accueil</a>

        <a href="cathegorie.php" class="pm-active">Catégories</a>

        <a href="#">Produits</a>

        <a href="#">Nos Prestations</a>

      </nav>

      <!-- ACTIONS -->
      <div class="pm-actions">

        <!-- SEARCH -->
        <div class="pm-search">

          <i class="fa fa-search"></i>

          <input type="text" placeholder="Rechercher…">

        </div>

        <!-- FAVORIS -->
        <div class="pm-icon-btn">
       <a href="../client/page-accueil/index.php">
             <i class="fa fa-house"></i>
        </a>
         

        </div>

        <!-- PANIER -->
        <div class="pm-icon-btn pm-cart">

         

          <a href="panier.php" style="text-decoration: none; ">
             <i class="fa fa-shopping-cart"></i>
               <span class="pm-badge">3</span>
          </a>
        

        </div>

        <!-- MENU BURGER -->
        <div class="pm-burger" id="pmBurger">

          <span></span>
          <span></span>
          <span></span>

        </div>

      </div>

    </div>

    <!-- LIGNE DORÉE -->
    <div class="pm-goldline"></div>

    <!-- BANNER -->
    <div class="pm-banner">

      <span class="pm-banner-dot"></span>

      <span class="pm-banner-text">
        Bienvenue sur PREMMAR BOUTIQUES
      </span>

      <span class="pm-banner-dot"></span>

      <span class="pm-banner-text">
        Quincaillerie &amp; Matériaux de Construction
      </span>

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

        spans[0].style.transform =
          'rotate(45deg) translate(5px,5px)';

        spans[1].style.opacity = '0';

        spans[2].style.transform =
          'rotate(-45deg) translate(5px,-5px)';

      } else {

        spans[0].style.transform = '';

        spans[1].style.opacity = '';

        spans[2].style.transform = '';

      }

    });

  </script>

</body>

</html>