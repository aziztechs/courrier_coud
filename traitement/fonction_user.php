<?php
global $connexion;

/**
 * Récupère les utilisateurs de manière paginée, triés par ID décroissant
 * 
 * @param int $limit Nombre d'utilisateurs par page
 * @param int $offset Position de départ
 * @return array|false Tableau d'utilisateurs ou false en cas d'erreur
 */
function getAllUsersPaginated($limit, $offset) {
    global $connexion;
    
    // Validation des paramètres
    $limit = max(1, (int)$limit);  // Au moins 1
    $offset = max(0, (int)$offset); // Au moins 0
    
    // Requête préparée pour plus de sécurité
    $sql = "SELECT * FROM user ORDER BY id_user DESC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($connexion, $sql);
    
    if (!$stmt) {
        error_log("Erreur de préparation de la requête: " . mysqli_error($connexion));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Erreur d'exécution de la requête: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    mysqli_stmt_close($stmt);
    
    return $users;
}

    /**
     * Récupère le nombre total d'utilisateurs
     * @return int
     */
    function getAllUsersCount() {
        global $connexion;
        $sql = "SELECT COUNT(*) as total FROM user";
        $result = mysqli_query($connexion, $sql);
        $row = mysqli_fetch_assoc($result);
        return (int)$row['total'];
    }

    /**
     * Récupère le nombre total d'utilisateurs correspondant à une recherche
     * @param string $search Terme de recherche
     * @return int
     */
    function getSearchUsersCount($search) {
        global $connexion;
        $search = mysqli_real_escape_string($connexion, $search);
        $sql = "SELECT COUNT(*) as total FROM user 
                WHERE Nom LIKE '%$search%' 
                OR Prenom LIKE '%$search%'
                OR Username LIKE '%$search%'
                OR email LIKE '%$search%'";
        $result = mysqli_query($connexion, $sql);
        $row = mysqli_fetch_assoc($result);
        return (int)$row['total'];
    }

    /**
     * Recherche des utilisateurs avec pagination
     */
    function searchUsers($search, $limit, $offset) {
        global $connexion;
        
        $search = mysqli_real_escape_string($connexion, $search);
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $sql = "SELECT * FROM user 
                WHERE Nom LIKE '%$search%' 
                OR Prenom LIKE '%$search%'
                OR Username LIKE '%$search%'
                OR email LIKE '%$search%'
                ORDER BY id_user DESC 
                LIMIT $limit OFFSET $offset";
        
        $result = mysqli_query($connexion, $sql);
        $users = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
        
        return $users;
    }

    // Fonction pour récupérer un utilisateur par son ID
    function getUserById($id) {
        global $connexion;
        $id = intval($id);
        $sql = "SELECT * FROM user WHERE id_user = $id";
        return mysqli_query($connexion, $sql);
    }

    /**
     * Ajoute un nouvel utilisateur dans la base de données
     */
    function ajouterUtilisateur($nom, $prenom, $username, $password, $email, $tel, $fonction, $matricule, $actif) {
        global $connexion;
        
        // Validation des entrées
        if (empty($nom) || empty($prenom) || empty($username) || empty($password) || empty($email)) {
            return false;
        }
        
        // Nettoyage des données
        $nom = mysqli_real_escape_string($connexion, trim($nom));
        $prenom = mysqli_real_escape_string($connexion, trim($prenom));
        $username = mysqli_real_escape_string($connexion, trim($username));
        $email = mysqli_real_escape_string($connexion, trim($email));
        $tel = mysqli_real_escape_string($connexion, trim($tel));
        $fonction = mysqli_real_escape_string($connexion, trim($fonction));
        $matricule = mysqli_real_escape_string($connexion, trim($matricule));
        $actif = intval($actif) ? 1 : 0;
        
        // Vérification de l'unicité du username/email
        $check_sql = "SELECT id_user FROM user WHERE Username = ? OR email = ? LIMIT 1";
        $stmt = mysqli_prepare($connexion, $check_sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_close($stmt);
            return false;
        }
        mysqli_stmt_close($stmt);
        
        // Hashage sécurisé du mot de passe
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        // Utilisation des requêtes préparées pour plus de sécurité
        $sql = "INSERT INTO user (Nom, Prenom, Username, Password, email, Tel, Fonction, Matricule, Actif) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($connexion, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssi", $nom, $prenom, $username, $passwordHash, $email, $tel, $fonction, $matricule, $actif);
        $result = mysqli_stmt_execute($stmt);
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        return $result && $affected_rows > 0;
    }

    // Exemple d'utilisation :
    /*
    if (ajouterUtilisateur($nom, $prenom, $username, $password, $email, $tel, $fonction, $matricule, $actif)) {
        header("Location: users.php");
        exit();
    } else {
        header("Location: error_page.php?error=user_add_failed");
        exit();
    }
    */
   
 

    function modifierUtilisateur($id, $nom, $prenom, $username, $email, $tel, $fonction, $matricule, $actif, $password = null) {
        global $connexion;
        
        try {
            // Construction dynamique de la requête
            $query = "UPDATE user SET 
                Nom = ?, Prenom = ?, Username = ?, 
                email = ?, Tel = ?, Fonction = ?, 
                Matricule = ?, Actif = ?";
            
            $params = [$nom, $prenom, $username, $email, $tel, $fonction, $matricule, $actif];
            $types = "sssssssi";
            
            if ($password !== null) {
                $query .= ", Password = ?";
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $params[] = $passwordHash;
                $types .= "s";
            }
            
            $query .= " WHERE id_user = ?";
            $params[] = $id;
            $types .= "i";
            
            $stmt = $connexion->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                // Gestion des erreurs d'unicité avec messages en français
                if ($stmt->errno === 1062) {
                    $errorMessage = $stmt->error;
                    
                    if (strpos($errorMessage, 'Username') !== false) {
                        throw new RuntimeException("Ce nom d'utilisateur est déjà utilisé");
                    } 
                    elseif (strpos($errorMessage, 'email') !== false) {
                        throw new RuntimeException("Cette adresse email est déjà utilisée");
                    } 
                    elseif (strpos($errorMessage, 'Matricule') !== false) {
                        throw new RuntimeException("Ce matricule est déjà attribué à un autre utilisateur");
                    }
                    else {
                        throw new RuntimeException("Une valeur existe déjà dans la base de données");
                    }
                }
                throw new RuntimeException("Erreur lors de la mise à jour de l'utilisateur");
            }
            
            return $stmt->affected_rows > 0;
            
        } catch (mysqli_sql_exception $e) {
            // Gestion des erreurs SQL avec messages en français
            if ($e->getCode() === 1062) {
                $errorMessage = $e->getMessage();
                
                if (strpos($errorMessage, 'Username') !== false) {
                    throw new RuntimeException("Ce nom d'utilisateur est déjà utilisé");
                } 
                elseif (strpos($errorMessage, 'email') !== false) {
                    throw new RuntimeException("Cette adresse email est déjà utilisée");
                } 
                elseif (strpos($errorMessage, 'Matricule') !== false) {
                    throw new RuntimeException("Ce matricule est déjà attribué à un autre utilisateur");
                }
                else {
                    throw new RuntimeException("Une valeur existe déjà dans la base de données");
                }
            }
            
            error_log("Erreur SQL dans modifierUtilisateur: " . $e->getMessage());
            throw new RuntimeException("Erreur technique lors de la mise à jour");
        }
    }

    /**
     * Supprime un utilisateur de manière sécurisée
     * 
     * @param int $id ID de l'utilisateur
     * @return bool True si succès
     * @throws RuntimeException Si échec
     */
    function supprimerUtilisateur($id) {
        global $connexion;
        
        $stmt = $connexion->prepare("DELETE FROM user WHERE id_user = ?");
        if (!$stmt) {
            throw new RuntimeException("Erreur système");
        }
        
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new RuntimeException("Échec de la requête");
        }
        
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        if ($affected === 0) {
            throw new RuntimeException("Utilisateur introuvable");
        }
        
        return true;
    }


    // Fonction pour mettre à jour le mot de passe
    function updatePassword($id, $newPassword) {
        global $connexion;

        $id = intval($id);
        $newPasswordHash = sha1($newPassword);

        $sql = "UPDATE user SET Password='$newPasswordHash' WHERE id_user=$id";
        return mysqli_query($connexion, $sql);
    }

    // Ajoutez cette fonction pour gérer l'activation/désactivation
    function toggleUserStatus($userId, $currentStatus) {
        global $connexion;
        
        try {
            $newStatus = $currentStatus ? 0 : 1;
            $stmt = $connexion->prepare("UPDATE user SET Actif = ? WHERE id_user = ?");
            $stmt->bind_param("ii", $newStatus, $userId);
            
            if ($stmt->execute()) {
                return $newStatus;
            }
            return false;
        } catch (mysqli_sql_exception $e) {
            error_log("Erreur lors du changement de statut utilisateur: " . $e->getMessage());
            return false;
        }
    }





