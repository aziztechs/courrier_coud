<?php


// Vérifier que l'ID est présent et valide
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: liste_factures.php');
    exit();
}

$id_facture = (int)$_GET['id'];
$errors = [];
$success = false;

// Récupérer la facture existante
$facture = get_facture($id_facture);
if (!$facture) {
    header('Location: liste_factures.php');
    exit();
}

// Initialiser les données du formulaire
$data = [
    'date_arrive' => $facture['date_arrive'],
    'numero_courrier' => $facture['numero_courrier'],
    'expediteur' => $facture['expediteur'],
    'numero_facture' => $facture['numero_facture'],
    'decade' => $facture['decade'],
    'montant_ttc' => $facture['montant_ttc'],
    'type_facture' => $facture['type_facture']
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données
    $data['date_arrive'] = $_POST['date_arrive'] ?? '';
    $data['numero_courrier'] = trim($_POST['numero_courrier'] ?? '');
    $data['expediteur'] = trim($_POST['expediteur'] ?? '');
    $data['numero_facture'] = trim($_POST['numero_facture'] ?? '');
    $data['decade'] = trim($_POST['decade'] ?? '');
    $data['montant_ttc'] = str_replace(',', '.', $_POST['montant_ttc'] ?? '');
    $data['type_facture'] = $_POST['type_facture'] ?? '';

    // Validation des champs (identique à ajouter_facture.php)
    // [...] (mêmes validations que dans ajouter_facture.php)

    // Gestion du fichier PDF
    $pdfPath = null;
    $deletePdf = isset($_POST['delete_pdf']);
    $hasNewPdf = isset($_FILES['facture_pdf']) && $_FILES['facture_pdf']['error'] === UPLOAD_ERR_OK;

    if ($deletePdf && !empty($facture['facture_pdf'])) {
        // Suppression du PDF existant
        if (file_exists($facture['facture_pdf'])) {
            unlink($facture['facture_pdf']);
        }
        $pdfPath = ''; // Chaîne vide pour indiquer la suppression en base
    } elseif ($hasNewPdf) {
        // Upload d'un nouveau PDF
        $file = $_FILES['facture_pdf'];
        
        // Vérification du type et taille
        $fileType = mime_content_type($file['tmp_name']);
        if ($fileType !== 'application/pdf') {
            $errors['facture_pdf'] = 'Seuls les fichiers PDF sont acceptés';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors['facture_pdf'] = 'Le fichier ne doit pas dépasser 5MB';
        } else {
            // Création du répertoire si inexistant
            $uploadDir = '../../uploads/factures/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Génération d'un nom unique
            $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $file['name']);
            $pdfPath = $uploadDir . $fileName;
            
            // Déplacement du fichier
            if (!move_uploaded_file($file['tmp_name'], $pdfPath)) {
                $errors['facture_pdf'] = 'Erreur lors de l\'upload du fichier';
            } else {
                // Suppression de l'ancien PDF si existant
                if (!empty($facture['facture_pdf']) && file_exists($facture['facture_pdf'])) {
                    unlink($facture['facture_pdf']);
                }
            }
        }
    }

    // Si pas d'erreurs, mise à jour en base
    if (empty($errors)) {
        try {
            if (modifier_facture($id_facture, $data, $pdfPath ?? $facture['facture_pdf'])) {
                $_SESSION['success_message'] = 'La facture a été modifiée avec succès';
                header("Refresh: 2; URL=liste_factures.php");
                $show_redirect_message = true;
            } else {
                $errors['general'] = 'Erreur lors de la modification de la facture';
                // Suppression du nouveau PDF uploadé si la mise à jour a échoué
                if ($pdfPath && file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
            }
        } catch (Exception $e) {
            $errors['general'] = 'Erreur technique : ' . $e->getMessage();
            if ($pdfPath && file_exists($pdfPath)) {
                unlink($pdfPath);
            }
        }
    }
}