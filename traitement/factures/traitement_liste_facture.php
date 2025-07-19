<?php

// 4. Filtrage et validation des entrées
global $connexion;
$filters = [
    'search' => filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'date_debut' => filter_input(INPUT_GET, 'date_debut', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'date_fin' => filter_input(INPUT_GET, 'date_fin', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'statut' => filter_input(INPUT_GET, 'statut', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ''
];

try {
    // 5. Construction sécurisée de la requête
    $sql = "SELECT * FROM facture WHERE 1=1";
    $params = [];
    $types = '';

    if (!empty($filters['search'])) {
        $sql .= " AND (numero_courrier LIKE ? OR expediteur LIKE ? OR numero_facture LIKE ? OR type_facture LIKE ?)";
        $search_term = "%{$filters['search']}%";
        $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
        $types .= 'ssss';
    }

    // Validation des dates
    if (!empty($filters['date_debut']) && DateTime::createFromFormat('Y-m-d', $filters['date_debut']) !== false) {
        $sql .= " AND DATE(date_arrive) >= ?";
        $params[] = $filters['date_debut'];
        $types .= 's';
    }
    
    if (!empty($filters['date_fin']) && DateTime::createFromFormat('Y-m-d', $filters['date_fin']) !== false) {
        $sql .= " AND DATE(date_arrive) <= ?";
        $params[] = $filters['date_fin'];
        $types .= 's';
    }

    if (!empty($filters['statut'])) {
        $sql .= " AND type_facture = ?";
        $params[] = $filters['statut'];
        $types .= 's';
    }

    $sql .= " ORDER BY id_facture DESC";

    // 6. Pagination sécurisée
    $results_per_page = filter_input(INPUT_GET, 'per_page', FILTER_VALIDATE_INT, [
        'options' => ['default' => 5, 'min_range' => 1, 'max_range' => 100]
    ]);
    
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
        'options' => ['default' => 1, 'min_range' => 1]
    ]);

    // Comptage des résultats
    $count_sql = "SELECT COUNT(*) as total FROM ($sql) as counted";
    $stmt = $connexion->prepare($count_sql);
    
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $total_factures = $stmt->get_result()->fetch_assoc()['total'];
    $total_pages = max(1, ceil($total_factures / $results_per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $results_per_page;

    // Requête paginée
    $sql .= " LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $results_per_page;
    $types .= 'ii';

    $stmt = $connexion->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $factures = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    error_log('[Factures] ' . date('Y-m-d H:i:s') . ' - ' . $e->getMessage());
    $factures = [];
    $total_factures = 0;
    $total_pages = 1;
    $page = 1;
}

// Génération du token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


?>