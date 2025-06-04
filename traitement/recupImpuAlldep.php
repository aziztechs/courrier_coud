<?php

require_once('fonction.php');
$connexion = connexionBD() ;


$mysqli = new mysqli('localhost', 'root', '', 'db_courrier');

// Vérifier la connexion
if ($mysqli->connect_error) {
    die("Échec de la connexion : " . $mysqli->connect_error);
}

if(isset($_GET['id_courrierA'])) {
$id_courrier = $_GET['id_courrierA']; // Assurez-vous de valider et de nettoyer cet input
}
// Requête pour récupérer toutes les imputations de ce courrier
$query = "
    SELECT 
        i.id_imputation,
        i.departement,
        i.Instruction,
        i.date_impu,
        i.instruction_personnalisee,
        c.Numero_Courrier,
        c.id_courrier,
        c.Objet
    FROM 
        imputation i
         JOIN
        courrier c ON c.id_courrier = i.id_courrier
    WHERE 
        i.id_courrier = ?
    ORDER BY 
        i.departement, i.date_impu DESC;
";

$stmt = $connexion->prepare($query);
$stmt->bind_param("i", $id_courrier);
$stmt->execute();
$result = $stmt->get_result();

$suiv_par_departement = [];
while ($row = $result->fetch_assoc()) {
    $suiv_par_departement[$row['departement']][] = $row;
}

$stmt->close();
