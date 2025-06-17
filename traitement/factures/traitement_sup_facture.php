<?php
// Vérification de l'ID et des droits
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['error_message'] = 'ID de facture invalide';
    header('Location: liste_factures.php');
    exit();
}

$id_facture = (int)$_GET['id'];

// Vérifier si la facture existe
$facture = get_facture($id_facture);
if (!$facture) {
    $_SESSION['error_message'] = 'Facture introuvable';
    header('Location: liste_factures.php');
    exit();
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Supprimer le fichier PDF associé s'il existe
        if (!empty($facture['facture_pdf']) && file_exists($facture['facture_pdf'])) {
            unlink($facture['facture_pdf']);
        }
        
        // Supprimer la facture de la base de données
        if (supprimer_facture($id_facture)) {
            $_SESSION['success_message'] = 'La facture #'.$facture['numero_facture'].' a été supprimée avec succès';
        } else {
            $_SESSION['error_message'] = 'Erreur lors de la suppression de la facture';
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Erreur technique : '.$e->getMessage();
    }
    
    header('Location: liste_factures.php');
    exit();
}
