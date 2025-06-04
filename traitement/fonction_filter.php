<?php


// Connexion à la base de données
require('../../traitement/fonction.php');
include('../../activite.php');
require('../../traitement/recupImpuAlldep.php');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
$nature_filter = isset($_GET['nature_filter']) ? $_GET['nature_filter'] : '';
$type_filter = isset($_GET['type_filter']) ? $_GET['type_filter'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 5;

// Validate page number
if ($page < 1) {
    $page = 1;
}

$filters = [
    'search' => $_GET['search'] ?? '',
    'nature' => $_GET['nature'] ?? '',
    'type' => $_GET['type'] ?? '',
    'date_debut' => $_GET['date_debut'] ?? '',
    'date_fin' => $_GET['date_fin'] ?? ''
];

$page = $_GET['page'] ?? 1;
$itemsPerPage = 5;

$courriers = getFilteredCourriers($filters, $page, $itemsPerPage);
$totalCourriers = countFilteredCourriers($filters);


$totalPages = ceil($totalCourriers / $itemsPerPage);

// Adjust page if it's beyond total pages
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
    // Optionally redirect to the last valid page
    $query_params = http_build_query([
        'page' => $page,
        'search' => $search,
        'date_filter' => $date_filter,
        'nature_filter' => $nature_filter,
        'type_filter' => $type_filter
    ]);
    header("Location: ?$query_params");
    exit();
}