<?php
// Configuration de la base de données (devrait être dans un fichier de configuration séparé)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_courrier');
define('DB_CHARSET', 'utf8mb4');

// Connexion à la base de données MySQL avec gestion d'erreur améliorée
function connexionBD(): mysqli {
    $connexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($connexion === false) {
        error_log("Erreur de connexion MySQL: " . mysqli_connect_error());
        throw new RuntimeException("Impossible de se connecter à la base de données");
    }
    
    if (!mysqli_set_charset($connexion, DB_CHARSET)) {
        error_log("Erreur lors du chargement du jeu de caractères ".DB_CHARSET." : ".mysqli_error($connexion));
        throw new RuntimeException("Erreur de configuration de la base de données");
    }
    
    return $connexion;
}

// Variable globale pour la connexion
$connexion = null;

try {
    $connexion = connexionBD();
} catch (RuntimeException $e) {
    error_log($e->getMessage());
    die("Erreur critique: Impossible de se connecter à la base de données");
}

/**
 * Récupère toutes les factures
 * @return array Tableau des factures
 */
function getFactures(): array {
    global $connexion;
    $sql = "SELECT * FROM facture ORDER BY id_facture DESC";
    $result = $connexion->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Récupère une facture par son ID
 * @param int $id ID de la facture
 * @return array|null Données de la facture ou null si non trouvée
 */
function getFactureById(int $id): ?array {
    global $connexion;
    $stmt = $connexion->prepare("SELECT * FROM facture WHERE id_facture = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: null;
}

/**
 * Supprime une facture et son fichier PDF associé
 * @param int $id ID de la facture à supprimer
 * @return bool True si suppression réussie
 */
function supprimerFacture(int $id): bool {
    global $connexion;
    $facture = getFactureById($id);
    if (!$facture) return false;

    // Suppression du fichier PDF
    if (!empty($facture['facture_pdf'])) {
        $pdfPath = realpath(__DIR__ . '/../uploads/' . $facture['facture_pdf']);
        if ($pdfPath && file_exists($pdfPath) && is_writable($pdfPath)) {
            unlink($pdfPath);
        }
    }

    $stmt = $connexion->prepare("DELETE FROM facture WHERE id_facture = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}


/**
 * Ajoute une nouvelle facture dans la base de données
 * @param array $data Les données de la facture
 * @return int|false L'ID de la nouvelle facture ou false en cas d'échec
 */
function ajouterFacture(array $data) {
    global $connexion;
    
    // Vérification des champs obligatoires
    $required = ['date_arrive', 'numero_courrier', 'expediteur', 'numero_facture', 'montant_ttc', 'type_facture'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            error_log("Champ manquant: $field");
            return false;
        }
    }

    // Préparation de la requête
    $query = "INSERT INTO facture 
              (date_arrive, numero_courrier, expediteur, numero_facture, decade, montant_ttc, type_facture, facture_pdf) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $connexion->prepare($query);
    if (!$stmt) {
        error_log("Erreur de préparation: " . $connexion->error);
        return false;
    }

    // Liaison des paramètres
    $stmt->bind_param(
        "sssssdss",
        $data['date_arrive'],
        $data['numero_courrier'],
        $data['expediteur'],
        $data['numero_facture'],
        $data['decade'],
        $data['montant_ttc'],
        $data['type_facture'],
        $data['facture_pdf'] 
    );

    // Exécution
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    } else {
        error_log("Erreur d'exécution: " . $stmt->error);
        $stmt->close();
        return false;
    }

    // Vérifier que le montant est numérique et positif
    if (!is_numeric($data['montant_ttc']) || $data['montant_ttc'] <= 0) {
        error_log("Montant invalide : " . $data['montant_ttc']);
        return false;
    }

    // Forcer 2 décimales (au cas où)
    $data['montant_ttc'] = number_format((float)$data['montant_ttc'], 2, '.', '');
}

/**
 * Met à jour une facture existante
 * @param int $id ID de la facture
 * @param array $data Nouvelles données
 * @return bool True si mise à jour réussie
 */
function modifierFacture(int $id, array $data): bool {
    global $connexion;
    
    $query = "UPDATE facture SET 
        date_arrive = ?, 
        numero_courrier = ?, 
        expediteur = ?, 
        numero_facture = ?, 
        decade = ?, 
        montant_ttc = ?, 
        type_facture = ?, 
        facture_pdf = ?
        WHERE id_facture = ?";
    
    $stmt = $connexion->prepare($query);
    if (!$stmt) {
        error_log("Erreur de préparation: " . $connexion->error);
        return false;
    }
    
    $success = $stmt->bind_param(
        "sssssdssi", 
        $data['date_arrive'],
        $data['numero_courrier'],
        $data['expediteur'],
        $data['numero_facture'],
        $data['decade'],
        $data['montant_ttc'],
        $data['type_facture'],
        $data['facture_pdf'],
        $id
    ) && $stmt->execute();
    
    if (!$success) {
        error_log("Erreur d'exécution: " . $stmt->error);
    }
    
    $stmt->close();
    return $success;
}

/**
 * Upload un fichier PDF
 * @param array $file Fichier uploadé ($_FILES)
 * @return array Résultat avec 'success' ou 'error'
 */
function uploaderPDF(array $file): array {
    $uploadDir = __DIR__ . '/../uploads/factures/';
    
    // Vérification des erreurs
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Erreur lors du téléchargement du fichier'];
    }
    
    // Vérification du type MIME réel
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if ($mime !== 'application/pdf') {
        return ['error' => 'Seuls les fichiers PDF sont autorisés'];
    }
    
    // Vérification de la taille
    if ($file['size'] > 5_000_000) { // 5MB
        return ['error' => 'Le fichier est trop volumineux (max 5MB)'];
    }
    
    // Création du répertoire si nécessaire
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return ['error' => 'Impossible de créer le répertoire de destination'];
        }
    }
    
    // Sécurisation du nom de fichier
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('facture_', true) . '.' . $extension;
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => $fileName];
    }
    
    return ['error' => 'Erreur lors du déplacement du fichier'];
}

/**
 * Valide les données d'une facture
 * @param array $data Données à valider
 * @return array Tableau d'erreurs (vide si valide)
 */
function validerFacture(array $data): array {
    $errors = [];
    $required = ['date_arrive', 'numero_courrier', 'expediteur', 'numero_facture', 'montant_ttc', 'type_facture'];
    
    foreach ($required as $field) {
        if (empty(trim($data[$field] ?? ''))) {
            $errors[] = "Le champ " . str_replace('_', ' ', $field) . " est requis";
        }
    }
    
    if (!empty($data['date_arrive']) && !DateTime::createFromFormat('Y-m-d', $data['date_arrive'])) {
        $errors[] = "Format de date invalide (doit être YYYY-MM-DD)";
    }
    
    if (!empty($data['montant_ttc']) && !is_numeric($data['montant_ttc'])) {
        $errors[] = "Montant TTC doit être un nombre valide";
    }
    
    return $errors;
}

/**
 * Compte le nombre total de factures
 * @return int Nombre de factures
 */
function getCountFacture(): int {
    global $connexion;
    $result = $connexion->query("SELECT COUNT(*) as total FROM facture");
    return $result ? (int)$result->fetch_assoc()['total'] : 0;
}

/**
 * Récupère tous les types de factures distincts
 * @return array Tableau des types de factures
 */
function recuperer_statuts(): array {
    global $connexion;
    $result = $connexion->query("SELECT DISTINCT type_facture FROM facture ORDER BY type_facture ASC");
    return $result ? array_column($result->fetch_all(MYSQLI_ASSOC), 'type_facture') : [];
}

// Fonction supplémentaire pour vérifier l'unicité
function getFactureByNumber($numero) {
    global $connexion;
    $stmt = $connexion->prepare("SELECT id_facture FROM facture WHERE numero_facture = ? LIMIT 1");
    $stmt->bind_param("s", $numero);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Vérifie si un numéro de courrier existe déjà dans la base
 * @param string $numero_courrier
 * @return bool
 */
function numero_courrier_existe(string $numero_courrier): bool {
    global $connexion;
    $stmt = $connexion->prepare("SELECT id_facture FROM facture WHERE numero_courrier = ? LIMIT 1");
    $stmt->bind_param("s", $numero_courrier);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result && $result->num_rows > 0;
}

/**
 * Vérifie si un numéro de facture existe déjà dans la base
 * @param string $numero_facture
 * @return bool
 */
function numero_facture_existe(string $numero_facture): bool {
    global $connexion;
    $stmt = $connexion->prepare("SELECT id_facture FROM facture WHERE numero_facture = ? LIMIT 1");
    $stmt->bind_param("s", $numero_facture);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result && $result->num_rows > 0;
}