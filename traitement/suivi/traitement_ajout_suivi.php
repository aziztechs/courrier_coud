<?php

// Vérification de l'authentification
if (empty($_SESSION['username'])) {
    header('Location: /courrier_coud/');
    exit();
}

require_once('../../traitement/fonction_suivi_courrier.php');

// Initialisation des variables
$errors = [];
$formData = [
    'numero' => '',
    'date_reception' => date('Y-m-d'),
    'expediteur' => '',
    'objet' => '',
    'destinataire' => '',
    'statut_1' => 'CSA', // Valeur par défaut
    'statut_2' => '',
    'statut_3' => '',
    'statut_1_autre' => '',
    'statut_2_autre' => '',
    'statut_3_autre' => ''
];

// Traitement du formulaire initial (étape 1)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['confirm'])) {
    // Nettoyage des données
    $formData = array_map('trim', $_POST);

    // Validation des champs requis
    $requiredFields = ['numero', 'date_reception', 'expediteur', 'objet', 'destinataire', 'statut_1'];
    foreach ($requiredFields as $field) {
        if (empty($formData[$field])) {
            $errors[$field] = 'Ce champ est obligatoire';
        }
    }

    // Validation spécifique pour le statut "AUTRE"
    if ($formData['statut_1'] === 'AUTRE' && empty($formData['statut_1_autre'])) {
        $errors['statut_1_autre'] = 'Veuillez spécifier le statut';
    }

    // Validation du format de date
    if (!empty($formData['date_reception'])) {
        $date = DateTime::createFromFormat('Y-m-d', $formData['date_reception']);
        if (!$date || $date->format('Y-m-d') !== $formData['date_reception']) {
            $errors['date_reception'] = 'Format de date invalide (YYYY-MM-DD attendu)';
        }
    }

    // Validation du format du numéro
    if (!empty($formData['numero']) && !preg_match('/^\d{4}-\d{3}$/', $formData['numero'])) {
        $errors['numero'] = 'Format invalide (doit être YYYY-NNN)';
    }

    // Si aucune erreur, préparer la confirmation
    if (empty($errors)) {
        $_SESSION['form_data'] = $formData;
        header('Location: ajout_suivi.php?confirm=1');
        exit();
    }
}

// Traitement de la confirmation (étape 2)
if (isset($_GET['confirm']) && $_GET['confirm'] == '1' && isset($_SESSION['form_data'])) {
    $formData = $_SESSION['form_data'];
    
    // Traitement des statuts "AUTRE"
    if ($formData['statut_1'] === 'AUTRE') {
        $formData['statut_1'] = $formData['statut_1_autre'];
    }
    if ($formData['statut_2'] === 'AUTRE') {
        $formData['statut_2'] = $formData['statut_2_autre'];
    }
    if ($formData['statut_3'] === 'AUTRE') {
        $formData['statut_3'] = $formData['statut_3_autre'];
    }

    try {
        // Vérification de l'unicité du numéro
        $checkStmt = $connexion->prepare("SELECT id FROM suivi_courrier WHERE numero = ? LIMIT 1");
        $checkStmt->bind_param("s", $formData['numero']);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $errors['numero'] = "Ce numéro de suivi existe déjà";
        } else {
            // Insertion dans la base
            $stmt = $connexion->prepare("INSERT INTO suivi_courrier
                                  (numero, date_reception, expediteur, objet, destinataire, statut_1, statut_2, statut_3) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", 
                $formData['numero'],
                $formData['date_reception'],
                $formData['expediteur'],
                $formData['objet'],
                $formData['destinataire'],
                $formData['statut_1'],
                $formData['statut_2'],
                $formData['statut_3']
            );
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = [
                    'title' => 'Succès!',
                    'text' => 'Le suivi a été ajouté avec succès',
                    'icon' => 'success',
                    'timer' => 2000,
                    'redirect' => 'liste_suivi_courrier.php'
                ];
                
                // Nettoyer les données de session
                unset($_SESSION['form_data']);
                
                header('Location: ajout_suivi.php');
                exit();
            } else {
                throw new Exception("Erreur lors de l'insertion dans la base de données");
            }
        }
    } catch (Exception $e) {
        $errors['general'] = "Une erreur est survenue lors de l'ajout du suivi";
        error_log("Erreur ajout suivi: " . $e->getMessage());
    }
}
?>