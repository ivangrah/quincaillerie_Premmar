<?php

include_once "../bd/config.php";


session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: liste.php');
    exit;
}


// Message de suppression réussie
$supprime = isset($_GET['supprime']) && $_GET['supprime'] == 1;

// Récupérer tous les produits avec leur catégorie et sous-catégorie
$stmt = $pdo->query("
    SELECT p.*, c.nom_categorie, sc.nom_sous_categorie
    FROM PRODUIT p
    LEFT JOIN CATEGORIE c ON p.id_categorie = c.id_categorie
    LEFT JOIN SOUS_CATEGORIE sc ON p.id_sous_categorie = sc.id_sous_categorie
    ORDER BY p.id_categorie, p.nom_produit
");
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des produits – PREMMAR Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f4f7f6;
            font-family: 'Segoe UI', sans-serif;
        }

        h1 {
            color: #7c3aed;
            font-weight: 700;
        }

        .table thead {
            background: linear-gradient(135deg, #f97316, #7c3aed);
            color: white;
        }

        .table tbody tr:hover {
            background: #faf5ff;
        }

        .badge-cat {
            background: #ede9fe;
            color: #7c3aed;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.8rem;
        }

        .badge-sous {
            background: #ffedd5;
            color: #f97316;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.8rem;
        }

        .btn-ajouter {
            background: linear-gradient(135deg, #f97316, #7c3aed);
            border: none;
        }

        .card {
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .produit-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="container py-5">

        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fa-solid fa-box"></i> Gestion des produits</h1>
            <a href="ajouter.php" class="btn btn-ajouter text-white fw-bold px-4 py-2">
                <i class="fa-solid fa-plus"></i> Ajouter un produit
            </a>
        </div>

        <!-- Message suppression -->
        <?php if ($supprime): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fa-solid fa-check"></i> Produit supprimé avec succès.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats rapides -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card p-3 text-center">
                    <h2 class="text-violet mb-0" style="color:#7c3aed;"><?= count($produits) ?></h2>
                    <p class="text-muted mb-0">Total produits</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 text-center">
                    <h2 class="mb-0" style="color:#f97316;">
                        <?= count(array_unique(array_column($produits, 'id_categorie'))) ?>
                    </h2>
                    <p class="text-muted mb-0">Catégories</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 text-center">
                    <h2 class="mb-0" style="color:#16a34a;">
                        <?= count(array_filter($produits, fn($p) => $p['quantité'] > 0)) ?>
                    </h2>
                    <p class="text-muted mb-0">En stock</p>
                </div>
            </div>
        </div>

        <!-- Recherche rapide -->
        <div class="mb-3">
            <input type="text" id="recherche" class="form-control" placeholder="🔍 Rechercher un produit...">
        </div>

        <!-- Tableau -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tableau-produits">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Sous-catégorie</th>
                            <th>Prix</th>
                            <th>Qté</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($produits)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-box-open"></i> Aucun produit trouvé.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($produits as $p): ?>
                                <tr>
                                    <td><?= $p['id_produit'] ?></td>
                                    <td>
                                        <?php if ($p['image']): ?>
                                            <img src="../client/produit/images/<?= htmlspecialchars(strtolower($p['nom_categorie'])) ?>/<?= htmlspecialchars($p['image']) ?>"
                                                class="produit-img"
                                                onerror="this.src='https://via.placeholder.com/50'">
                                        <?php else: ?>
                                            <div class="produit-img bg-light d-flex align-items-center justify-content-center">
                                                <i class="fa-solid fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($p['nom_produit']) ?></strong></td>
                                    <td><span class="badge-cat"><?= htmlspecialchars($p['nom_categorie'] ?? 'N/A') ?></span></td>
                                    <td><span class="badge-sous"><?= htmlspecialchars($p['nom_sous_categorie'] ?? 'N/A') ?></span></td>
                                    <td><?= number_format($p['prix'], 0, ',', ' ') ?> FCFA</td>
                                    <td>
                                        <span class="<?= $p['quantité'] > 0 ? 'text-success' : 'text-danger' ?> fw-bold">
                                            <?= $p['quantité'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/projet_quincaillerie/CRUD/miseajour.php?id=<?= $p['id_produit'] ?>"
                                            class="btn btn-warning btn-sm text-white me-1">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="/projet_quincaillerie/CRUD/supprimer.php?id=<?= $p['id_produit'] ?>"
                                            class="btn btn-danger btn-sm">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Recherche en temps réel
        document.getElementById('recherche').addEventListener('input', function() {
            const val = this.value.toLowerCase();
            document.querySelectorAll('#tableau-produits tbody tr').forEach(tr => {
                tr.style.display = tr.textContent.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    </script>
</body>

</html>