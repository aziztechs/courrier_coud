<?php
require_once('fonction.php'); // Pour la connexion à la base

/**
 * Liste les suivis de courrier avec pagination
 * 
 * @param int $limit Nombre d'éléments par page
 * @param int $offset Position de départ
 * @param array $filters Tableau de filtres optionnels
 * @return array Tableau des suivis
 * @throws RuntimeException En cas d'erreur SQL
 */
function lister_suivis(int $limit = 0, int $offset = 0, array $filters = []): array {
    global $connexion;
    
    try {
        // Construction sécurisée de la requête
        $query = "SELECT * FROM suivi_courrier WHERE 1=1";
        $params = [];
        $types = '';
        
        // Filtrage
        if (!empty($filters['search'])) {
            $query .= " AND (numero LIKE ? OR expediteur LIKE ? OR objet LIKE ? OR destinataire LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $types .= 'ssss';
        }
        
        if (!empty($filters['statut'])) {
            $query .= " AND statut_1 = ?";
            $params[] = $filters['statut'];
            $types .= 's';
        }
        
        if (!empty($filters['date_reception'])) {
            $query .= " AND date_reception = ?";
            $params[] = $filters['date_reception'];
            $types .= 's';
        }
        
        $query .= " ORDER BY date_reception DESC, id DESC";
        
        // Pagination
        if ($limit > 0) {
            $query .= " LIMIT ? OFFSET ?";
            $params = array_merge($params, [$limit, $offset]);
            $types .= 'ii';
        }
        
        $stmt = $connexion->prepare($query);
        if (!$stmt) {
            throw new RuntimeException("Erreur de préparation: " . $connexion->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (mysqli_sql_exception $e) {
        error_log("Erreur SQL dans lister_suivis(): " . $e->getMessage());
        throw new RuntimeException("Erreur lors de la récupération des suivis");
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
    // En cas d'exception, retourne un tableau vide (sécurité)
    return [];
}

/**
 * Ajoute un nouveau suivi de courrier
 * 
 * @param string $numero Numéro du courrier
 * @param string $date_reception Date de réception (format Y-m-d)
 * @param string $expediteur Expéditeur du courrier
 * @param string $objet Objet du courrier
 * @param string $destinataire Destinataire du courrier
 * @param string $statut_1 Statut principal
 * @param string|null $statut_2 Statut secondaire
 * @param string|null $statut_3 Statut tertiaire
 * @return int ID du nouveau suivi
 * @throws InvalidArgumentException Si les données sont invalides
 * @throws RuntimeException En cas d'erreur SQL
 */
function ajouter_suivi(
    string $numero, 
    string $date_reception, 
    string $expediteur, 
    string $objet, 
    string $destinataire, 
    string $statut_1, 
    ?string $statut_2, 
    ?string $statut_3 
): int {
    global $connexion;
    
    try {
        // Validation des données
        $errors = [];
        
        // Champs obligatoires
        $required = [
            'numero' => $numero,
            'date_reception' => $date_reception,
            'expediteur' => $expediteur,
            'objet' => $objet,
            'destinataire' => $destinataire,
            'statut_1' => $statut_1
        ];
        
        foreach ($required as $field => $value) {
            if (empty(trim($value))) {
                $errors[] = "Le champ $field est obligatoire";
            }
        }
        
        // Format du numéro (ex: 2023-001)
        if (!preg_match('/^\d{4}-\d{3}$/', $numero)) {
            $errors[] = "Format de numéro invalide (doit être YYYY-NNN)";
        }
        
        // Validation de la date
        $dateObj = DateTime::createFromFormat('Y-m-d', $date_reception);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $date_reception) {
            $errors[] = "Format de date invalide (YYYY-MM-DD attendu)";
        }
        
        if ($errors) {
            throw new InvalidArgumentException(implode("\n", $errors));
        }
        
        // Préparation de la requête
        $stmt = $connexion->prepare("INSERT INTO suivi_courrier 
                              (numero, date_reception, expediteur, objet, destinataire, statut_1, statut_2, statut_3) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new RuntimeException("Erreur de préparation: " . $connexion->error);
        }
        
        $stmt->bind_param(
            "ssssssss", 
            $numero, 
            $date_reception, 
            $expediteur, 
            $objet, 
            $destinataire, 
            $statut_1, 
            $statut_2, 
            $statut_3
        );
        
        if (!$stmt->execute()) {
            throw new RuntimeException("Erreur d'exécution: " . $stmt->error);
        }
        
        return $stmt->insert_id;
    } catch (mysqli_sql_exception $e) {
        error_log("Erreur SQL dans ajouter_suivi(): " . $e->getMessage());
        throw new RuntimeException("Erreur lors de l'ajout du suivi");
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
    // En cas d'exception, retourne 0 (sécurité)
    return 0;
}


function obtenir_suivi(int $id): ?array {
    global $connexion;
    $resultat = null;
    try {
        if ($id <= 0) {
            throw new InvalidArgumentException("ID de suivi invalide");
        }
        
        $stmt = $connexion->prepare("SELECT * FROM suivi_courrier WHERE id = ? LIMIT 1");
        if (!$stmt) {
            throw new RuntimeException("Erreur de préparation: " . $connexion->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $resultat = $result->fetch_assoc() ?: null;
    } catch (mysqli_sql_exception $e) {
        error_log("Erreur SQL dans obtenir_suivi(): " . $e->getMessage());
        throw new RuntimeException("Erreur lors de la récupération du suivi");
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
    return $resultat;
}


function modifierSuivi(int $id, array $data): bool {
    global $connexion;

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
        throw new Exception("Erreur de préparation de la requête : " . $connexion->error);
    }

    $stmt->bind_param(
        "ssssssssi",
        $data['numero'],
        $data['date_reception'],
        $data['expediteur'],
        $data['objet'],
        $data['destinataire'],
        $data['statut_1'],
        $data['statut_2'],
        $data['statut_3'],
        $id
    );

    if (!$stmt->execute()) {
        throw new Exception("Erreur lors de l'exécution : " . $stmt->error);
    }

    return true;
}


/**
 * Supprime un suivi
 * 
 * @param int $id ID du suivi à supprimer
 * @return bool True si la suppression a réussi
 * @throws InvalidArgumentException Si l'ID est invalide
 * @throws RuntimeException En cas d'erreur SQL
 */
function supprimerSuivi(int $id, mysqli $connexion): bool{ 
    try {
        if ($id <= 0) {
            throw new InvalidArgumentException("ID de suivi invalide");
        }
        
        // Vérification de l'existence avant suppression
        $existing = obtenir_suivi($id, $connexion);
        if (!$existing) {
            throw new InvalidArgumentException("Suivi introuvable");
        }
        
        $stmt = $connexion->prepare("DELETE FROM suivi_courrier WHERE id = ?");
        if (!$stmt) {
            throw new RuntimeException("Erreur de préparation: " . $connexion->error);
        }
        
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new RuntimeException("Erreur d'exécution: " . $stmt->error);
        }
        
        return $stmt->affected_rows > 0;
    } catch (mysqli_sql_exception $e) {
        error_log("Erreur SQL dans supprimer_suivi(): " . $e->getMessage());
        throw new RuntimeException("Erreur lors de la suppression du suivi");
    } catch (Exception $e) {
        error_log("Erreur dans supprimer_suivi(): " . $e->getMessage());
        return false;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
    // En cas d'exception ou de sortie inattendue, retourne false (sécurité)
    return false;
}
/**
 * Recherche des suivis selon un critère
 * 
 * @param string $critere Colonne de recherche
 * @param string $valeur Valeur à rechercher
 * @param int $limit Limite de résultats
 * @param int $offset Offset de pagination
 * @return array Tableau des résultats
 * @throws InvalidArgumentException Si le critère est invalide
 * @throws RuntimeException En cas d'erreur SQL
 */
function rechercher_suivis(
    string $critere, 
    string $valeur, 
    int $limit = 0, 
    int $offset = 0
): array {
    global $connexion;
    
    try {
        // Colonnes autorisées (prévention injection SQL)
        $colonnes_autorisees = [
            'numero', 'expediteur', 'objet', 
            'destinataire', 'statut_1', 'statut_2', 'statut_3'
        ];
        
        if (!in_array($critere, $colonnes_autorisees)) {
            throw new InvalidArgumentException("Critère de recherche non autorisé");
        }
        
        // Construction sécurisée de la requête
        $query = "SELECT * FROM suivi_courrier WHERE $critere LIKE ? 
                 ORDER BY date_reception DESC, id DESC";
        
        if ($limit > 0) {
            $query .= " LIMIT ? OFFSET ?";
        }
        
        $stmt = $connexion->prepare($query);
        if (!$stmt) {
            throw new RuntimeException("Erreur de préparation: " . $connexion->error);
        }
        
        $searchTerm = "%$valeur%";
        
        if ($limit > 0) {
            $stmt->bind_param("sii", $searchTerm, $limit, $offset);
        } else {
            $stmt->bind_param("s", $searchTerm);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (mysqli_sql_exception $e) {
        error_log("Erreur SQL dans rechercher_suivis(): " . $e->getMessage());
        throw new RuntimeException("Erreur lors de la recherche des suivis");
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
    // En cas d'exception ou de sortie inattendue, retourne un tableau vide (sécurité)
    return [];
}

/**
 * Alias de obtenir_suivi() avec vérification supplémentaire
 * 
 * @param int $id_suivi ID du suivi
 * @return array Données du suivi
 * @throws InvalidArgumentException Si le suivi n'existe pas
 * @throws RuntimeException En cas d'erreur SQL
 */
function getSuiviById(int $id_suivi): array {
    $suivi = obtenir_suivi($id_suivi);
    
    if (!$suivi) {
        throw new InvalidArgumentException("Suivi introuvable");
    }
    
    return $suivi;
}

/**
 * Récupère la liste des statuts principaux (statut_1) distincts depuis la base de données
 * 
 * @return array Liste des statuts
 * @throws RuntimeException En cas d'erreur SQL
 */
function recuperer_statuts(): array {
    global $connexion;
    $statuts = [];
    try {
        $query = "SELECT DISTINCT statut_1 FROM suivi_courrier ORDER BY statut_1 ASC";
        $result = $connexion->query($query);
        if (!$result) {
            throw new RuntimeException("Erreur lors de la récupération des statuts: " . $connexion->error);
        }
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['statut_1'])) {
                $statuts[] = $row['statut_1'];
            }
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Erreur SQL dans recuperer_statuts(): " . $e->getMessage());
        throw new RuntimeException("Erreur lors de la récupération des statuts");
    }
    return $statuts;
}