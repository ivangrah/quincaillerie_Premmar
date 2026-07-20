<?php

include_once 'config.php';

$success = null;

try {
    $connection = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT * FROM CATEGORIE";
    $statement = $connection->prepare($sql);
    $statement->execute();
    $result = $statement->fetchAll();
} catch (PDOException $error) {
    die("Erreur : " . $error->getMessage());
}

?>

<?php include 'header.php'; ?>

<div class="cat-grid">
<?php foreach ($result as $row) : ?>
    <a href="/projet_quincaillerie/<?= htmlspecialchars($row['lien']) ?>" class="cat-card-link">
        <div class="cat-card">
            <div class="cat-image-wrapper">
                <img src="../bd/catheimage/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['nom_categorie']) ?>">
            </div>
            <h3><?= htmlspecialchars($row['nom_categorie']) ?></h3>
        </div>
    </a>
<?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>

<style>
    body {
        background: #1a0a2e;
        font-family: 'Poppins', sans-serif;
        color: #f5f0ff;
    }

    .cat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 50px auto;
        padding: 0 24px;
    }

    .cat-card-link {
        text-decoration: none;
        color: inherit;
    }

    .cat-card {
        background: #2a0f40;
        border: 1px solid rgba(243, 161, 37, 0.15);
        border-radius: 16px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), border-color 0.3s, box-shadow 0.3s;
        height: 100%;
    }

    .cat-card:hover {
        transform: translateY(-8px);
        border-color: rgba(243, 161, 37, 0.6);
        box-shadow: 0 15px 35px rgba(243, 161, 37, 0.15);
    }

    .cat-image-wrapper {
        width: 100%;
        height: 200px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid rgba(243, 161, 37, 0.2);
        transition: border-color 0.3s;
    }

    .cat-card:hover .cat-image-wrapper {
        border-color: rgba(243, 161, 37, 0.5);
    }

    .cat-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .cat-card:hover img {
        transform: scale(1.06);
    }

    .cat-card h3 {
        margin-top: 18px;
        font-size: 1.15rem;
        color: #f3a125;
        font-weight: 700;
        text-align: center;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        transition: color 0.3s;
    }

    .cat-card:hover h3 {
        color: #f7bc5e;
    }

    @media (max-width: 768px) {
        .cat-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 30px auto;
            padding: 0 16px;
        }
        
        .cat-image-wrapper {
            height: 160px;
        }
        
        .cat-card h3 {
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .cat-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        
        .cat-image-wrapper {
            height: 200px;
        }
    }
</style>

