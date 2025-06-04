<?php
global $connexion;

// Fonction pour ajouter un utilisateur
function ajouterUtilisateur($nom, $prenom, $username, $password, $email, $tel, $fonction, $matricule, $actif) {
    global $connexion;
    
    $nom = mysqli_real_escape_string($connexion, $nom);
    $prenom = mysqli_real_escape_string($connexion, $prenom);
    $username = mysqli_real_escape_string($connexion, $username);
    $email = mysqli_real_escape_string($connexion, $email);
    $tel = mysqli_real_escape_string($connexion, $tel);
    $fonction = mysqli_real_escape_string($connexion, $fonction);
    $matricule = mysqli_real_escape_string($connexion, $matricule);
    $actif = intval($actif);

    $passwordHash = sha1($password);

    $sql = "INSERT INTO user (Nom, Prenom, Username, Password, email, Tel, Fonction, Matricule, Actif) 
            VALUES ('$nom', '$prenom', '$username', '$passwordHash', '$email', '$tel', '$fonction', '$matricule', $actif)";
    
    $result = mysqli_query($connexion, $sql);

    if ($result && mysqli_affected_rows($connexion) > 0) {
        header("Location: users.php");
        exit();
    } else {
        header("Location: error_page.php");
        exit();
    }
}

// Fonction pour récupérer tous les utilisateurs
function getAllUsersPaginated( $limit, $offset) {
    global $connexion;
    $sql = "SELECT * FROM user LIMIT $limit OFFSET $offset";
    return mysqli_query($connexion, $sql);
}

function getAllUsersCount() {
    global $connexion;
    $sql = "SELECT COUNT(*) as total FROM user";
    $result = mysqli_query($connexion, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function searchUsers($search, $limit, $offset) {
    global $connexion;
    $search = mysqli_real_escape_string($connexion, $search);
    $sql = "SELECT * FROM user WHERE Nom LIKE '%$search%' OR Prenom LIKE '%$search%' OR Username LIKE '%$search%' LIMIT $limit OFFSET $offset";
    return mysqli_query($connexion, $sql);
}

// Fonction pour récupérer un utilisateur par son ID
function getUserById($id) {
    global $connexion;
    $id = intval($id);
    $sql = "SELECT * FROM user WHERE id_user = $id";
    return mysqli_query($connexion, $sql);
}

// Fonction pour supprimer un utilisateur
function supprimerUtilisateur($id) {
    global $connexion;
    $id = intval($id);
    $sql = "DELETE FROM user WHERE id_user = $id";
    return mysqli_query($connexion, $sql);
}

// Fonction pour modifier un utilisateur
function modifierUtilisateur($id, $nom, $prenom, $username, $email, $tel, $fonction, $matricule, $actif, $password = null) {
    global $connexion;

    $id = intval($id);
    $nom = mysqli_real_escape_string($connexion, $nom);
    $prenom = mysqli_real_escape_string($connexion, $prenom);
    $username = mysqli_real_escape_string($connexion, $username);
    $email = mysqli_real_escape_string($connexion, $email);
    $tel = mysqli_real_escape_string($connexion, $tel);
    $fonction = mysqli_real_escape_string($connexion, $fonction);
    $matricule = mysqli_real_escape_string($connexion, $matricule);
    $actif = intval($actif);

    $sql = "UPDATE user SET 
        Nom = '$nom',
        Prenom = '$prenom',
        Username = '$username',
        email = '$email',
        Tel = '$tel',
        Fonction = '$fonction',
        Matricule = '$matricule',
        Actif = $actif";

    if ($password !== null) {
        $passwordHash = sha1($password);
        $sql .= ", Password = '$passwordHash'";
    }

    $sql .= " WHERE id_user = $id";

    return mysqli_query($connexion, $sql);
}

// Fonction pour mettre à jour le mot de passe
function updatePassword($id, $newPassword) {
    global $connexion;

    $id = intval($id);
    $newPasswordHash = sha1($newPassword);

    $sql = "UPDATE user SET Password='$newPasswordHash' WHERE id_user=$id";
    return mysqli_query($connexion, $sql);
}