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
$facture = getFactures($id_facture);
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

  // Configuration du chemin des factures
    $facturesDir = __DIR__ . '/../../uploads/factures/';
    $baseUrlPath = '../../uploads/factures/'; // Chemin relatif pour la BDD

    $pdfPath = $facture['facture_pdf'] ?? null; // Conserve le chemin existant par défaut
    $deletePdf = isset($_POST['delete_pdf']);
    $hasNewPdf = isset($_FILES['facture_pdf']) && $_FILES['facture_pdf']['error'] === UPLOAD_ERR_OK;

    // 1. Gestion de la suppression du PDF
    if ($deletePdf && !empty($facture['facture_pdf'])) {
        $fullPath = $facturesDir . basename($facture['facture_pdf']);
        if (file_exists($fullPath)) {
            if (!unlink($fullPath)) {
                $errors['facture_pdf'] = 'Erreur lors de la suppression du fichier existant';
            } else {
                $pdfPath = ''; // Indique que le PDF a été supprimé
            }
        }
    }

    // 2. Gestion du nouvel upload
    if ($hasNewPdf && empty($errors)) {
        $file = $_FILES['facture_pdf'];
        
        // Validation du fichier
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        // Vérifications
        if ($mimeType !== 'application/pdf') {
            $errors['facture_pdf'] = 'Seuls les fichiers PDF sont acceptés';
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB max
            $errors['facture_pdf'] = 'Le fichier ne doit pas dépasser 5MB';
        } elseif (!is_uploaded_file($file['tmp_name'])) {
            $errors['facture_pdf'] = 'Fichier uploadé invalide';
        }
        
        // Si tout est valide
        if (empty($errors)) {
            // Création du répertoire si nécessaire
            if (!file_exists($facturesDir)) {
                if (!mkdir($facturesDir, 0755, true)) {
                    $errors['facture_pdf'] = 'Impossible de créer le dossier de stockage';
                }
            }
            
            if (empty($errors)) {
                // Génération d'un nom de fichier sécurisé
                $extension = 'pdf';
                $newFilename = 'facture_' . uniqid() . '_' . time() . '.' . $extension;
                $destination = $facturesDir . $newFilename;
                
                // Déplacement du fichier
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Suppression de l'ancien fichier si existant
                    if (!empty($facture['facture_pdf'])) {
                        $oldFile = $facturesDir . basename($facture['facture_pdf']);
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                    $pdfPath = $baseUrlPath . $newFilename; // Chemin relatif pour la BDD
                } else {
                    $errors['facture_pdf'] = 'Erreur lors de l\'enregistrement du fichier';
                }
            }
        }
    }

    // 3. Si aucune modification de PDF n'est demandée, on conserve le chemin existant
    if (!$deletePdf && !$hasNewPdf && isset($facture['facture_pdf'])) {
        $pdfPath = $facture['facture_pdf'];
    }
    // Si pas d'erreurs, mise à jour en base
    if (empty($errors)) {
        try {
            if (modifierFacture($id_facture, $data, $pdfPath ?? $facture['facture_pdf'])) {
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