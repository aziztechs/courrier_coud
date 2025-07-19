<?php
// Récupérer et valider l'ID de l'archive
$id_archive = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_archive) {
    $_SESSION['error_message'] = "Identifiant d'archive invalide.";
    header('Location: liste_archive.php');
    exit();
}

// Récupérer les données de l'archive
$archive = getArchiveById($id_archive);
if (!$archive) {
    $_SESSION['error_message'] = "Archive introuvable.";
    header('Location: liste_archive.php');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validation des champs obligatoires
        $requiredFields = ['type_archivage', 'num_correspondance'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Le champ $field est obligatoire.");
            }
        }

        // Préparation des données
        $data = [
            'id_archive' => $id_archive,
            'type_archivage' => $_POST['type_archivage'],
            'num_correspondance' => trim($_POST['num_correspondance']),
            'commentaire' => $_POST['commentaire'] ?? null,
            'pdf_archive' => $archive['pdf_archive'] // Valeur par défaut
        ];

        // Gestion du fichier PDF si un nouveau est uploadé
        if (!empty($_FILES['pdf_archive']['tmp_name'])) {
            $fileInfo = pathinfo($_FILES['pdf_archive']['name']);
            $extension = strtolower($fileInfo['extension'] ?? '');

            if ($extension !== 'pdf') {
                throw new Exception("Seuls les fichiers PDF sont acceptés.");
            }

            if ($_FILES['pdf_archive']['size'] > 5 * 1024 * 1024) {
                throw new Exception("Le fichier est trop volumineux (max 5Mo).");
            }

            $uploadDir = '../../uploads/archives/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Génération d'un nom de fichier sécurisé
            $safeFilename = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $fileInfo['filename']);
            $fileName = uniqid() . '_' . $safeFilename . '.pdf';
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['pdf_archive']['tmp_name'], $uploadPath)) {
                // Supprimer l'ancien fichier PDF s'il existe
                if (!empty($archive['pdf_archive']) && file_exists('../../uploads/' . $archive['pdf_archive'])) {
                    unlink('../../uploads/' . $archive['pdf_archive']);
                }
                $data['pdf_archive'] = 'archives/' . $fileName;
            } else {
                throw new Exception("Erreur lors du téléchargement du fichier.");
            }
        }

        // Mise à jour dans la base
        if (!modifierArchive($data)) {
            throw new Exception("Erreur lors de la mise à jour dans la base de données.");
        }

        // Réponse JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => "Archive modifiée avec succès!",
            'redirect' => "liste_archive.php"
        ]);
        exit();

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit();
    }
}

// Récupération des numéros existants pour l'autocomplétion
$correspondances = getNumerosCorrespondance();

$enumValues = getEnumValuesMysqli($connexion, 'archive', 'type_archivage');

// Valeurs par défaut si la récupération échoue
if (empty($enumValues)) {
    $enumValues = [
        'depart', 'decision', 'attestation', 'note_service', 
        'etat_paiement', 'etat_salaire', 'remboursement',
        'circulaire', 'note_information', 'autorisation_engagement', 
        'bordereau'
    ];
}