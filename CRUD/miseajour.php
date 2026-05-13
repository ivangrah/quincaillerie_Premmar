<?php
include_once "../bd/config.php";

$success = null;
$error = null;

// 1. Récupérer le produit à modifier
$id_produit = (int)($_GET['id'] ?? 0);

if ($id_produit === 0) {
    header("Location: liste.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM PRODUIT WHERE id_produit = ?");
$stmt->execute([$id_produit]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    die("Produit introuvable.");
}

// 2. Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom               = trim($_POST['nom'] ?? '');
    $description       = trim($_POST['description'] ?? '');
    $prix              = (float)($_POST['prix'] ?? 0);
    $prix_gros         = (int)($_POST['prix_gros'] ?? 0);
    $quantite          = (int)($_POST['quantite'] ?? 0);
    $contenant         = trim($_POST['contenant'] ?? '');
    $id_categorie      = (int)($_POST['id_categorie'] ?? 0);
    $id_sous_categorie = (int)($_POST['id_sous_categorie'] ?? 0);

    // Valeur par défaut : on garde l'image actuelle définie en BD
    $nom_image = $produit['image'];
    $upload_ok = true;

    // 3. Gestion de l'image (seulement si un nouveau fichier est envoyé)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

        // Récupérer la catégorie pour le dossier
        $stmt_cat = $pdo->prepare("SELECT nom_categorie FROM CATEGORIE WHERE id_categorie = ?");
        $stmt_cat->execute([$id_categorie]);
        $cat = $stmt_cat->fetch(PDO::FETCH_ASSOC);

        // Nettoyage sécurisé du nom de dossier
        $nom_dossier = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $cat['nom_categorie'] ?? 'divers'));

        // Nettoyage sécurisé du nom de fichier
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $nom_image = strtolower(preg_replace('/[^A-Za-z0-9]/', '_', $nom)) . '_' . time() . '.' . $ext;

        // Chemin physique absolu pour Linux/XAMPP
        $chemin_relatif = "/projet_quincaillerie/client/produit/images/" . $nom_dossier . "/";
        $dossier_physique = $_SERVER['DOCUMENT_ROOT'] . $chemin_relatif;

        // Création du dossier si inexistant
        if (!is_dir($dossier_physique)) {
            if (!mkdir($dossier_physique, 0755, true)) {
                $error = "Erreur : Impossible de créer le dossier de destination.";
                $upload_ok = false;
            }
        }

        // Déplacement du fichier
        if ($upload_ok) {
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dossier_physique . $nom_image)) {
                $error = "L'image n'a pas pu être déplacée. Vérifiez les permissions du dossier.";
                $upload_ok = false;
            }
        }
    }

    // 4. Mise à jour en BD seulement si l'upload est validé
    if ($upload_ok) {
        try {
            $stmt = $pdo->prepare("
                UPDATE PRODUIT 
                SET nom_produit = ?, description = ?, prix = ?, `prix-gros` = ?, quantité = ?, 
                    image = ?, contenant = ?, id_sous_categorie = ?, id_categorie = ?
                WHERE id_produit = ?
            ");
            $stmt->execute([
                $nom,
                $description,
                $prix,
                $prix_gros,
                $quantite,
                $nom_image,
                $contenant,
                $id_sous_categorie,
                $id_categorie,
                $id_produit
            ]);

            // Mise à jour synchrone du prix (si ta table type_produit existe toujours)
            $pdo->prepare("UPDATE type_produit SET prix = ?, prix_gros = ? WHERE id_produit = ?")
                ->execute([$prix, $prix_gros, $id_produit]);

            $success = "Produit \"$nom\" mis à jour avec succès !";

            // Rafraîchir les données pour l'affichage
            $stmt2 = $pdo->prepare("SELECT * FROM PRODUIT WHERE id_produit = ?");
            $stmt2->execute([$id_produit]);
            $produit = $stmt2->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = "Erreur BD : " . $e->getMessage();
        }
    }
}

$categories      = $pdo->query("SELECT * FROM CATEGORIE")->fetchAll(PDO::FETCH_ASSOC);
$sous_categories = $pdo->query("SELECT * FROM SOUS_CATEGORIE")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un produit – PREMMAR Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f4f7f6;
        }

        .card {
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        h1 {
            color: #f97316;
            font-weight: 700;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f97316, #fbbf24);
            border: none;
            color: white !important;
        }

        .preview-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="text-center mb-4"><i class="fa-solid fa-pen-to-square"></i> Modifier un produit</h1>

                <?php if ($success): ?>
                    <div class="alert alert-success"> <?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"> <?= $error ?></div>
                <?php endif; ?>

                <div class="card p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nom du produit</label>
                            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($produit['nom_produit']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($produit['description']) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Prix (FCFA)</label>
                                <input type="number" name="prix" class="form-control" value="<?= $produit['prix'] ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Prix gros</label>
                                <input type="number" name="prix_gros" class="form-control" value="<?= $produit['prix-gros'] ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Quantité</label>
                                <input type="number" name="quantite" class="form-control" value="<?= $produit['quantité'] ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Catégorie</label>
                                <select name="id_categorie" class="form-select" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id_categorie'] ?>" <?= $cat['id_categorie'] == $produit['id_categorie'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['nom_categorie']) ?>
                                        </option>
                                    <?php endforeach; ?>$
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Sous-catégorie</label>
                                <select name="id_sous_categorie" class="form-select" required>
                                    <?php foreach ($sous_categories as $sc): ?>
                                        <option value="<?= $sc['id_sous_categorie'] ?>" <?= $sc['id_sous_categorie'] == $produit['id_sous_categorie'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sc['nom_sous_categorie']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Image du produit</label>
                            <div class="d-flex align-items-center gap-3 mb-3 p-2 bg-light rounded">
                                <?php
                                // Déterminer le dossier actuel pour l'affichage de l'ancienne image
                                $stmt_c = $pdo->prepare("SELECT nom_categorie FROM CATEGORIE WHERE id_categorie = ?");
                                $stmt_c->execute([$produit['id_categorie']]);
                                $cat_info = $stmt_c->fetch();
                                $folder_name = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $cat_info['nom_categorie'] ?? 'divers'));
                                $img_src = "/projet_quincaillerie/client/produit/images/$folder_name/" . $produit['image'];
                                ?>
                                <img src="<?= $img_src ?>" class="preview-img" alt="Actuelle" onerror="this.src='https://placehold.co/80?text=Pas+d\'image'">
                                <div>
                                    <small class="text-muted d-block">Image actuelle :</small>
                                    <code class="small"><?= htmlspecialchars($produit['image']) ?></code>
                                </div>
                            </div>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div class="form-text">Sélectionnez un fichier uniquement si vous souhaitez changer l'image.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning w-100 py-2 fw-bold">
                                <i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications
                            </button>
                            <a href="liste.php" class="btn btn-secondary w-100 py-2 fw-bold">Retour</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>