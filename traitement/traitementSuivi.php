<?php
// Connectez-vous à votre base de données MySQL
// Fonction de connexion dans l'espace utilisateur
require_once('fonction.php');
$connexion = connexionBD() ;


// Vérifier si l'ID du courrier est stocké dans la session
if (isset($_GET['id_imputation'])) {
    $id_imputation = $_GET['id_imputation'];

    // Requête pour récupérer les informations de suivi liées à l'ID d'imputation
    $query = "
    SELECT 
        i.departement,
        i.id_imputation,
        i.date_impu,
        i.Instruction,
        s.date_suivi,
        s.statut,
        s.id_user
    FROM 
        imputation i
    LEFT JOIN 
        suivi s ON i.id_imputation = s.id_imputation
    WHERE 
        i.id_imputation = ?

       
";

$stmt = $connexion->prepare($query);
$stmt->bind_param("i", $id_imputation);
$stmt->execute();
$result = $stmt->get_result();

// Organiser les suivis par département
$suivis_par_departement = [];
while ($row = $result->fetch_assoc()) {
    $suivis_par_departement[$row['departement']][] = $row;
}

$stmt->close();

// Stocker les suivis dans la session pour les utiliser dans le fichier suivi.php
$_SESSION['suivis_par_departement'] = $suivis_par_departement;

 }