<?php
require_once('fonction.php');
$connexion = connexionBD() ;


$mysqli = new mysqli('localhost', 'root', '', 'db_courrier');

// Vérifier la connexion
if ($mysqli->connect_error) {
    die("Échec de la connexion : " . $mysqli->connect_error);
}

// Préparer la requête SQL
$query = "
    SELECT
        c.id_courrier,
        c.Numero_Courrier,
        c.Date,
        c.Objet,
        c.pdf,
        i.Instruction,
        i.departement,
        i.date_impu,
        
    FROM
        courrier c
    JOIN
        imputation i ON c.id_courrier = i.id_courrier
        GROUP BY c.Numero_Courrier
";

// Exécuter la requête
if ($result = $mysqli->query($query)) {
    // Vérifier s'il y a des résultats
    if ($result->num_rows > 0) {
        // Afficher les résultats
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
        echo "Aucun courrier imputé trouvé.";
    }

    // Libérer les résultats
    $result->free();
} else {
    echo "Erreur dans la requête : " . $mysqli->error;
}