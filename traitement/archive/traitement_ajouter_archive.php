<?php
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
        
        // Vérification de la duplication du numéro
        $num_correspondance = trim($_POST['num_correspondance']);
        if (numeroCorrespondanceExiste($num_correspondance)) {
            throw new Exception("Le numéro de correspondance existe déjà.");
        }

        // Validation du fichier PDF
        if (empty($_FILES['pdf_archive']['tmp_name'])) {
            throw new Exception("Veuillez sélectionner un fichier PDF.");
        }

        $fileInfo = pathinfo($_FILES['pdf_archive']['name']);
        $extension = strtolower($fileInfo['extension'] ?? '');

        if ($extension !== 'pdf') {
            throw new Exception("Seuls les fichiers PDF sont acceptés.");
        }

        if ($_FILES['pdf_archive']['size'] > 5 * 1024 * 1024) {
            throw new Exception("Le fichier est trop volumineux (max 5Mo).");
        }

        // Préparation du dossier de stockage
        $uploadDir = '../../uploads/archives/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Génération d'un nom de fichier sécurisé
        $safeFilename = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $fileInfo['filename']);
        $fileName = uniqid() . '_' . $safeFilename . '.pdf';
        $uploadPath = $uploadDir . $fileName;
        
        // Déplacement du fichier
        if (!move_uploaded_file($_FILES['pdf_archive']['tmp_name'], $uploadPath)) {
            throw new Exception("Erreur lors du téléchargement du fichier.");
        }

        // Préparation des données pour l'insertion
        $data = [
            'type_archivage' => $_POST['type_archivage'],
            'num_correspondance' => $num_correspondance,
            'pdf_archive' => 'archives/' . $fileName,
            'commentaire' => $_POST['commentaire'] ?? null
        ];
        
        // Insertion dans la base
        if (!ajouterArchive($data)) {
            // Suppression du fichier en cas d'échec
            unlink($uploadPath);
            throw new Exception("Erreur lors de l'ajout dans la base de données.");
        }

        // Réponse JSON pour requête AJAX
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => "Archive ajoutée avec succès!",
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