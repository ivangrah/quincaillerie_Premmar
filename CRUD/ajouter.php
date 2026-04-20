<?php
include_once "../bd/config.php";

$success = null;
$error = null;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom         = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix        = (float)($_POST['prix'] ?? 0);
    $prix_gros   = (int)($_POST['prix_gros'] ?? 0);
    $quantite    = (int)($_POST['quantite'] ?? 0);
    $contenant   = trim($_POST['contenant'] ?? '');
    $id_categorie = (int)($_POST['id_categorie'] ?? 0);
    $id_sous_categorie = (int)($_POST['id_sous_categorie'] ?? 0);

    // Gestion de l'image
    $nom_image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $nom_image = strtolower(str_replace(' ', '_', $nom)) . '.' . $ext;

        // Dossier selon la catégorie
        $dossier = "client/produit/images/" . $_POST['nom_dossier'] . "/";
        if (!is_dir($dossier)) mkdir($dossier, 0755, true);

        move_uploaded_file($_FILES['image']['tmp_name'], $dossier . $nom_image);
    }

    // Insertion en BD
    try {
        $stmt = $pdo->prepare("
            INSERT INTO PRODUIT (nom_produit, description, prix, `prix-gros`, quantité, image, contenant, id_sous_categorie, id_categorie)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nom, $description, $prix, $prix_gros, $quantite, $nom_image, $contenant, $id_sous_categorie, $id_categorie]);
        $success = "Produit \"$nom\" ajouté avec succès !";
    } catch (PDOException $e) {
        $error = "Erreur BD : " . $e->getMessage();
    }
}

// Récupérer les catégories et sous-catégories pour les selects
$categories = $pdo->query("SELECT * FROM CATEGORIE")->fetchAll(PDO::FETCH_ASSOC);
$sous_categories = $pdo->query("SELECT * FROM SOUS_CATEGORIE")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un produit – PREMMAR Admin</title>
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
            color: #7c3aed;
            font-weight: 700;
        }

        .btn-primary {
            background: linear-gradient(135deg, #f97316, #7c3aed);
            border: none;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <h1 class="text-center mb-4">
                    <i class="fa-solid fa-plus-circle"></i> Ajouter un produit
                </h1>

                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fa-solid fa-check"></i> <?= $success ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?= $error ?></div>
                <?php endif; ?>

                <div class="card p-4">
                    <form method="POST" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label">Nom du produit</label>
                            <input type="text" name="nom" class="form-control" placeholder="Ex: Robinet de douche" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Description du produit"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Prix (FCFA)</label>
                                <input type="number" name="prix" class="form-control" placeholder="20000" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Prix gros (FCFA)</label>
                                <input type="number" name="prix_gros" class="form-control" placeholder="15000">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Quantité</label>
                                <input type="number" name="quantite" class="form-control" placeholder="10" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contenant</label>
                            <input type="text" name="contenant" class="form-control" placeholder="Ex: Boite, Sachet, Unité">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Catégorie</label>
                                <select name="id_categorie" class="form-select" required>
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id_categorie'] ?>">
                                            <?= htmlspecialchars($cat['nom_categorie']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sous-catégorie</label>
                                <select name="id_sous_categorie" class="form-select" required>
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($sous_categories as $sc): ?>
                                        <option value="<?= $sc['id_sous_categorie'] ?>">
                                            <?= htmlspecialchars($sc['nom_sous_categorie']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dossier image (nom du dossier dans client/produit/images/)</label>
                            <input type="text" name="nom_dossier" class="form-control" placeholder="Ex: plomberie">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Image du produit</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 text-white fw-bold">
                            <i class="fa-solid fa-floppy-disk"></i> Enregistrer le produit
                        </button>

                    </form>
                </div>

            </div>
        </div>
    </div>
</body>

</html>