<?php
ob_start();
include_once "../../../../bd/config.php";

$id_produit = 0;
if (isset($_POST['id_produit'])) {
    $id_produit = (int)$_POST['id_produit'];
} elseif (isset($_GET['id'])) {
    $id_produit = (int)$_GET['id'];
}

if ($id_produit === 0) {
    die("<p style='color:red'>Produit introuvable.</p>");
}

// ✅ On utilise directement $pdo fourni par config.php — pas de double connexion
try {
    $sql = "SELECT p.*, sc.nom_sous_categorie
            FROM PRODUIT p
            INNER JOIN SOUS_CATEGORIE sc 
            ON p.id_sous_categorie = sc.id_sous_categorie
            WHERE p.id_produit = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_produit]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produit)
        die("<p style='color:red'>Produit introuvable.</p>");

    $sql2 = "SELECT * FROM type_produit WHERE id_produit = :id";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([':id' => $id_produit]);
    $types = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $error) {
    echo "<pre style='color:red'>ERREUR : " . htmlspecialchars($error->getMessage()) . "</pre>";
    die();
}

// ✅ FIX Bug 2 : Calcul du montant initial côté PHP pour initialiser le champ caché
$prix_initial    = count($types) > 0 ? (float)$types[0]['prix'] : (float)$produit['prix'];
$frais_initial   = ($prix_initial > 0 && $prix_initial <= 5000) ? 2000 : ($prix_initial > 5000 ? 5000 : 0);
$montant_initial = $prix_initial + $frais_initial;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande — <?= htmlspecialchars($produit['nom_produit']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="commande.css">
</head>

<body>

    <!-- ══════════ NAVBAR ══════════ -->
    <nav class="navbar">
        <a href="../../../../index.php" class="navbar-brand">
            <div class="navbar-logo-icon">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <span class="navbar-name">PREMMAR</span>
        </a>

        <ul class="navbar-links">
            <li><a href="../../../../index.php"><i class="fa-solid fa-house"></i> Accueil</a></li>
            <li><a href="../electricite/index.php"><i class="fa-solid fa-plug"></i>electricite</a></li>
            <li><a href="#" class="active"><i class="fa-solid fa-cart-shopping"></i> Commande</a></li>
        </ul>

        <button class="hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <!--══════════ MENU MOBILE ══════════-->
    <div class="nav-mobile" id="navMobile">
        <a href="../../../../index.php"><i class="fa-solid fa-house"></i> Accueil</a>
        <a href="../electricite/index.php"><i class="fa-solid fa-plug"></i>electricite</a>
        <a href="#" class="active"><i class="fa-solid fa-cart-shopping"></i> Commande</a>
    </div>

    <!-- ══════════ FORMULAIRE SPLIT ══════════ -->
    <form method="POST" action="tratement.php" id="commandeForm">
        <div class="split-wrapper">

            <!-- ─── PANNEAU GAUCHE : Résumé produit ─── -->
            <div class="panel-left">

                <!-- Tag catégorie -->
                <span class="prod-tag">
                    <i class="fa-solid fa-bolt fa-xs"></i>
                    <?= htmlspecialchars($produit['nom_sous_categorie']) ?>
                </span>

                <!-- Image produit -->
                <div class="prod-image-wrap">
                    <img src="../../../produit/images/electricite/<?= htmlspecialchars($produit['image']) ?>"
                        alt="<?= htmlspecialchars($produit['nom_produit']) ?>">
                </div>

                <!-- Nom produit -->
                <p class="prod-title"><?= htmlspecialchars($produit['nom_produit']) ?></p>
                <p class="prod-sub">Sélectionner un type</p>

                <hr class="sep">

                <!-- Types de produit -->
                <span class="types-label">Type de produit</span>

                <?php foreach ($types as $key => $type): ?>
                    <div class="type">
                        <label>
                            <?= htmlspecialchars($type['nom_type']) ?>
                            <small>
                                Détail : <?= number_format($type['prix'], 0, ',', ' ') ?> FCFA
                                &nbsp;·&nbsp;
                                Gros : <?= number_format($type['prix_gros'], 0, ',', ' ') ?> FCFA
                            </small>
                        </label>
                        <input type="radio"
                            name="type"
                            value="<?= (int)$type['id_type'] ?>"
                            data-prix="<?= (float)$type['prix'] ?>"
                            <?= $key === 0 ? 'checked' : '' ?>>
                    </div>
                <?php endforeach; ?>

                <hr class="sep">

                <!-- Quantité -->
                <div class="qty-row">
                    <span class="qty-label">Quantité</span>
                    <div class="qty-controls">
                        <button type="button" class="moins">−</button>
                        <span id="quantite">1</span>
                        <button type="button" class="plus">+</button>
                    </div>
                </div>

                <!-- ✅ FIX Bug 1 : Affichage initial vide, le JS recalcule immédiatement -->
                <div class="prix-total-wrap">
                    <span class="prix-total-label">Total</span>
                    <span id="prix_total">Chargement…</span>
                </div>

                <!-- Champs cachés -->
                <input type="hidden" name="id_produit" value="<?= (int)$produit['id_produit'] ?>">
                <input type="hidden" name="quantite" id="quantite_hidden" value="1">
                <!-- ✅ FIX Bug 2 : Valeur initiale calculée côté PHP, plus de "0" envoyé -->
                <input type="hidden" id="prix_total_hidden" name="prix_total_final" value="<?= $montant_initial ?>">

                <!-- Bouton confirmer -->
                <div class="btn">
                    <button class="bt" type="submit">
                        <i class="fa-solid fa-check"></i> Confirmer la commande
                    </button>
                </div>

            </div><!-- /panel-left -->

            <!-- ─── PANNEAU DROIT : Formulaire ─── -->
            <div class="panel-right">

                <div class="form-heading">
                    <span class="step-tag">Finaliser votre commande</span>
                    <h2>Vos informations</h2>
                </div>

                <!-- Section 1 : Identité -->
                <div class="form-section">
                    <div class="section-title">
                        <div class="section-num">1</div>
                        <h3>Identité</h3>
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div class="fields-grid">
                        <div class="flex-input field-full">
                            <label>Nom complet</label>
                            <input type="text" id="nom" name="nom" placeholder="Ex : Konan Yao Marc" required>
                        </div>
                        <div class="flex-input">
                            <label>Email</label>
                            <input type="email" id="email" name="email" placeholder="vous@exemple.com" required>
                        </div>
                        <div class="flex-input">
                            <label>Téléphone</label>
                            <input type="tel" id="tel" name="telephone" placeholder="+225 07 00 00 00" required>
                        </div>
                    </div>
                </div>

                <!-- Section 2 : Adresse -->
                <div class="form-section">
                    <div class="section-title">
                        <div class="section-num">2</div>
                        <h3>Adresse de livraison</h3>
                        <i class="fa-solid fa-location-dot"></i>
                    </div>

                    <div class="fields-grid">
                        <div class="flex-input field-full">
                            <label>Adresse</label>
                            <input type="text" name="adresse" placeholder="Rue, quartier, commune…" required>
                        </div>
                        <div class="flex-input">
                            <label>Ville</label>
                            <input type="text" name="ville" placeholder="Abidjan">
                        </div>
                        <div class="flex-input">
                            <label>Code postal</label>
                            <input type="number" name="code_postal" placeholder="00000">
                        </div>
                    </div>
                </div>

                <!-- Section 3 : Paiement -->
                <div class="form-section">
                    <div class="section-title">
                        <div class="section-num">3</div>
                        <h3>Mode de paiement</h3>
                        <i class="fa-solid fa-credit-card"></i>
                    </div>

                    <div class="payment-grid">

                        <label class="pay-option">
                            <input type="radio" name="mode_paiement" value="geniuspay" required>
                            <div class="pay-icon"><i class="fa-solid fa-wallet"></i></div>
                            <div class="pay-info">
                                <strong>Paiement avant livraison</strong>
                                <small>Via GeniusPay — Orange Money, MTN, Wave, Moov</small>
                            </div>
                        </label>

                        <label class="pay-option">
                            <input type="radio" name="mode_paiement" value="apres_livraison">
                            <div class="pay-icon"><i class="fa-solid fa-truck"></i></div>
                            <div class="pay-info">
                                <strong>Paiement à la livraison</strong>
                                <small>Règlement en espèces à la réception</small>
                            </div>
                        </label>

                        <label class="pay-option">
                            <!-- ✅ FIX Bug 3 : on écoute le changement de mode pour recalculer les frais -->
                            <input type="radio" name="mode_paiement" value="en_boutique">
                            <div class="pay-icon"><i class="fa-solid fa-store"></i></div>
                            <div class="pay-info">
                                <strong>Paiement en boutique</strong>
                                <small>Venez régler directement en magasin</small>
                                <small>ADRESSE DU MAGASIN : ANGRE DJOROGOBITE 2 PRES
                                    DU PETIT MARCHE</small>
                            </div>
                        </label>

                    </div>
                </div>

            </div><!-- /panel-right -->

        </div><!-- /split-wrapper -->
    </form>

    <!-- ══════════ SCRIPTS ══════════ -->
    <script>
        // ── Hamburger ──
        const hamburger = document.getElementById('hamburger');
        const navMobile = document.getElementById('navMobile');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('open');
            navMobile.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navMobile.contains(e.target)) {
                hamburger.classList.remove('open');
                navMobile.classList.remove('open');
            }
        });

        // ── Prix / Quantité ──
        let quantite = 1;
        let prixUnitaire = <?= count($types) > 0 ? (float)$types[0]['prix'] : (float)$produit['prix'] ?>;
        const FRAIS_LIVRAISON = 2000;
        const FRAIS_MAXIMUM = 5000;

        const quantiteSpan = document.getElementById('quantite');
        const quantiteHidden = document.getElementById('quantite_hidden');
        const prixTotalSpan = document.getElementById('prix_total');

        // ✅ FIX Bug 3 : fonction updatePrix tient compte du mode de paiement
        function updatePrix() {
            const totalProduits = parseFloat(prixUnitaire) * parseInt(quantite);
            const modeSelectionne = document.querySelector('input[name="mode_paiement"]:checked')?.value;

            let frais = 0;

            // Frais de livraison uniquement si paiement avant ou après livraison (pas en boutique)
            if (modeSelectionne !== 'en_boutique') {
                if (totalProduits > 0 && totalProduits <= 5000) {
                    frais = FRAIS_LIVRAISON; // 2 000 FCFA
                } else if (totalProduits > 5000) {
                    frais = FRAIS_MAXIMUM; // 5 000 FCFA
                }
            }

            const totalFinal = totalProduits + frais;

            // ✅ FIX Bug 1 : affichage toujours mis à jour par le JS
            prixTotalSpan.textContent = new Intl.NumberFormat('fr-FR').format(totalFinal) + " FCFA";

            // ✅ FIX Bug 2 : champ caché toujours synchronisé avec la vraie valeur
            document.getElementById('prix_total_hidden').value = totalFinal;
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

        document.querySelectorAll('input[name="type"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                prixUnitaire = parseFloat(e.target.dataset.prix);
                updatePrix();
            });
        });

        // ✅ FIX Bug 3 : recalcul quand le mode de paiement change
        document.querySelectorAll('input[name="mode_paiement"]').forEach(radio => {
            radio.addEventListener('change', updatePrix);
        });

        // ── Validation ──
        document.getElementById('commandeForm').addEventListener('submit', function(e) {
            const mode = document.querySelector('input[name="mode_paiement"]:checked');
            if (!mode) {
                e.preventDefault();
                alert("Veuillez choisir un mode de paiement.");
            }
        });

        // ✅ Appel au chargement de la page pour afficher le bon montant dès le début
        updatePrix();
    </script>

</body>

</html>