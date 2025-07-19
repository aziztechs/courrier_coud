<?php 
// Récupérer l'ID du courrier à modifier
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$courrier = getCourrierById($connexion, $id);

if (!$courrier) {
    $_SESSION['message'] = "Le courrier demandé n'existe pas.";
    $_SESSION['message_type'] = "danger";
    header("Location: liste_courriers.php");
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'Numero_Courrier' => $_POST['numero_courrier'],
        'date' => $_POST['date'],
        'Objet' => $_POST['objet'],
        'pdf' => $courrier['pdf'], // Conserve le fichier existant par défaut
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

        // Supprimer l'ancien fichier s'il existe
        if (!empty($courrier['pdf']) && file_exists($courrier['pdf'])) {
            unlink($courrier['pdf']);
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

    // Modification
    if (empty($_SESSION['message'])) {
        if (modifierCourrier($connexion, $id, $data)) {
            $_SESSION['message'] = "Le courrier a été modifié avec succès.";
            $_SESSION['message_type'] = "success";
            $_SESSION['courrier_modifie'] = $data['Numero_Courrier'];
            
            // Redirection vers la même page pour afficher le modal
            header("Location: modifier_courrier.php?id=$id&success=1");
            exit();
        } else {
            $_SESSION['message'] = "Erreur lors de la modification du courrier.";
            $_SESSION['message_type'] = "danger";
        }
    }
}