<?php
// Vérification CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
    $_SESSION['error'] = "Erreur de sécurité CSRF - Token invalide";
    header('Location: editUser.php?id='.(int)$_GET['id']);
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userResult = getUserById($id);

if ($userResult->num_rows === 0) {
    $_SESSION['error'] = "Erreur : Utilisateur introuvable avec cet ID";
    header('Location: users.php');
    exit();
}

$user = $userResult->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmed'])) {
    // Récupération et validation des données
    $data = [
        'nom' => trim($_POST['Nom']),
        'prenom' => trim($_POST['Prenom']),
        'username' => trim($_POST['Username']),
        'email' => filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL),
        'tel' => preg_replace('/[^0-9]/', '', $_POST['Tel']),
        'fonction' => trim($_POST['Fonction']),
        'matricule' => trim($_POST['Matricule']),
        'actif' => isset($_POST['Actif']) ? 1 : 0,
        'password' => !empty($_POST['password']) ? $_POST['password'] : null
    ];

    // Validation des champs obligatoires
    $errors = [];
    if (empty($data['nom'])) $errors[] = "Le champ Nom est obligatoire";
    if (empty($data['prenom'])) $errors[] = "Le champ Prénom est obligatoire";
    if (empty($data['username'])) $errors[] = "Le champ Nom d'utilisateur est obligatoire";
    if (!$data['email']) $errors[] = "Format d'email invalide";
    if (!empty($data['tel']) && strlen($data['tel']) < 8) $errors[] = "Le numéro de téléphone doit contenir au moins 8 chiffres";

    if (empty($errors)) {
        try {
            // Vérification des doublons
            $checkStmt = $connexion->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM user WHERE Username = ? AND id_user != ?) as username_count,
                    (SELECT COUNT(*) FROM user WHERE email = ? AND id_user != ?) as email_count,
                    (SELECT COUNT(*) FROM user WHERE Matricule = ? AND id_user != ?) as matricule_count
            ");
            
            $checkStmt->bind_param("sisisi", 
                $data['username'], $id,
                $data['email'], $id,
                $data['matricule'], $id
            );
            
            $checkStmt->execute();
            $result = $checkStmt->get_result()->fetch_assoc();
            
            if ($result['username_count'] > 0) $errors[] = "Ce nom d'utilisateur est déjà utilisé";
            if ($result['email_count'] > 0) $errors[] = "Cette adresse email est déjà utilisée";
            if (!empty($data['matricule']) && $result['matricule_count'] > 0) $errors[] = "Ce matricule est déjà attribué";

            if (empty($errors)) {
                if (modifierUtilisateur(
                    $id,
                    $data['nom'],
                    $data['prenom'],
                    $data['username'],
                    $data['email'],
                    $data['tel'],
                    $data['fonction'],
                    $data['matricule'],
                    $data['actif'],
                    $data['password']
                )) {
                    $_SESSION['success_message'] = "L'utilisateur a été mis à jour avec succès";
                    header('Location: editUser.php?id='.$id);
                    exit();
                } else {
                    $errors[] = "La mise à jour a échoué";
                }
            }
        } catch (Exception $e) {
            $errors[] = "Erreur technique : " . $e->getMessage();
            error_log("Erreur dans editUser.php: " . $e->getMessage());
        }
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        header('Location: editUser.php?id='.$id);
        exit();
    }
}