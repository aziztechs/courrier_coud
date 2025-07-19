<?php

// Récupération et validation des paramètres de filtrage
$filters = [
    'search' => filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'date_reception' => filter_input(INPUT_GET, 'date_reception', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'statut' => filter_input(INPUT_GET, 'statut', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ''
];

try {
    // Construction sécurisée de la requête SQL
    $sql = "SELECT * FROM suivi_courrier WHERE 1=1";
    $params = [];
    $types = '';

    if (!empty($filters['search'])) {
        $sql .= " AND (numero LIKE ? OR expediteur LIKE ? OR objet LIKE ? OR destinataire LIKE ?)";
        $search_term = "%{$filters['search']}%";
        $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
        $types .= str_repeat('s', 4); // 4 paramètres de type string
    }

    if (!empty($filters['date_reception'])) {
        if (DateTime::createFromFormat('Y-m-d', $filters['date_reception']) !== false) {
            $sql .= " AND DATE(date_reception) = ?";
            $params[] = $filters['date_reception'];
            $types .= 's';
        }
    }

    if (!empty($filters['statut'])) {
        $sql .= " AND (statut_1 = ? OR statut_2 = ? OR statut_3 = ?)";
        $params = array_merge($params, [$filters['statut'], $filters['statut'], $filters['statut']]);
        $types .= str_repeat('s', 3); // 3 paramètres de type string
    }

    $sql .= " ORDER BY id DESC";

    // Pagination sécurisée
    $results_per_page = filter_input(INPUT_GET, 'per_page', FILTER_VALIDATE_INT, [
        'options' => ['default' => 5, 'min_range' => 1, 'max_range' => 100]
    ]);
    
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
        'options' => ['default' => 1, 'min_range' => 1]
    ]);

    // Requête pour le nombre total de résultats
    $count_sql = str_replace('SELECT *', 'SELECT COUNT(*) as total', $sql);
    $stmt = $connexion->prepare($count_sql);
    
    if (!$stmt) {
        throw new Exception("Erreur de préparation de la requête: " . $connexion->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Erreur d'exécution de la requête: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Erreur de récupération des résultats: " . $connexion->error);
    }

    $total_results = $result->fetch_assoc()['total'];
    $total_pages = max(1, ceil($total_results / $results_per_page));
    $page = min($page, $total_pages); // S'assurer que la page est dans les limites
    $offset = ($page - 1) * $results_per_page;

    // Requête pour les résultats paginés
    $sql .= " LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $results_per_page;
    $types .= 'ii';

    $stmt = $connexion->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Erreur de préparation de la requête paginée: " . $connexion->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Erreur d'exécution de la requête paginée: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Erreur de récupération des résultats paginés: " . $connexion->error);
    }

    $suivis = $result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    // Gestion des erreurs (à adapter selon votre framework/application)
    error_log($e->getMessage());
    $suivis = [];
    $total_results = 0;
    $total_pages = 1;
    $page = 1;
}
?>