<?php
require_once('fonction.php'); // Pour la connexion à la base
require_once('fonction_archive.php'); // Pour les fonctions d'archivage

// Configuration de la pagination
$perPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, $currentPage);
$offset = ($currentPage - 1) * $perPage;

// Définition des filtres avec validation
$allowedTypes = ['manuel', 'automatique', 'annuel', ''];

$filters = [
    'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
    'type_archivage' => isset($_GET['type_archivage']) && in_array($_GET['type_archivage'], $allowedTypes) 
        ? $_GET['type_archivage'] : '',
    'date_debut' => isset($_GET['date_debut']) ? $_GET['date_debut'] : '',
    'date_fin' => isset($_GET['date_fin']) ? $_GET['date_fin'] : ''
];

// Validation des dates
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Nettoyage des dates invalides
if (!empty($filters['date_debut']) && !validateDate($filters['date_debut'])) {
    $filters['date_debut'] = '';
}

if (!empty($filters['date_fin']) && !validateDate($filters['date_fin'])) {
    $filters['date_fin'] = '';
}

// Correction si date_debut > date_fin
if (!empty($filters['date_debut']) && !empty($filters['date_fin']) 
    && $filters['date_debut'] > $filters['date_fin']) {
    $temp = $filters['date_debut'];
    $filters['date_debut'] = $filters['date_fin'];
    $filters['date_fin'] = $temp;
}

// Adaptation des filtres pour utiliser nos fonctions
$adaptedFilters = [
    'search' => $filters['search'],
    'type_archivage' => $filters['type_archivage']
];

// Ajout du filtre de date si valide
if (!empty($filters['date_debut'])) {
    $adaptedFilters['date_archivage'] = $filters['date_debut'];
}

// Récupération des données avec nos fonctions
try {
    // Récupération des archives filtrées
    $archives = getArchivesWithFilters($adaptedFilters, $offset, $perPage);
    
    // Comptage total des archives filtrées
    $totalArchives = countArchivesWithFilters($adaptedFilters);
    
    // Calcul du nombre total de pages
    $totalPages = max(1, ceil($totalArchives / $perPage));
    
    // Correction de la page courante si nécessaire
    if ($currentPage > $totalPages && $totalPages > 0) {
        $currentPage = $totalPages;
        $offset = ($currentPage - 1) * $perPage;
        $archives = getArchivesWithFilters($adaptedFilters, $offset, $perPage);
    }
    
    $errorMessage = '';
} catch (Exception $e) {
    $errorMessage = "Erreur lors de la récupération des archives: " . $e->getMessage();
    $archives = [];
    $totalArchives = 0;
    $totalPages = 1;
}