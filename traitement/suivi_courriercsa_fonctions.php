<?php

require_once('fonction.php'); // Pour la connexion à la base
$items_par_page = 5; // Variable globale pour la pagination

// Fonction de validation des données
function validateSuiviData($data) {
    $errors = [];

    if (empty($data['id_courrier'])) {
        $errors['id_courrier'] = "L'ID du courrier est requis";
    }

    if (empty($data['destinataire'])) {
        $errors['destinataire'] = "Le destinataire est requis";
    } elseif (strlen($data['destinataire']) > 100) {
        $errors['destinataire'] = "Le destinataire ne doit pas dépasser 100 caractères";
    }

    return $errors;
}

// Ajouter un suivi
function ajouterSuivi($mysqli, $data) {
    $errors = validateSuiviData($data);
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $query = "INSERT INTO suivi_courriercsa 
             (id_courrier, destinataire, statut_1, statut_2, statut_3) 
             VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        return ['success' => false, 'error' => $mysqli->error];
    }

    $stmt->bind_param("issss", 
        $data['id_courrier'],
        $data['destinataire'],
        $data['statut_1'],
        $data['statut_2'],
        $data['statut_3']
    );

    if ($stmt->execute()) {
        return ['success' => true, 'id' => $stmt->insert_id];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

// Obtenir un suivi par son ID
function getSuivi($mysqli, $id_suivi) {
    $query = "SELECT s.*, c.Numero_Courrier, c.date, c.Objet, c.Expediteur 
             FROM suivi_courriercsa s 
             JOIN courrier c ON s.id_courrier = c.id_courrier
             WHERE s.id_suivi = ?";
    
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        return ['success' => false, 'error' => $mysqli->error];
    }

    $stmt->bind_param("i", $id_suivi);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['success' => false, 'error' => 'Suivi non trouvé'];
    }

    return ['success' => true, 'data' => $result->fetch_assoc()];
}

// Obtenir la liste des suivis avec pagination et filtres
function getSuivis($mysqli, $page = 1, $filters = []) {
    global $items_par_page;
    
    // Base query with join
    $query = "SELECT SQL_CALC_FOUND_ROWS s.*, 
             c.Numero_Courrier, c.date, c.Objet, c.Expediteur, c.Nature, c.Type
             FROM suivi_courriercsa s
             JOIN courrier c ON s.id_courrier = c.id_courrier";

    $where = [];
    $params = [];
    $types = '';

    // Apply filters
    if (!empty($filters['date_debut'])) {
        $where[] = "c.date >= ?";
        $params[] = $filters['date_debut'];
        $types .= 's';
    }

    if (!empty($filters['date_fin'])) {
        $where[] = "c.date <= ?";
        $params[] = $filters['date_fin'];
        $types .= 's';
    }

    if (!empty($filters['search'])) {
        $search = "%{$filters['search']}%";
        $where[] = "(c.Numero_Courrier LIKE ? OR 
                    c.Objet LIKE ? OR 
                    c.Expediteur LIKE ? OR 
                    s.destinataire LIKE ? OR
                    s.statut_1 LIKE ? OR
                    s.statut_2 LIKE ? OR
                    s.statut_3 LIKE ?)";
        for ($i = 0; $i < 7; $i++) {
            $params[] = $search;
            $types .= 's';
        }
    }

    if (!empty($filters['statut'])) {
        $where[] = "(s.statut_1 = ? OR s.statut_2 = ? OR s.statut_3 = ?)";
        $params[] = $filters['statut'];
        $params[] = $filters['statut'];
        $params[] = $filters['statut'];
        $types .= 'sss';
    }

    if (!empty($filters['nature'])) {
        $where[] = "c.Nature = ?";
        $params[] = $filters['nature'];
        $types .= 's';
    }

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    // Pagination avec tri par id_suivi DESC
    $offset = ($page - 1) * $items_par_page;
    $query .= " ORDER BY s.id_suivi DESC LIMIT ? OFFSET ?";
    $params[] = $items_par_page;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        return ['success' => false, 'error' => $mysqli->error];
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Get total count
    $total_result = $mysqli->query("SELECT FOUND_ROWS()");
    $total_rows = $total_result->fetch_row()[0];
    $total_pages = ceil($total_rows / $items_par_page);

    $suivis = [];
    while ($row = $result->fetch_assoc()) {
        $suivis[] = $row;
    }

    return [
        'success' => true,
        'data' => $suivis,
        'pagination' => [
            'page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total_rows
        ]
    ];
}

// Modifier un suivi
function modifierSuivi($mysqli, $id_suivi, $data) {
    $errors = validateSuiviData($data);
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $query = "UPDATE suivi_courriercsa SET 
             id_courrier = ?, 
             destinataire = ?, 
             statut_1 = ?, 
             statut_2 = ?, 
             statut_3 = ?
             WHERE id_suivi = ?";
    
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        return ['success' => false, 'error' => $mysqli->error];
    }

    $stmt->bind_param("issssi", 
        $data['id_courrier'],
        $data['destinataire'],
        $data['statut_1'],
        $data['statut_2'],
        $data['statut_3'],
        $id_suivi
    );

    if ($stmt->execute()) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

// Supprimer un suivi
function supprimerSuivi($mysqli, $id_suivi) {
    $query = "DELETE FROM suivi_courriercsa WHERE id_suivi = ?";
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        return ['success' => false, 'error' => $mysqli->error];
    }

    $stmt->bind_param("i", $id_suivi);

    if ($stmt->execute()) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

// Obtenir la liste des courriers pour les select
function getCourriersForSelect($mysqli) {
    $query = "SELECT id_courrier, Numero_Courrier, Objet FROM courriercsa ORDER BY date DESC";
    $result = $mysqli->query($query);

    $courriers = [];
    while ($row = $result->fetch_assoc()) {
        $courriers[] = $row;
    }

    return $courriers;
}

// Obtenir les statuts possibles
function getStatutsPossibles() {
    return [
        'CSA' => 'CSA',
        'DA' => 'DA',
        'ACP' => 'ACP',
        'DI' => 'DI',
        'DST' => 'DST',
        'DMG' => 'DMG', 
        'CHRONO' => 'CHRONO', 
    ];
}

// Obtenir la classe CSS pour un statut
function getStatusBadgeClass($statut) {
    switch ($statut) {
        case 'CSA':
            return 'success';
        case 'CHRONO':
            return 'info';
        case 'DA':
            return 'warning';
        case 'ACP':
            return 'primary';
        case 'DI':
            return 'danger text-white';
        case 'DST':
            return 'secondary';
        case 'DMG':
            return 'dark text-white';
        default:
            return 'light text-dark';
    }
}

function getCountTotalSuiviCourrier($mysqli) {
    $query = "SELECT COUNT(*) as total FROM suivi_courriercsa";
    $result = $mysqli->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        return (int)$row['total'];
    }
    return 0;
}
?>