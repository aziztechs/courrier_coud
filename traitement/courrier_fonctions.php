<?php
// Inclure le fichier de fonction et établir la connexion
require_once('fonction.php');
$connexion = connexionBD();

/**
 * Enregistre un nouveau courrier
 */
function enregistrerCourrier($connexion, $data, &$errors = []) {
    // Validation du numéro de courrier
    if (empty($data['Numero_Courrier'])) {
        $errors['Numero_Courrier'] = "Le numéro de courrier est obligatoire";
    } else {
        $numero = mysqli_real_escape_string($connexion, $data['Numero_Courrier']);
        
        // Validation du format (COUR-AAAA-NNN)
        if (!preg_match('/^COUR-\d{4}-\d{3}$/', $numero)) {
            $errors['Numero_Courrier'] = "Format invalide. Le format doit être COUR-AAAA-NNN";
        } else {
            // Vérification de l'unicité du numéro
            $checkQuery = "SELECT id_courrier FROM courrier WHERE Numero_Courrier = '$numero'";
            $result = mysqli_query($connexion, $checkQuery);
            
            if (mysqli_num_rows($result) > 0) {
                $errors['Numero_Courrier'] = "Ce numéro de courrier existe déjà";
            }
        }
    }

    // Validation des autres champs obligatoires
    $requiredFields = ['date', 'Objet', 'Nature', 'Type', 'Expediteur'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $errors[$field] = "Ce champ est obligatoire";
        }
    }

    // Si des erreurs existent, on ne procède pas à l'insertion
    if (!empty($errors)) {
        return false;
    }

    // Échappement des autres données
    $date = mysqli_real_escape_string($connexion, $data['date']);
    $objet = mysqli_real_escape_string($connexion, $data['Objet']);
    $nature = mysqli_real_escape_string($connexion, $data['Nature']);
    $type = mysqli_real_escape_string($connexion, $data['Type']);
    $expediteur = mysqli_real_escape_string($connexion, $data['Expediteur']);
    
    // Gestion du fichier PDF (optionnel)
    $pdf = isset($data['pdf']) ? mysqli_real_escape_string($connexion, $data['pdf']) : null;
    $pdfValue = $pdf ? "'$pdf'" : "NULL";

    $query = "INSERT INTO courrier (Numero_Courrier, date, Objet, pdf, Nature, Type, Expediteur) 
              VALUES ('$numero', '$date', '$objet', $pdfValue, '$nature', '$type', '$expediteur')";
    
    if (mysqli_query($connexion, $query)) {
        return mysqli_insert_id($connexion);
    } else {
        // Gestion des erreurs SQL
        $errors['database'] = "Erreur lors de l'enregistrement: " . mysqli_error($connexion);
        return false;
    }
}

/**
 * Modifie un courrier existant
 */
function modifierCourrier($connexion, $id, $data, &$errors = []) {
    // Validation de l'ID
    $id = (int)$id;
    if ($id <= 0) {
        $errors['id'] = "ID de courrier invalide";
        return false;
    }

    // Vérification que le courrier existe
    $courrierExist = getCourrierById($connexion, $id);
    if (!$courrierExist) {
        $errors['id'] = "Le courrier avec l'ID $id n'existe pas";
        return false;
    }

    // Validation du numéro de courrier
    if (empty($data['Numero_Courrier'])) {
        $errors['Numero_Courrier'] = "Le numéro de courrier est obligatoire";
    } else {
        $numero = mysqli_real_escape_string($connexion, $data['Numero_Courrier']);
        
        // Validation du format (COUR-AAAA-NNN)
        if (!preg_match('/^COUR-\d{4}-\d{3}$/', $numero)) {
            $errors['Numero_Courrier'] = "Format invalide. Le format doit être COUR-AAAA-NNN";
        } else {
            // Vérification de l'unicité du numéro (en excluant l'enregistrement actuel)
            $checkQuery = "SELECT id_courrier FROM courriercsa 
                          WHERE Numero_Courrier = '$numero' AND id_courrier != $id";
            $result = mysqli_query($connexion, $checkQuery);
            
            if (mysqli_num_rows($result) > 0) {
                $errors['Numero_Courrier'] = "Ce numéro de courrier est déjà utilisé par un autre courrier";
            }
        }
    }

    // Validation des autres champs obligatoires
    $requiredFields = ['date', 'Objet', 'Nature', 'Type', 'Expediteur'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $errors[$field] = "Ce champ est obligatoire";
        }
    }

    // Si des erreurs existent, on ne procède pas à la mise à jour
    if (!empty($errors)) {
        return false;
    }

    // Échappement des autres données
    $date = mysqli_real_escape_string($connexion, $data['date']);
    $objet = mysqli_real_escape_string($connexion, $data['Objet']);
    $nature = mysqli_real_escape_string($connexion, $data['Nature']);
    $type = mysqli_real_escape_string($connexion, $data['Type']);
    $expediteur = mysqli_real_escape_string($connexion, $data['Expediteur']);
    
    // Gestion du fichier PDF (optionnel)
    $pdf = isset($data['pdf']) && !empty($data['pdf']) 
           ? mysqli_real_escape_string($connexion, $data['pdf']) 
           : $courrierExist['pdf']; // Conserver l'ancienne valeur si non fournie

    $query = "UPDATE courrier SET 
                Numero_Courrier = '$numero',
                date = '$date',
                Objet = '$objet',
                pdf = '$pdf',
                Nature = '$nature',
                Type = '$type',
                Expediteur = '$expediteur'
              WHERE id_courrier = $id";
    
    if (mysqli_query($connexion, $query)) {
        return true;
    } else {
        // Gestion des erreurs SQL
        $errors['database'] = "Erreur lors de la modification: " . mysqli_error($connexion);
        return false;
    }
}

/**
 * Récupère la liste des courriers
 */
function getListeCourrier($connexion, $limit = 1000) {
    $limit = (int)$limit;
    $query = "SELECT * FROM courrier ORDER BY id_courrier DESC LIMIT $limit";
    $result = mysqli_query($connexion, $query);
    
    $courriers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $courriers[] = $row;
    }
    
    return $courriers;
}


/**
 * Récupère un courrier par son ID
 */
function getCourrierById($connexion, $id) {
    $id = (int)$id;
    $query = "SELECT * FROM courrier WHERE id_courrier = $id";
    $result = mysqli_query($connexion, $query);
    
    return mysqli_fetch_assoc($result);
}

/**
 * Supprime un courrier
 */
function supprimerCourrier($connexion, $id) {
    // Vérification que l'ID existe
    if (!getCourrierById($connexion, $id)) {
        throw new Exception("Le courrier avec l'ID $id n'existe pas");
    }

    $id = (int)$id;
    $query = "DELETE FROM courrier WHERE id_courrier = $id";
    
    return mysqli_query($connexion, $query);
}

/**
 * Récupère les courriers filtrés avec pagination
 */

function getFilteredCourrier($connexion, $filters = [], $page = 1, $itemsPerPage = 10) {
    $query = "SELECT * FROM courrier WHERE 1=1";
    $params = [];
    
    // Recherche unifiée
    if (!empty($filters['search'])) {
        $search = mysqli_real_escape_string($connexion, $filters['search']);
        $query .= " AND (
            Numero_Courrier LIKE '%$search%' OR 
            Objet LIKE '%$search%' OR 
            Nature LIKE '%$search%' OR 
            Type LIKE '%$search%' OR 
            Expediteur LIKE '%$search%'
        )";
    }
    
    // Filtres par date
    if (!empty($filters['date_debut'])) {
        $date_debut = mysqli_real_escape_string($connexion, $filters['date_debut']);
        $query .= " AND date >= '$date_debut'";
    }
    
    if (!empty($filters['date_fin'])) {
        $date_fin = mysqli_real_escape_string($connexion, $filters['date_fin']);
        $query .= " AND date <= '$date_fin'";
    }
    
    // Pagination
    $offset = ($page - 1) * $itemsPerPage;
    $query .= " ORDER BY id_courrier DESC LIMIT $offset, $itemsPerPage";
    
    $result = mysqli_query($connexion, $query);
    
    $courriers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $courriers[] = $row;
    }
    
    return $courriers;
}

function countFilteredCourriers($connexion, $filters = []) {
    $query = "SELECT COUNT(*) as total FROM courrier WHERE 1=1";
    
    // Recherche unifiée
    if (!empty($filters['search'])) {
        $search = mysqli_real_escape_string($connexion, $filters['search']);
        $query .= " AND (
            Numero_Courrier LIKE '%$search%' OR 
            Objet LIKE '%$search%' OR 
            Nature LIKE '%$search%' OR 
            Type LIKE '%$search%' OR 
            Expediteur LIKE '%$search%'
        )";
    }
    
    // Filtres par date
    if (!empty($filters['date_debut'])) {
        $date_debut = mysqli_real_escape_string($connexion, $filters['date_debut']);
        $query .= " AND date >= '$date_debut'";
    }
    
    if (!empty($filters['date_fin'])) {
        $date_fin = mysqli_real_escape_string($connexion, $filters['date_fin']);
        $query .= " AND date <= '$date_fin'";
    }
    
    $result = mysqli_query($connexion, $query);
    $row = mysqli_fetch_assoc($result);
    
    return (int)$row['total'];
}

/**
 * Récupère le dernier numéro de courrier pour une année donnée
 */
function getDernierNumeroCourrier($connexion, $annee) {
    $query = "SELECT MAX(CAST(SUBSTRING_INDEX(Numero_Courrier, '-', -1) AS UNSIGNED)) as dernier_num 
              FROM courrier 
              WHERE Numero_Courrier LIKE 'COUR-$annee-%'";
    $result = mysqli_query($connexion, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['dernier_num'] ?? 0;
}
?>