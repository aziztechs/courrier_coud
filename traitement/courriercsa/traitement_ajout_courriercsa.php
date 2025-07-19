<?php
// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Génération automatique du numéro de courrier
    $annee = date('Y');
    //$dernier_numero = getDernierNumeroCourrier($connexion, $annee);
    //$nouveau_numero = 'COUR-' . $annee . '-' . str_pad($dernier_numero + 1, 3, '0', STR_PAD_LEFT);

    $data = [
        'Numero_Courrier' => $_POST['numero_courrier'] ?? 'COUR-' . $annee . '-NNN', // Placeholder, will be updated later
        'date' => $_POST['date'],
        'Objet' => $_POST['objet'],
        'pdf' => '',
        'Nature' => $_POST['nature'],
        'Type' => $_POST['type'],
        'Expediteur' => $_POST['expediteur']
    ];

    // Gestion du fichier PDF
    if (!empty($_FILES['pdf']['name'])) {
        $uploadDir = '../../uploads/courriers/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid() . '_' . basename($_FILES['pdf']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $targetPath)) {
            $data['pdf'] = $targetPath;
        } else {
            $_SESSION['message'] = "Erreur lors du téléchargement du fichier PDF.";
            $_SESSION['message_type'] = "danger";
        }
    }

    // Enregistrement
    if (empty($_SESSION['message'])) {
        $id = enregistrerCourrier($connexion, $data);
        if ($id) {
            $_SESSION['message'] = "Le courrier a été enregistré avec succès. Numéro: " . $nouveau_numero;
            $_SESSION['message_type'] = "success";
            $_SESSION['nouveau_courrier'] = $nouveau_numero;
            
            // Redirection vers la même page pour afficher le modal
            header("Location: ajouter_courriercsa.php?success=1");
            exit();
        } else {
            $_SESSION['message'] = "Erreur lors de l'enregistrement du courrier.";
            $_SESSION['message_type'] = "danger";
        }
    }
}
