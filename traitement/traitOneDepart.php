<?php
// Connectez-vous à votre base de données MySQL
// Fonction de connexion dans l'espace utilisateur
require_once('fonction.php');
$connexion = connexionBD() ;
/*function getCourriersByRole() {
    global $connexion;
    // Requête SQL pour récupérer les courriers
    $query = "SELECT DISTINCT  c.Numero_Courrier, c.Date, c.Objet, c.Nature, c.pdf , i.id_imputation, i.Instruction , i.date_impu
              FROM courrier c
              INNER JOIN imputation i ON c.id_courrier = i.id_courrier
              WHERE i.departement = ?  GROUP BY
        c.id_courrier"
              ;
    $role = $_SESSION['subrole'];
    $idU = $_SESSION['id_user'];
    // Préparer et exécuter la requête
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Récupérer les résultats
    $courriers = $result->fetch_all(MYSQLI_ASSOC);
    
    // Fermer la déclaration et la connexion
   
    
    return $courriers;
}
*/
function getCourriersByRole($search = '', $page = 1, $results_per_page = 10) {
    global $connexion;
    $offset = ($page - 1) * $results_per_page;
    
    // Requête SQL pour récupérer les courriers avec la recherche
    $query = "SELECT DISTINCT c.Numero_Courrier, c.Date, c.Type ,c.Objet, c.Nature, c.pdf, i.id_imputation, i.Instruction, i.date_impu , i.instruction_personnalisee
              FROM courrier c
              INNER JOIN imputation i ON c.id_courrier = i.id_courrier
              WHERE i.departement = ? AND (c.Numero_Courrier LIKE ? OR c.Objet LIKE ? OR i.Instruction LIKE ? OR i.instruction_personnalisee LIKE ? OR c.Type LIKE ?   )
              GROUP BY c.id_courrier
              LIMIT ? OFFSET ?";
    
    $role = $_SESSION['subrole'];
    $searchTerm = '%' . $search . '%';
    
    // Préparer et exécuter la requête
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("ssssssii", $role, $searchTerm, $searchTerm, $searchTerm, $searchTerm , $searchTerm, $results_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Récupérer les résultats
    $courriers = $result->fetch_all(MYSQLI_ASSOC);
    
    return $courriers;
}


function getTotaleCourriers($search = '') {
    global $connexion;
    
    $query = "SELECT COUNT(DISTINCT c.id_courrier) AS total
              FROM courrier c
              INNER JOIN imputation i ON c.id_courrier = i.id_courrier
              WHERE i.departement = ? AND (c.Numero_Courrier LIKE ? OR c.Objet LIKE ? OR i.Instruction LIKE ? OR i.instruction_personnalisee LIKE ?)";
    
    $role = $_SESSION['subrole'];
    $searchTerm = '%' . $search . '%';
    
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("sssss", $role, $searchTerm, $searchTerm , $searchTerm , $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'];
}