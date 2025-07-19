<?php
session_start();
require_once('fonction_user.php');
require_once('connect.php');

// En-têtes de sécurité
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');



try {
    // Vérification de la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Méthode non autorisée', 405);
    }

    // Vérification CSRF
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        throw new RuntimeException('Erreur de sécurité CSRF', 403);
    }

    // Validation et nettoyage des données
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    
    $status = filter_input(INPUT_POST, 'status', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0, 'max_range' => 1]
    ]);

    if ($userId === false || $userId === null || $status === false || $status === null) {
        throw new InvalidArgumentException('Données invalides', 400);
    }

    // Journalisation avant modification
    error_log("Tentative de changement de statut pour l'utilisateur ID: $userId, nouveau statut: $status");

    // Mise à jour du statut
    $newStatus = toggleUserStatus($userId, $status);
    
    if ($newStatus === false) {
        throw new RuntimeException('Échec de la mise à jour du statut', 500);
    }

    // Journalisation après succès
    error_log("Statut utilisateur $userId changé avec succès. Nouveau statut: $newStatus");

    // Réponse JSON
    echo json_encode([
        'success' => true,
        'newStatus' => $newStatus,
        'message' => 'Statut mis à jour avec succès'
    ]);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (RuntimeException $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
    
    // Journalisation détaillée pour les erreurs inattendues
    error_log("Erreur inattendue: " . $e->getMessage() . "\n" . $e->getTraceAsString());
}