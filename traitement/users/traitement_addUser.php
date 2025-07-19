<?php
// Génération du token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmed'])) {
    // Vérification CSRF renforcée
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Erreur de sécurité. Veuillez réessayer.";
        header("Location: addUser.php");
        exit();
    }

    // Nettoyage et validation des données
    $data = [
        'nom' => trim($_POST['Nom'] ?? ''),
        'prenom' => trim($_POST['Prenom'] ?? ''),
        'username' => trim($_POST['Username'] ?? ''),
        'password' => $_POST['Password'] ?? '',
        'email' => trim($_POST['email'] ?? ''),
        'tel' => preg_replace('/[^0-9]/', '', $_POST['Tel'] ?? ''),
        'fonction' => trim($_POST['Fonction'] ?? ''),
        'matricule' => trim($_POST['Matricule'] ?? ''),
        'actif' => isset($_POST['Actif']) ? 1 : 0
    ];

    // Échappement pour la sécurité
    foreach ($data as $key => $value) {
        if ($key !== 'password') {
            $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
    }

    // Validation
    $errors = [];
    if (empty($data['nom'])) $errors[] = "Le nom est obligatoire";
    if (empty($data['prenom'])) $errors[] = "Le prénom est obligatoire";
    if (empty($data['username'])) $errors[] = "Le nom d'utilisateur est obligatoire";
    if (strlen($data['password']) < 8) $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "L'email n'est pas valide";
    if (empty($data['fonction'])) $errors[] = "La fonction est obligatoire";
    if (!empty($data['tel']) && strlen($data['tel']) < 8) $errors[] = "Le numéro de téléphone doit contenir au moins 8 chiffres";

    if (empty($errors)) {
        try {
            // Vérification des doublons avec requête préparée
            $checkQuery = "SELECT 
                (SELECT COUNT(*) FROM user WHERE Username = ?) as username_count,
                (SELECT COUNT(*) FROM user WHERE email = ?) as email_count,
                (SELECT COUNT(*) FROM user WHERE Tel = ?) as tel_count,
                (SELECT COUNT(*) FROM user WHERE Matricule = ? AND Matricule != '') as matricule_count";
            
            $checkStmt = $connexion->prepare($checkQuery);
            $checkStmt->bind_param("ssss", 
                $data['username'],
                $data['email'],
                $data['tel'],
                $data['matricule']
            );
            $checkStmt->execute();
            $result = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();

            // Messages d'erreur spécifiques
            if ($result['username_count'] > 0) $errors[] = "Ce nom d'utilisateur est déjà utilisé";
            if ($result['email_count'] > 0) $errors[] = "Cet email est déjà associé à un compte";
            if ($result['tel_count'] > 0) $errors[] = "Ce numéro de téléphone est déjà enregistré";
            if (!empty($data['matricule']) && $result['matricule_count'] > 0) $errors[] = "Ce matricule est déjà attribué";

            if (empty($errors)) {
                if (ajouterUtilisateur(
                    $data['nom'],
                    $data['prenom'],
                    $data['username'],
                    $data['password'],
                    $data['email'],
                    $data['tel'],
                    $data['fonction'],
                    $data['matricule'],
                    $data['actif']
                )) {
                    $_SESSION['swal'] = [
                        'title' => 'Succès',
                        'text' => "Utilisateur ajouté avec succès",
                        'icon' => 'success',
                        'timer' => 5000,
                        'redirect' => 'users.php'
                    ];
                    header("Location: addUser.php");
                    exit();
                } else {
                    throw new Exception("Échec de l'ajout de l'utilisateur");
                }
            }
        } catch (Exception $e) {
            error_log("Erreur dans addUser.php: " . $e->getMessage());
            $errors[] = "Une erreur technique est survenue";
        }
    }

    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $data;
    header("Location: addUser.php");
    exit();
}

// Nettoyage des données de session après affichage
$formData = $_SESSION['form_data'] ?? [];
$formErrors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_data'], $_SESSION['form_errors']);

?>