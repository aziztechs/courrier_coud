<?php
// Définir l'environnement si non défini
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'production'); // ou 'development' selon votre besoin
}
session_start();

// 1. Vérification de l'authentification et des autorisations
if (empty($_SESSION['username']) || $_SESSION['Fonction'] === 'assistant_courrier') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

require_once __DIR__ . '/../../traitement/fonction_facture.php';

// 2. Vérification du token CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
    exit();
}

// 3. Validation des données
if (empty($_POST['id_facture']) || !ctype_digit($_POST['id_facture'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID facture invalide']);
    exit();
}

$id_facture = (int)$_POST['id_facture'];

try {
    // 4. Vérification de l'existence de la facture
    $facture = getFactureById($id_facture);
    if (!$facture) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Facture introuvable']);
        exit();
    }

    // 5. Suppression de la facture
    $resultat = supprimerFacture($id_facture);
    
    if ($resultat) {
        // Journalisation de la suppression
        error_log("Facture #$id_facture supprimée par l'utilisateur " . $_SESSION['username']);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Facture #' . $id_facture . ' supprimée avec succès',
            'deleted_id' => $id_facture
        ]);
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Échec de la suppression en base de données'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log('Erreur suppression facture #' . $id_facture . ': ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur',
        'error_details' => (ENVIRONMENT === 'development') ? $e->getMessage() : null
    ]);
}