<?php
// Configuration de la base de données (devrait être dans un fichier de configuration séparé)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_courrier');
define('DB_CHARSET', 'utf8mb4');

// Connexion à la base de données MySQL avec gestion d'erreur améliorée
function connexionBD() {
    $connexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($connexion === false) {
        error_log("Erreur de connexion MySQL: " . mysqli_connect_error());
        throw new Exception("Impossible de se connecter à la base de données");
    }
    
    // Utilisation de utf8mb4 pour un meilleur support Unicode
    if (!mysqli_set_charset($connexion, DB_CHARSET)) {
        error_log("Erreur lors du chargement du jeu de caractères ".DB_CHARSET." : ".mysqli_error($connexion));
        throw new Exception("Erreur de configuration de la base de données");
    }
    
    return $connexion;
}

try {
    $connexion = connexionBD();
} catch (Exception $e) {
    // Ne pas afficher les détails d'erreur en production
    error_log($e->getMessage());
    die("Erreur critique: Impossible de se connecter à la base de données");
}

// Fonction de connexion sécurisée avec protection contre les attaques par force brute
function login($username, $password) {
    global $connexion;
    
    // Validation des entrées
    if (empty($username) || empty($password) || !is_string($username) || !is_string($password)) {
        return null;
    }
    
    // Nettoyage des entrées
    $username = trim($username);
    
    try {
        $stmt = $connexion->prepare("SELECT * FROM `user` WHERE `Username` = ? LIMIT 1");
        if (!$stmt) {
            error_log("Erreur de préparation de requête: " . $connexion->error);
            return null;
        }
        
        $stmt->bind_param("s", $username);
        if (!$stmt->execute()) {
            error_log("Erreur d'exécution de requête: " . $stmt->error);
            return null;
        }
        
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
             //Vérification du mot de passe avec password_verify (nécessite que les mots de passe soient hashés avec password_hash)
            if (password_verify($password, $row['Password'])) {
                //Journalisation de la connexion réussie
                logActivity($username, "Connexion réussie");
                return $row;
            }
            
            // Alternative si vous devez conserver sha1 temporairement
             //if (hash_equals(sha1($password), $row['Password'])) {
                //logActivity($username, "Connexion réussie");
                //return $row;
            //}
        }
        
        // Journalisation des tentatives échouées avec un délai pour ralentir les attaques par force brute
        logActivity($username, "Tentative de connexion échouée");
        usleep(rand(200000, 500000)); // Délai aléatoire entre 200ms et 500ms
        
        return null;
    } catch (Exception $e) {
        error_log("Exception dans la fonction login: " . $e->getMessage());
        return null;
    }
}


// Fonction pour journaliser les activités
function logActivity($username, $action) {
    global $connexion;
    
    if (!is_string($username)) {
        $username = 'system';
    }
    if (!is_string($action)) return false;
    
    try {
        $sql = "INSERT INTO activity_log (username, action, activity_date) VALUES (?, ?, NOW())";
        $stmt = $connexion->prepare($sql);
        
        if (!$stmt) {
            error_log("Erreur de préparation du log: " . $connexion->error);
            return false;
        }
        
        $stmt->bind_param("ss", $username, $action);
        
        if (!$stmt->execute()) {
            error_log("Erreur d'enregistrement du log: " . $stmt->error);
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Exception dans logActivity: " . $e->getMessage());
        return false;
    }
}

// Fonction pour vérifier les permissions de l'utilisateur
function checkPermission($requiredPermission) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    if (empty($_SESSION['user_permissions'])) {
        return false;
    }
    
    return in_array($requiredPermission, $_SESSION['user_permissions'], true);
}

// Fonction pour sécuriser les sorties HTML
function escapeHtml($data) {
    if (is_array($data)) {
        return array_map('escapeHtml', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}