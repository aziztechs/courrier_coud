<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    exit();
}
//connexion à la base de données
require('../../traitement/fonction.php');
// Sélectionnez les options à partir de la base de données avec une pagination
require('../../traitement/requete.php');
$departement = isset($_GET['departement']) ? $_GET['departement'] : '';

// Préparer la requête SQL pour récupérer les courriers imputés pour le département spécifique
$query = "
    SELECT
        c.id_courrier,
        c.Numero_Courrier,
        c.Date,
        c.Objet,
        c.pdf,
        i.Instruction,
        i.departement
    FROM
        courrier c
    JOIN
        imputation i ON c.id_courrier = i.id_courrier
    WHERE
        i.departement = ?
    ORDER BY
        c.Date DESC
";

// Préparer et exécuter la requête avec filtre
$stmt = $connexion->prepare($query);
$stmt->bind_param("s", $departement);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: GESTION_PANNES</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/base.css" />
    <link rel="stylesheet" href="../../assets/css/vendor.css" />
    <link rel="stylesheet" href="../../assets/css/main.css" />
    <link rel="stylesheet" href="../../assets/css/login.css" />
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/bootstrap/js/bootstrap.min.js">
    <link rel="stylesheet" href="../../assets/bootstrap/js/bootstrap.bundle.min.js">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <?php include('../../head.php');?>
    <div class="container">
        <h1>Courriers Imputés pour Département: <?php echo htmlspecialchars($departement); ?></h1>
       
        <form action="" method="get">
            <div id="sections">
                <div class="section">
                    <!-- <p><strong>Section 1 : Collecte des données</strong></p> -->
                    <p>Description de la collecte des données...</p>
                   

    <?php
    // Afficher les résultats de la requête
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "ID Courrier: " . htmlspecialchars($row['id_courrier']) . "<br>";
            echo "Numéro Courrier: " . htmlspecialchars($row['Numero_Courrier']) . "<br>";
            echo "Date: " . htmlspecialchars($row['Date']) . "<br>";
            echo "Objet: " . htmlspecialchars($row['Objet']) . "<br>";
            echo "PDF: <a href='" . htmlspecialchars($row['pdf']) . "'>Voir PDF</a><br>";
            echo "Instruction: " . htmlspecialchars($row['Instruction']) . "<br>";
            echo "Département: " . htmlspecialchars($row['departement']) . "<br><br>";
        }
    } else {
        echo "Aucun courrier imputé trouvé pour le département sélectionné.";
    }

    // Libérer les résultats
    $result->free();
    $stmt->close();
    
    ?>
                  
        </form>
    </div>
    <script src="../../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../../assets/js/plugins.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>

</html>