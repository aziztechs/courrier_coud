<?php

require_once('fonction.php'); // Pour la connexion à la base

// 1. Fonction pour ajouter un suivi
function ajouter_suivi($numero, $date_reception, $expediteur, $objet, $destinataire, $statut_1, $statut_2, $statut_3) {
    global $connexion;
    
    $stmt = $connexion->prepare("INSERT INTO suivi_courrier 
                          (numero, date_reception, expediteur, objet, destinataire, statut_1, statut_2, statut_3) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $numero, $date_reception, $expediteur, $objet, $destinataire, $statut_1, $statut_2, $statut_3);
    
    return $stmt->execute();
}

// 2. Fonction pour lister tous les suivis
function lister_suivis() {
    global $connexion;
    
    $result = $connexion->query("SELECT * FROM suivi_courrier ORDER BY date_reception DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// 3. Fonction pour obtenir un suivi par son ID
function obtenir_suivi($id) {
    global $connexion;
    
    $stmt = $connexion->prepare("SELECT * FROM suivi_courrier WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

// 4. Fonction pour modifier un suivi
function modifier_suivi($id, $numero, $date_reception, $expediteur, $objet, $destinataire, $statut_1, $statut_2, $statut_3) {
    global $connexion;
    
    // Validation des entrées
    if (!is_numeric($id) || $id <= 0) {
        throw new Exception("ID de suivi invalide");
    }
    
    // Vérification des champs obligatoires
    $required_fields = [$numero, $date_reception, $expediteur, $objet, $destinataire];
    foreach ($required_fields as $field) {
        if (empty(trim($field))) {
            throw new Exception("Tous les champs obligatoires doivent être remplis");
        }
    }
    
    try {
        // Préparation de la requête (sans date_modification)
        $query = "UPDATE suivi_courrier SET 
                numero = ?, 
                date_reception = ?, 
                expediteur = ?, 
                objet = ?, 
                destinataire = ?, 
                statut_1 = ?, 
                statut_2 = ?, 
                statut_3 = ?
                WHERE id = ?";
        
        $stmt = $connexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Erreur de préparation de la requête: " . $connexion->error);
        }
        
        // Liaison des paramètres
        $stmt->bind_param("ssssssssi", 
            $numero, 
            $date_reception, 
            $expediteur, 
            $objet, 
            $destinataire, 
            $statut_1, 
            $statut_2, 
            $statut_3, 
            $id
        );
        
        // Exécution
        if (!$stmt->execute()) {
            throw new Exception("Erreur d'exécution: " . $stmt->error);
        }
        
        if ($stmt->affected_rows === 0) {
            throw new Exception("Aucune modification effectuée - ID peut-être incorrect");
        }
        
        return true;
        
    } catch (mysqli_sql_exception $e) {
        error_log("Erreur SQL dans modifier_suivi(): " . $e->getMessage());
        throw new Exception("Erreur lors de la mise à jour du suivi: " . $e->getMessage());
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

// Fonction pour récupérer un suivi par son ID (alias de obtenir_suivi)
function getSuiviById($id_suivi) {
    return obtenir_suivi($id_suivi);
}

// 5. Fonction pour supprimer un suivi
function supprimer_suivi($id) {
    global $connexion;
    
    $stmt = $connexion->prepare("DELETE FROM suivi_courrier WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    return $stmt->execute();
}

// 6. Fonction pour rechercher des suivis
function rechercher_suivis($critere, $valeur) {
    global $connexion;
    
    // Liste des colonnes autorisées pour la recherche (sécurité)
    $colonnes_autorisees = ['numero', 'expediteur', 'objet', 'destinataire', 'statut_1', 'statut_2', 'statut_3'];
    
    if (!in_array($critere, $colonnes_autorisees)) {
        return false;
    }
    
    $stmt = $connexion->prepare("SELECT * FROM suivi_courrier WHERE $critere LIKE ? ORDER BY date_reception DESC");
    $term = "%$valeur%";
    $stmt->bind_param("s", $term);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}