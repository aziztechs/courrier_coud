<?php
session_start();

// Vérification de session
if (empty($_SESSION['username'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Non authentifié']));
}

require 'fonction_user.php';
require 'connect.php';

// Vérification CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Content-Type: application/json');
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Token CSRF invalide']));
}

// Validation de l'ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    header('Content-Type: application/json');
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'ID utilisateur invalide']));
}

// Empêcher l'auto-suppression
if ($id === (int)($_SESSION['user_id'] ?? 0)) {
    header('Content-Type: application/json');
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte']));
}

try {
    if (supprimerUtilisateur($id)) {
        // Journalisation
        error_log("Suppression réussie de l'utilisateur ID: $id par " . $_SESSION['username']);
        echo json_encode([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    } else {
        throw new RuntimeException("Échec de la suppression en base de données");
    }
} catch (RuntimeException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    error_log("Erreur suppression utilisateur ID $id - " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur technique: ' . $e->getMessage()
    ]);
}