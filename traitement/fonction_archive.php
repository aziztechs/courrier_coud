<?php
include('../../traitement/fonction.php'); // Assurez-vous que $connexion (mysqli) est bien initialisé ici


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
 * @param array $data Les données de l'archive
 * @return bool True si succès, false sinon
 * @throws Exception Si le numéro de correspondance existe déjà
 */
function ajouterArchive($data) {
    global $connexion;
    
    // Vérifier si le numéro de correspondance existe déjà
    $stmt = $connexion->prepare("SELECT COUNT(*) FROM archive WHERE num_correspondance = ?");
    $stmt->bind_param("s", $data['num_correspondance']);
    $stmt->execute();
    $count = 0;
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    if ($count > 0) {
        throw new Exception("Ce numéro de correspondance existe déjà dans les archives.");
    }
    
    // Préparation de la requête d'insertion
    $stmt = $connexion->prepare("INSERT INTO archive 
        (type_archivage, num_correspondance, pdf_archive, motif_archivage, commentaire, date_archivage) 
        VALUES (?, ?, ?, ?, ?, NOW())");
    
    $stmt->bind_param(
        "sssss", 
        $data['type_archivage'],
        $data['num_correspondance'],
        $data['pdf_archive'],
        $data['motif_archivage'],
        $data['commentaire']
    );
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}



// 🔹 READ : Récupérer toutes les archives
function getAllArchives() {
    global $connexion;

    $sql = "SELECT * FROM archive ORDER BY date_archivage DESC";
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
 * @param int $id_archive ID de l'archive
 * @return array|null Les données de l'archive ou null si non trouvée
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
 * @param array $data Les données de l'archive à modifier
 * @return bool True si succès, false sinon
 */
function modifierArchive($data) {
    global $connexion;
    
    // Préparation de la requête de mise à jour
    $stmt = $connexion->prepare("UPDATE archive SET 
        type_archivage = ?,
        num_correspondance = ?,
        pdf_archive = ?,
        motif_archivage = ?,
        commentaire = ?
        WHERE id_archive = ?");
    
    $stmt->bind_param(
        "sssssi", 
        $data['type_archivage'],
        $data['num_correspondance'],
        $data['pdf_archive'],
        $data['motif_archivage'],
        $data['commentaire'],
        $data['id_archive']
    );
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Supprime une archive par son ID
 * @param int $id_archive ID de l'archive à supprimer
 * @return bool True si succès, false sinon
 */
function supprimerArchive($id_archive) {
    global $connexion;
    $stmt = $connexion->prepare("DELETE FROM archive WHERE id_archive = ?");
    $stmt->bind_param("i", $id_archive);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

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

function countTotalArchives() {
    global $connexion;
    $sql = "SELECT COUNT(*) as total FROM archive";
    $result = $connexion->query($sql);
    if ($row = $result->fetch_assoc()) {
        return (int)$row['total'];
    }
    return 0;
}

// Fonctions à ajouter dans fonction_archive.php

function getArchivesWithFilters($filters, $offset, $perPage) {
    global $connexion;
    
    $query = "SELECT * FROM archive WHERE 1=1";
    $params = [];
    $types = '';
    
    // Construction dynamique de la requête
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
    
    if (!empty($filters['motif_archivage'])) {
        $query .= " AND motif_archivage = ?";
        $params[] = $filters['motif_archivage'];
        $types .= 's';
    }
    
    if (!empty($filters['date_archivage'])) {
        $query .= " AND date_archivage >= ?";
        $params[] = $filters['date_archivage'] . ' 00:00:00';
        $types .= 's';
    }
    
   
    $query .= " ORDER BY date_archivage DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $perPage;
    $types .= 'ii';
    
    $stmt = $connexion->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}



function countArchivesWithFilters($filters) {
    global $connexion;
    
    $query = "SELECT COUNT(*) as total FROM archive WHERE 1=1";
    $params = [];
    $types = '';
    
    // Mêmes filtres que getArchivesWithFilters
    if (!empty($filters['search'])) {
        $query .= " AND (num_correspondance LIKE ? OR commentaire LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }
    
    // ... (autres filtres)
    
    $stmt = $connexion->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'];
}

?>
