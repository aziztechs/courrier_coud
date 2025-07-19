<?php
$errors = [];
$data = [];
$show_redirect_message = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage et validation des données
    $data = [
        'date_arrive'      => trim($_POST['date_arrive'] ?? ''),
        'numero_courrier'  => trim($_POST['numero_courrier'] ?? ''),
        'expediteur'       => trim($_POST['expediteur'] ?? ''),
        'numero_facture'   => trim($_POST['numero_facture'] ?? ''),
        'decade'           => trim($_POST['decade'] ?? ''),
        'montant_ttc'      => str_replace(',', '.', trim($_POST['montant_ttc'] ?? '')),
        'type_facture'     => trim($_POST['type_facture'] ?? '')
    ];

    // Validation des champs obligatoires
    $required_fields = ['date_arrive', 'numero_courrier', 'expediteur', 'numero_facture', 'montant_ttc', 'type_facture'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $errors[$field] = 'Ce champ est obligatoire.';
        }
    }

    // Validation spécifique des champs
    if (!empty($data['date_arrive']) && !DateTime::createFromFormat('Y-m-d', $data['date_arrive'])) {
        $errors['date_arrive'] = 'Format de date invalide (YYYY-MM-DD attendu).';
    }

   // Remplacer la validation du montant par :
    if (!empty($data['montant_ttc'])) {
        // Nettoyage : supprime tous les caractères non numériques sauf virgule et point
        $montant = preg_replace('/[^0-9,.]/', '', $data['montant_ttc']);
        // Remplace les virgules par des points pour la conversion
        $montant = str_replace(',', '.', $montant);
        
        // Validation que c'est un nombre positif avec optionnellement 2 décimales
        if (!is_numeric($montant) || $montant <= 0) {
            $errors['montant_ttc'] = 'Le montant doit être un nombre positif (ex: 13000000 ou 13000000.00)';
        } else {
            // Conversion en float avec 2 décimales maximum
            $data['montant_ttc'] = round((float)$montant, 2);
        }
    }

    // Vérification des doublons
    if (!isset($errors['numero_courrier']) && numero_courrier_existe($data['numero_courrier'])) {
        $errors['numero_courrier'] = 'Ce numéro de courrier existe déjà.';
    }

    if (!isset($errors['numero_facture']) && numero_facture_existe($data['numero_facture'])) {
        $errors['numero_facture'] = 'Ce numéro de facture existe déjà.';
    }

    // Traitement du fichier PDF
    $pdf_path = null;
    if (!empty($_FILES['facture_pdf']['name'])) {
        $file = $_FILES['facture_pdf'];
        
        // Vérification erreur upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['facture_pdf'] = $this->getUploadErrorMessage($file['error']);
        } else {
            // Vérification type MIME
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            
            if ($mime !== 'application/pdf') {
                $errors['facture_pdf'] = 'Seuls les fichiers PDF sont acceptés.';
            }
            
            // Vérification taille
            $max_size = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $max_size) {
                $errors['facture_pdf'] = 'Le fichier ne doit pas dépasser '.formatFileSize($max_size).'.';
            }
            
            // Si tout est OK, préparer l'upload
            if (!isset($errors['facture_pdf'])) {
                $upload_dir = '../../uploads/factures/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $safe_filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-]/', '_', $file['name']);
                $pdf_path = $upload_dir . $safe_filename;
                
                if (!move_uploaded_file($file['tmp_name'], $pdf_path)) {
                    $errors['facture_pdf'] = 'Erreur lors de l\'enregistrement du fichier.';
                    $pdf_path = null;
                }
            }
        }
    }

    // Si aucune erreur, procéder à l'enregistrement
    if (empty($errors)) {
        // Conversion du montant en float
        $data['montant_ttc'] = (float)$data['montant_ttc'];
        
        // Enregistrement avec récupération de l'ID
        $id_facture = ajouter_facture_retour_id($data, $pdf_path);
        
        if ($id_facture) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'La facture a été ajoutée avec succès.'
            ];
            
            // Journalisation de l'action
            activity_log($_SESSION['username'], 'Ajout facture #'.$id_facture);
            
            header('Location: liste_factures.php');
            exit;
        } else {
            // Nettoyage en cas d'échec
            if ($pdf_path && file_exists($pdf_path)) {
                unlink($pdf_path);
            }
            $errors['general'] = 'Une erreur est survenue lors de l\'enregistrement dans la base de données.';
        }
    }
}

/**
 * Fonction utilitaire pour les messages d'erreur d'upload
 */
function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'Le fichier est trop volumineux.';
        case UPLOAD_ERR_PARTIAL:
            return 'Le téléchargement du fichier a été interrompu.';
        case UPLOAD_ERR_NO_FILE:
            return 'Aucun fichier n\'a été téléchargé.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Dossier temporaire manquant.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Échec de l\'écriture du fichier sur le disque.';
        case UPLOAD_ERR_EXTENSION:
            return 'Une extension PHP a arrêté le téléchargement.';
        default:
            return 'Erreur inconnue lors du téléchargement.';
    }
}

/**
 * Formatage de la taille des fichiers
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return '1 byte';
    } else {
        return '0 bytes';
    }
}

/**
 * Fonction pour journaliser les actions utilisateur
 */
function activity_log($username, $action) {
    global $connexion;
    $sql = "INSERT INTO activity_log (username, action, activity_date) VALUES (?, ?, NOW())";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param("ss", $username, $action);
    $stmt->execute();
}

/**
 * Version améliorée de la fonction d'ajout qui retourne l'ID
 */
function ajouter_facture_retour_id($data, $pdf_path = null) {
    global $connexion;
    
    try {
        $sql = "INSERT INTO facture 
                (date_arrive, numero_courrier, expediteur, numero_facture, decade, montant_ttc, type_facture, facture_pdf) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param(
            "sssssdss",
            $data['date_arrive'],
            $data['numero_courrier'],
            $data['expediteur'],
            $data['numero_facture'],
            $data['decade'],
            $data['montant_ttc'],
            $data['type_facture'],
            $pdf_path
        );
        
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        
        return false;
    } catch (mysqli_sql_exception $e) {
        error_log("Erreur ajout facture: " . $e->getMessage());
        return false;
    }
}