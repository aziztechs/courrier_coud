<?php
// Traitement de la suppression
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if (supprimerCourrier($connexion, $id)) {
        $_SESSION['message'] = "Le courrier a été supprimé avec succès.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Erreur lors de la suppression du courrier.";
        $_SESSION['message_type'] = "danger";
    }
    header("Location: liste_courrierscsa.php");
    exit();
}

// Récupération des paramètres de recherche
$search_term = $_GET['search'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$itemsPerPage = 5;

// Construction des filtres
$filters = [];
if (!empty($search_term)) {
    $filters['search'] = $search_term;
}
if (!empty($date_debut)) $filters['date_debut'] = $date_debut;
if (!empty($date_fin)) $filters['date_fin'] = $date_fin;

// Récupération unique des données
$courriers = getFilteredCourrier($connexion, $filters, $page, $itemsPerPage);
$totalCourriers = countFilteredCourriers($connexion, $filters);
$totalPages = ceil($totalCourriers / $itemsPerPage);

// Vérification du suivi pour chaque courrier
foreach ($courriers as &$courrier) {
    $query = "SELECT COUNT(*) as has_suivi FROM suivi_courriercsa WHERE id_courrier = ?";
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("i", $courrier['id_courrier']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $courrier['has_suivi'] = $row['has_suivi'] > 0;
    $stmt->close();
}
unset($courrier); // Détruire la référence