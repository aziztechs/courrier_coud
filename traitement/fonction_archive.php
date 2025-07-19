<?php
require_once('fonction.php'); // Pour la connexion à la base

/**
 * Récupère la liste des numéros de correspondance existants
 * @return array Liste des numéros de correspondance
 */
function getNumerosCorrespondance() {
    global $connexion;
    $result = $connexion->query("SELECT DISTINCT num_correspondance FROM archive ORDER BY num_correspondance");
    
    $numeros = array();
    while ($row = $result->fetch_assoc()) {
        $numeros[] = $row['num_correspondance'];
    }
    
    $result->free();
    return $numeros;
}

/**
 * Ajoute une nouvelle archive
 */
function ajouterArchive($data) {
    global $connexion;
    
    // Vérifier si le numéro de correspondance existe déjà
    if (numeroCorrespondanceExiste($data['num_correspondance'])) {
        throw new Exception("Ce numéro de correspondance existe déjà dans les archives.");
    }
    
    // Préparation de la requête d'insertion
    $stmt = $connexion->prepare("INSERT INTO archive 
        (type_archivage, num_correspondance, pdf_archive, commentaire, date_archivage) 
        VALUES (?, ?, ?, ?, NOW())");
    
    $stmt->bind_param(
        "ssss", 
        $data['type_archivage'],
        $data['num_correspondance'],
        $data['pdf_archive'],
        $data['commentaire']
    );
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Vérifie si un numéro de correspondance existe déjà
 */
function numeroCorrespondanceExiste($num_correspondance) {
    global $connexion;
    $stmt = $connexion->prepare("SELECT COUNT(*) FROM archive WHERE num_correspondance = ?");
    $stmt->bind_param("s", $num_correspondance);
    $stmt->execute();
    $count = 0;
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

/**
 * Récupère toutes les archives
 * @return array Tableau des archives
 */
function getAllArchives() {
    global $connexion;
    $sql = "SELECT * FROM archive ORDER BY id_archive DESC";
    $result = $connexion->query($sql);

    $archives = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $archives[] = $row;
        }
    }
    return $archives;
}

/**
 * Récupère une archive par son ID
 */
function getArchiveById($id_archive) {
    global $connexion;
    $stmt = $connexion->prepare("SELECT * FROM archive WHERE id_archive = ?");
    $stmt->bind_param("i", $id_archive);
    $stmt->execute();
    $result = $stmt->get_result();
    $archive = $result->fetch_assoc();
    $stmt->close();
    
    return $archive ?: null;
}

/**
 * Modifie une archive existante
 */
function modifierArchive($data) {
    global $connexion;
    
    $stmt = $connexion->prepare("UPDATE archive SET 
        type_archivage = ?,
        num_correspondance = ?,
        pdf_archive = ?,
        commentaire = ?
        WHERE id_archive = ?");
    
    $stmt->bind_param(
        "ssssi", 
        $data['type_archivage'],
        $data['num_correspondance'],
        $data['pdf_archive'],
        $data['commentaire'],
        $data['id_archive']
    );
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Supprime une archive par son ID
 */
function supprimerArchive($id_archive) {
    global $connexion;
    $stmt = $connexion->prepare("DELETE FROM archive WHERE id_archive = ?");
    $stmt->bind_param("i", $id_archive);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

/**
 * Récupère les archives paginées
 */
function getArchivesPaginated($offset, $perPage) {
    global $connexion;

    $sql = "SELECT * FROM archive ORDER BY date_archivage DESC LIMIT ?, ?";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param("ii", $offset, $perPage);
    $stmt->execute();

    $result = $stmt->get_result();
    $archives = [];
    while ($row = $result->fetch_assoc()) {
        $archives[] = $row;
    }
    return $archives;
}

/**
 * Compte le nombre total d'archives
 */
function countTotalArchives() {
    global $connexion;
    $sql = "SELECT COUNT(*) as total FROM archive";
    $result = $connexion->query($sql);
    if ($row = $result->fetch_assoc()) {
        return (int)$row['total'];
    }
    return 0;
}


// Dans votre fichier PHP (au début, avant le HTML)
function getEnumValuesMysqli($connexion, $table, $column) {
    global $connexion;
    $sql = "SHOW COLUMNS FROM $table LIKE '$column'";
    $result = $connexion->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (isset($row['Type'])) {
            preg_match("/^enum\(\'(.*)\'\)$/", $row['Type'], $matches);
            if ($matches) {
                return explode("','", $matches[1]);
            }
        }
    }
    return [];
}

// Modifier les fonctions de filtrage comme suit:

function getArchivesWithFilters($filters, $offset, $perPage) {
    global $connexion;
    
    // Construction de la requête de base
    $query = "SELECT * FROM archive WHERE 1=1";
    $params = [];
    $types = '';
    
    // Filtre de recherche
    if (!empty($filters['search'])) {
        $query .= " AND (num_correspondance LIKE ? OR commentaire LIKE ? OR type_archivage LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        $types .= 'sss';
    }
    
    // Filtre par type d'archivage
    if (!empty($filters['type_archivage'])) {
        $query .= " AND type_archivage = ?";
        $params[] = $filters['type_archivage'];
        $types .= 's';
    }
    
    // Filtre par date de début
    if (!empty($filters['date_debut'])) {
        $query .= " AND DATE(date_archivage) >= ?";
        $params[] = $filters['date_debut'];
        $types .= 's';
    }
    
    // Filtre par date de fin
    if (!empty($filters['date_fin'])) {
        $query .= " AND DATE(date_archivage) <= ?";
        $params[] = $filters['date_fin'];
        $types .= 's';
    }
    
    // Ajout de la pagination
    $query .= " ORDER BY date_archivage DESC LIMIT ?, ?";
    $params = array_merge($params, [$offset, $perPage]);
    $types .= 'ii';
    
    // Préparation et exécution de la requête
    $stmt = $connexion->prepare($query);
    if (!$stmt) {
        throw new Exception("Erreur de préparation de la requête: " . $connexion->error);
    }
    
    if ($types && !$stmt->bind_param($types, ...$params)) {
        throw new Exception("Erreur de liaison des paramètres: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Erreur d'exécution de la requête: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Erreur de récupération des résultats: " . $stmt->error);
    }
    
    $archives = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $archives;
}

function countArchivesWithFilters($filters) {
    global $connexion;
    
    $query = "SELECT COUNT(*) as total FROM archive WHERE 1=1";
    $params = [];
    $types = '';
    
    if (!empty($filters['search'])) {
        $query .= " AND (num_correspondance LIKE ? OR commentaire LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }
    
    if (!empty($filters['type_archivage'])) {
        $query .= " AND type_archivage = ?";
        $params[] = $filters['type_archivage'];
        $types .= 's';
    }
    
    // Gestion des dates
    if (!empty($filters['date_debut'])) {
        $query .= " AND date_archivage >= ?";
        $params[] = $filters['date_debut'] . ' 00:00:00';
        $types .= 's';
    }
    
    if (!empty($filters['date_fin'])) {
        $query .= " AND date_archivage <= ?";
        $params[] = $filters['date_fin'] . ' 23:59:59';
        $types .= 's';
    }
    
    $stmt = $connexion->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'];
}
