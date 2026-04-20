<?php
include_once "../bd/config.php";

$id_produit = (int)($_GET['id'] ?? 0);

if ($id_produit === 0) {
    die("Produit non spécifié.");
}

// Récupérer le produit avant suppression
$stmt = $pdo->prepare("SELECT * FROM PRODUIT WHERE id_produit = ?");
$stmt->execute([$id_produit]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    die("Produit introuvable.");
}

// Confirmation de suppression
if (isset($_POST['confirmer'])) {
    try {
        $pdo->prepare("DELETE FROM PRODUIT WHERE id_produit = ?")->execute([$id_produit]);
        header("Location: liste_produits.php?supprime=1");
        exit;
    } catch (PDOException $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un produit – PREMMAR Admin</title>
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
            color: #dc2626;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <h1 class="text-center mb-4">
                    <i class="fa-solid fa-trash"></i> Supprimer un produit
                </h1>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <div class="card p-4 text-center">

                    <div class="mb-4">
                        <i class="fa-solid fa-triangle-exclamation text-warning" style="font-size:3rem;"></i>
                        <h4 class="mt-3">Êtes-vous sûr de vouloir supprimer ce produit ?</h4>
                        <p class="text-muted">Cette action est irréversible.</p>
                    </div>

                    <div class="alert alert-light border text-start">
                        <p><strong>Nom :</strong> <?= htmlspecialchars($produit['nom_produit']) ?></p>
                        <p><strong>Prix :</strong> <?= number_format($produit['prix'], 0, ',', ' ') ?> FCFA</p>
                        <p><strong>Quantité :</strong> <?= $produit['quantité'] ?></p>
                        <p class="mb-0"><strong>Description :</strong> <?= htmlspecialchars($produit['description'] ?? 'N/A') ?></p>
                    </div>

                    <form method="POST" class="d-flex gap-2 mt-3">
                        <button type="submit" name="confirmer" class="btn btn-danger w-100 py-2 fw-bold">
                            <i class="fa-solid fa-trash"></i> Oui, supprimer
                        </button>
                        <a href="liste.php" class="btn btn-secondary w-100 py-2 fw-bold">
                            <i class="fa-solid fa-arrow-left"></i> Annuler
                        </a>
                    </form>

                </div>
            </div>
        </div>
    </div>
</body>

</html>