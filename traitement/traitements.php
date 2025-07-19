<?php
require_once('fonction.php');





/** ############# Traitement liste facture  ###############*/

// Configuration de la pagination
$factures_par_page = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $factures_par_page;

// Récupération des paramètres de recherche
$filters = [
    'search' => isset($_GET['search']) ? mysqli_real_escape_string($connexion, $_GET['search']) : '',
    'type_facture' => isset($_GET['type_facture']) ? mysqli_real_escape_string($connexion, $_GET['type_facture']) : '',
    'date_arrive' => isset($_GET['date_arrive']) ? mysqli_real_escape_string($connexion, $_GET['date_arrive']) : ''
];

// Construction de la requête de recherche
$where = [];
if (!empty($filters['search'])) {
    $where[] = "(numero_facture LIKE '%{$filters['search']}%' OR 
    numero_courrier LIKE '%{$filters['search']}%' OR 
    expediteur LIKE '%{$filters['search']}%')";
}
if (!empty($filters['type_facture'])) {
    $where[] = "type_facture = '{$filters['type_facture']}'";
}
if (!empty($filters['date_arrive'])) {
    $where[] = "date_arrive = '{$filters['date_arrive']}'";
}
$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Récupération des factures
$sql = "SELECT * FROM facture $where_clause ORDER BY id_facture DESC LIMIT $offset, $factures_par_page";
$result = mysqli_query($connexion, $sql);
$factures = [];
while ($row = mysqli_fetch_assoc($result)) {
    $factures[] = $row;
}

// Récupération du nombre total de factures pour la pagination
$count_sql = "SELECT COUNT(*) as total FROM facture $where_clause";
$count_result = mysqli_query($connexion, $count_sql);
$total_factures = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_factures / $factures_par_page);

// Récupération des types de factures disponibles
$types_sql = "SELECT DISTINCT type_facture FROM facture ORDER BY type_facture";
$types_result = mysqli_query($connexion, $types_sql);
$types = [];
while ($row = mysqli_fetch_assoc($types_result)) {
    $types[] = $row['type_facture'];
}
/** ############# Fin Traitement liste facture  ###############*/

/** #############  Traitement ajouter suivi  ###############*/
