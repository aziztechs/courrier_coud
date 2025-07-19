<?php
session_start();
// Vérification de session plus robuste
if (empty($_SESSION['username'])) {
    $_SESSION['error'] = "Session expirée ou non authentifiée";
    header('Location: /courrier_coud/');
    exit();
}

require '../../traitement/fonction_user.php';
require '../../traitement/connect.php';

// Vérification CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Erreur de sécurité (CSRF Token invalide)";
    header('Location: users.php');
    exit();
}

// Validation de l'ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => [
        'min_range' => 1
    ]
]);

if (!$id) {
    $_SESSION['error'] = "ID utilisateur invalide";
    header('Location: users.php');
    exit();
}

// Empêcher l'auto-suppression
if ($id === (int)($_SESSION['user_id'] ?? 0)) {
    $_SESSION['error'] = "Action interdite : Vous ne pouvez pas supprimer votre propre compte";
    header('Location: users.php');
    exit();
}

try {
    // Journalisation avant suppression
    error_log("Tentative de suppression utilisateur ID: $id par " . $_SESSION['username']);
    
    if (supprimerUtilisateur($id)) {
        $_SESSION['success'] = "Utilisateur supprimé avec succès";
        
        // Journalisation du succès
        error_log("Suppression réussie de l'utilisateur ID: $id");
    }
} catch (RuntimeException $e) {
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    
    // Journalisation détaillée de l'erreur
    error_log("Échec suppression utilisateur ID $id - " . $e->getMessage());
} catch (Exception $e) {
    $_SESSION['error'] = "Une erreur technique est survenue";
    error_log("Erreur technique suppression utilisateur - " . $e->getMessage());
}

header('Location: users.php');
exit();
?>