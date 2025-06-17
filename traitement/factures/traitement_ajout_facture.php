<?php



$errors = [];
$success = false;
$data = [
    'date_arrive' => '',
    'numero_courrier' => '',
    'expediteur' => '',
    'numero_facture' => '',
    'decade' => '',
    'montant_ttc' => '',
    'type_facture' => ''
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
    $data['type_facture'] = $_POST['type_facture'] ?? 'Classique';

    // Validation des champs
    if (empty($data['date_arrive'])) {
        $errors['date_arrive'] = 'La date d\'arrivée est obligatoire';
    } elseif (!DateTime::createFromFormat('Y-m-d', $data['date_arrive'])) {
        $errors['date_arrive'] = 'Format de date invalide';
    }

    if (empty($data['numero_courrier'])) {
        $errors['numero_courrier'] = 'Le numéro de courrier est obligatoire';
    }

    if (empty($data['expediteur'])) {
        $errors['expediteur'] = 'L\'expéditeur est obligatoire';
    }

    if (empty($data['numero_facture'])) {
        $errors['numero_facture'] = 'Le numéro de facture est obligatoire';
    }

    if (empty($data['montant_ttc'])) {
        $errors['montant_ttc'] = 'Le montant est obligatoire';
    } elseif (!is_numeric($data['montant_ttc'])) {
        $errors['montant_ttc'] = 'Le montant doit être un nombre';
    } elseif ($data['montant_ttc'] <= 0) {
        $errors['montant_ttc'] = 'Le montant doit être positif';
    }

    // Gestion du fichier PDF
    $pdfPath = null;
    if (isset($_FILES['facture_pdf']) && $_FILES['facture_pdf']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['facture_pdf'];
        
        // Vérification du type de fichier
        $fileType = mime_content_type($file['tmp_name']);
        if ($fileType !== 'application/pdf') {
            $errors['facture_pdf'] = 'Seuls les fichiers PDF sont acceptés';
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB max
            $errors['facture_pdf'] = 'Le fichier ne doit pas dépasser 5MB';
        } else {
            // Création du répertoire si inexistant
            $uploadDir = '../../uploads/factures/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Génération d'un nom de fichier unique
            $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $file['name']);
            $pdfPath = $uploadDir . $fileName;
            
            // Déplacement du fichier
            if (!move_uploaded_file($file['tmp_name'], $pdfPath)) {
                $errors['facture_pdf'] = 'Erreur lors de l\'upload du fichier';
                $pdfPath = null;
            }
        }
    }

    if (empty($errors)) {
        try {
            if (ajouter_facture($data, $pdfPath)) {
                // Message de succès en session pour l'affichage après redirection
                $_SESSION['success_message'] = 'La facture a été ajoutée avec succès';
                
                // Redirection après 2 secondes
                header("Refresh: 2; URL=liste_factures.php");
                $show_redirect_message = true;
                
                // On ne nettoie pas le formulaire pour éviter le flash entre l'affichage et la redirection
            } else {
                $errors['general'] = 'Erreur lors de l\'ajout de la facture';
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