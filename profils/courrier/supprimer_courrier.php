<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

// Vérification des permissions
if ($_SESSION['Fonction'] === 'assistant_courrier') {
    $_SESSION['error'] = "Vous n'avez pas les permissions nécessaires pour cette action";
    header('Location: liste_courrier.php');
    exit();
}

require_once('../../traitement/fonction.php');

// Vérification de l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de courrier invalide";
    header('Location: liste_courrier.php');
    exit();
}

$id_courrier = (int)$_GET['id'];

try {
    // Récupération des infos du courrier avant suppression
    $courrier = getCourrierById($id_courrier);
    
    if (!$courrier) {
        $_SESSION['error'] = "Courrier introuvable";
        header('Location: liste_courrier.php');
        exit();
    }

    // Suppression du fichier PDF associé s'il existe
    if (!empty($courrier['pdf']) && file_exists("../uploads/" . $courrier['pdf'])) {
        unlink("../uploads/" . $courrier['pdf']);
    }

    // Suppression en base de données
    $stmt = $connexion->prepare("DELETE FROM courrier WHERE id_courrier = ?");
    $stmt->bind_param("i", $id_courrier);
    
    if ($stmt->execute()) {
        // Journalisation de l'activité
        $action = "Suppression du courrier #" . $courrier['Numero_courrier'];
        logActivity($_SESSION['username'], $action);
        
        $_SESSION['success'] = "Le courrier a été supprimé avec succès";
    } else {
        throw new Exception("Erreur lors de la suppression en base de données");
    }
    
} catch (Exception $e) {
    error_log("Erreur suppression courrier: " . $e->getMessage());
    $_SESSION['error'] = "Une erreur est survenue lors de la suppression";
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}

header('Location: liste_courrier.php');
exit();
?>