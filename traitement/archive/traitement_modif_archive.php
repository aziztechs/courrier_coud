<?php
// Vérifier si l'ID de l'archive est passé en paramètre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Identifiant d'archive invalide.";
    header('Location: liste_archive.php');
    exit();
}

$id_archive = (int)$_GET['id'];

// Récupérer les données de l'archive à modifier
$archive = getArchiveById($id_archive);

if (!$archive) {
    $_SESSION['error_message'] = "Archive introuvable.";
    header('Location: liste_archive.php');
    exit();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validation des champs obligatoires
        if (empty($_POST['type_archivage'])) {
            throw new Exception("Le type d'archivage est obligatoire.");
        }
        
        if (empty($_POST['num_correspondance'])) {
            throw new Exception("Le numéro de correspondance est obligatoire.");
        }
        
        if (empty($_POST['motif_archivage'])) {
            throw new Exception("Le motif d'archivage est obligatoire.");
        }

        // Préparer les données pour la mise à jour
        $data = [
            'id_archive' => $id_archive,
            'type_archivage' => $_POST['type_archivage'],
            'num_correspondance' => $_POST['num_correspondance'],
            'motif_archivage' => $_POST['motif_archivage'],
            'commentaire' => $_POST['commentaire'] ?? null
        ];

        // Gestion du fichier PDF si un nouveau fichier est uploadé
        if (isset($_FILES['pdf_archive']) && $_FILES['pdf_archive']['error'] === UPLOAD_ERR_OK) {
            // Vérifier l'extension du fichier
            $fileExtension = pathinfo($_FILES['pdf_archive']['name'], PATHINFO_EXTENSION);
            if (strtolower($fileExtension) !== 'pdf') {
                throw new Exception("Seuls les fichiers PDF sont acceptés.");
            }
            
            // Vérifier la taille du fichier (max 5Mo)
            if ($_FILES['pdf_archive']['size'] > 5 * 1024 * 1024) {
                throw new Exception("Le fichier est trop volumineux (max 5Mo).");
            }
            
            // Dossier de destination
            $uploadDir = '../uploads/';
            
            // Créer le dossier s'il n'existe pas
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Générer un nom de fichier unique
            $fileName = 'archives/' . uniqid() . '_' . basename($_FILES['pdf_archive']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            // Déplacer le fichier uploadé
            if (move_uploaded_file($_FILES['pdf_archive']['tmp_name'], $uploadFile)) {
                // Supprimer l'ancien fichier PDF
                if (!empty($archive['pdf_archive']) && file_exists($uploadDir . $archive['pdf_archive'])) {
                    unlink($uploadDir . $archive['pdf_archive']);
                }
                
                $data['pdf_archive'] = $fileName;
            } else {
                throw new Exception("Erreur lors du téléchargement du fichier.");
            }
        } else {
            // Conserver l'ancien fichier PDF si aucun nouveau n'est uploadé
            $data['pdf_archive'] = $archive['pdf_archive'];
        }
        
        // Mettre à jour l'archive
        if (modifierArchive($data)) {
            $_SESSION['success_message'] = "L'archive a été modifiée avec succès.";
            header('Location: liste_archive.php');
            exit();
        } else {
            throw new Exception("Erreur lors de la modification de l'archive dans la base de données.");
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}