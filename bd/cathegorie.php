<?php
require_once 'config.php';



$success = null;

try {
    $connection = new PDO($dsn, $username, $password);
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
<?php include 'cathe.css'; ?>

<?php foreach ($result as $row) : ?>




    <div class="position">

        <div class="encd">
            <img src="../bd/catheimage/<?php echo htmlspecialchars($row["image"]); ?>">
            <h3><?php echo htmlspecialchars($row["nom_categorie"]); ?></h3>

        </div>



    </div>








<?php endforeach; ?>



<?php include 'footer.php'; ?>

<style>
    /* Reset simple */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Image responsive */
    img {
        width: 100%;
        /* prend toute la largeur disponible */
        max-width: 500px;
        /* mais ne dépasse pas 500px */
        height: auto;
        /* garde les proportions */
        border-radius: 20px;
        margin-top: 50px;
        transition: transform 0.3s ease;
    }

    /* Hover */
    img:hover {
        transform: scale(1.06);
        cursor: pointer;
    }

    /* Conteneur principal */
    .position {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 50px;
        padding: 10px;
    }

    /* Titre */
    h3 {
        margin-top: 20px;
        font-size: 2rem;
        color: #441b5f;
        text-shadow: 1px 0px 1px #6e5d42;
        font-weight: bold;
        text-align: center;
    }

    /* Carte */
    .encd {
        display: flex;
        flex-direction: column;
        align-items: center;
        background-color: #afafaf;
        padding: 20px;
        border-radius: 20px;
        box-shadow: 0 4px 8px rgba(26, 25, 25, 0.1);
        width: 90%;
        /* s'adapte sur mobile */
        max-width: 600px;
        /* limite sur grand écran */
    }

    /* 📱 Responsive pour petits écrans */
    @media (max-width: 768px) {

        img {
            margin-top: 30px;
        }

        h3 {
            font-size: 1.5rem;
        }

        .position {
            margin-top: 30px;
        }

    }

    /* 📱 Très petits écrans */
    @media (max-width: 480px) {

        h3 {
            font-size: 1.3rem;
        }

        .encd {
            padding: 15px;
        }

    }
</style>