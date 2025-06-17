<?php
// Configuration de la pagination
$perPage = 5;
$currentPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
    'options' => [
        'default' => 1,
        'min_range' => 1
    ]
]);
$offset = ($currentPage - 1) * $perPage;

// Définition des filtres avec validation et assainissement
$allowedTypes = ['manuel', 'automatique', 'annuel', ''];
$allowedMotifs = ['traitement_termine', 'delai_depasse', 'demande_specifique', ''];

$filters = [
    'search' => filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'type_archivage' => in_array(filter_input(INPUT_GET, 'type_archivage'), $allowedTypes) 
        ? filter_input(INPUT_GET, 'type_archivage') : '',
    'motif_archivage' => in_array(filter_input(INPUT_GET, 'motif_archivage'), $allowedMotifs)
        ? filter_input(INPUT_GET, 'motif_archivage') : '',
    'date_archivage' => filter_input(INPUT_GET, 'date_archivage', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ''
];

// Validation de la date
if (!empty($filters['date_archivage'])) {
    $dateParts = explode('-', $filters['date_archivage']);
    if (count($dateParts) === 3 && checkdate($dateParts[1], $dateParts[2], $dateParts[0])) {
        $filters['date_archivage'] = $dateParts[0] . '-' . $dateParts[1] . '-' . $dateParts[2];
    } else {
        $filters['date_archivage'] = '';
    }
}

// Récupération des données avec gestion des erreurs
try {
    $archives = getArchivesWithFilters($filters, $offset, $perPage);
    $totalArchives = countArchivesWithFilters($filters);
    $totalPages = max(1, ceil($totalArchives / $perPage));
    
    // Correction de la page courante si elle dépasse le nombre total de pages
    if ($currentPage > $totalPages && $totalPages > 0) {
        $currentPage = $totalPages;
        $offset = ($currentPage - 1) * $perPage;
        $archives = getArchivesWithFilters($filters, $offset, $perPage);
    }
} catch (PDOException $e) {
    error_log("Erreur base de données: " . $e->getMessage());
    $archives = [];
    $totalArchives = 0;
    $totalPages = 1;
    $errorMessage = "Une erreur est survenue lors de la récupération des archives.";
} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    $archives = [];
    $totalArchives = 0;
    $totalPages = 1;
    $errorMessage = "Une erreur inattendue est survenue.";
}