<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    
</body>
</html>

<?php

// Connexion à la base de données MySQL
function connexionBD()
{
    $connexion = mysqli_connect("localhost", "root", "", "db_courrier");
    if ($connexion === false) {
        die("Erreur : Impossible de se connecter. " . mysqli_connect_error());
    }
    return $connexion;
}
$connexion = connexionBD();

// Fonction de connexion sécurisée
function login($username, $password)
{
    global $connexion;
    $stmt = $connexion->prepare("SELECT * FROM `user` WHERE `Username` = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (sha1($password) === $row['Password']) {
            return $row;
        }
    }
    return null;
}

// Fonctions améliorées pour les filtres
function getFilteredCourriers($filters = [], $page = 1, $itemsPerPage = 10)
{
    global $connexion;
    $offset = ($page - 1) * $itemsPerPage;

    // Requête de base
    $sql = "SELECT DISTINCT c.id_courrier, c.date, c.Numero_courrier, c.Type, c.Objet, c.pdf, c.Nature, c.Type, c.Expediteur
            FROM courrier c
            LEFT JOIN imputation i ON c.id_courrier = i.id_courrier
            WHERE 1=1";
    
    $params = [];
    $types = '';

    // Application des filtres
    if (!empty($filters['search'])) {
        $sql .= " AND (c.Numero_Courrier LIKE ? OR c.Objet LIKE ? OR c.Nature LIKE ? OR c.Type LIKE ? OR c.Expediteur LIKE ?)";
        $searchTerm = "%{$filters['search']}%";
        $params = array_merge($params, array_fill(0, 5, $searchTerm));
        $types .= str_repeat('s', 5);
    }

    if (!empty($filters['nature'])) {
        $sql .= " AND c.Nature = ?";
        $params[] = $filters['nature'];
        $types .= 's';
    }

    if (!empty($filters['type'])) {
        $sql .= " AND c.Type = ?";
        $params[] = $filters['type'];
        $types .= 's';
    }

    if (!empty($filters['date_debut'])) {
        $sql .= " AND c.date >= ?";
        $params[] = $filters['date_debut'];
        $types .= 's';
    }

    if (!empty($filters['date_fin'])) {
        $sql .= " AND c.date <= ?";
        $params[] = $filters['date_fin'];
        $types .= 's';
    }

    // Tri et pagination
    $sql .= " ORDER BY c.date DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $itemsPerPage;
    $types .= 'ii';

    // Préparation et exécution
    $stmt = mysqli_prepare($connexion, $sql);
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $courriers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $courriers[] = $row;
    }

    return $courriers;
}

// Fonction pour compter les résultats filtrés
function countFilteredCourriers($filters = [])
{
    global $connexion;
    
    $sql = "SELECT COUNT(DISTINCT c.id_courrier) as total
            FROM courrier c
            LEFT JOIN imputation i ON c.id_courrier = i.id_courrier
            WHERE 1=1";
    
    $params = [];
    $types = '';

    // Mêmes filtres que dans getFilteredCourriers
    if (!empty($filters['search'])) {
        $sql .= " AND (c.Numero_Courrier LIKE ? OR c.Objet LIKE ? OR c.Nature LIKE ? OR c.Type LIKE ? OR c.Expediteur LIKE ?)";
        $searchTerm = "%{$filters['search']}%";
        $params = array_merge($params, array_fill(0, 5, $searchTerm));
        $types .= str_repeat('s', 5);
    }

    if (!empty($filters['nature'])) {
        $sql .= " AND c.Nature = ?";
        $params[] = $filters['nature'];
        $types .= 's';
    }

    if (!empty($filters['type'])) {
        $sql .= " AND c.Type = ?";
        $params[] = $filters['type'];
        $types .= 's';
    }

    if (!empty($filters['date_debut'])) {
        $sql .= " AND c.date >= ?";
        $params[] = $filters['date_debut'];
        $types .= 's';
    }

    if (!empty($filters['date_fin'])) {
        $sql .= " AND c.date <= ?";
        $params[] = $filters['date_fin'];
        $types .= 's';
    }

    $stmt = mysqli_prepare($connexion, $sql);
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return $row['total'];
}

// Fonction pour récupérer les valeurs distinctes pour les filtres
function getDistinctValues($column)
{
    global $connexion;
    $allowedColumns = ['Nature', 'Type', 'Expediteur']; // Colonnes autorisées pour éviter les injections SQL
    
    if (!in_array($column, $allowedColumns)) {
        return [];
    }

    $query = "SELECT DISTINCT $column FROM courrier WHERE $column IS NOT NULL ORDER BY $column";
    $result = $connexion->query($query);
    
    $values = [];
    while ($row = $result->fetch_assoc()) {
        $values[] = $row[$column];
    }
    
    return $values;
}

// Fonction pour générer les options de filtre
function generateFilterOptions($values, $selected = '')
{
    $options = '<option value="">Tous</option>';
    foreach ($values as $value) {
        $selectedAttr = ($selected == $value) ? 'selected' : '';
        $options .= "<option value=\"$value\" $selectedAttr>$value</option>";
    }
    return $options;
}

// Fonction pour construire la requête de filtre
function buildFilterQuery($filters)
{
    $query = '';
    if (!empty($filters['search'])) {
        $query .= '&search=' . urlencode($filters['search']);
    }
    if (!empty($filters['nature'])) {
        $query .= '&nature=' . urlencode($filters['nature']);
    }
    if (!empty($filters['type'])) {
        $query .= '&type=' . urlencode($filters['type']);
    }
    if (!empty($filters['date_debut'])) {
        $query .= '&date_debut=' . urlencode($filters['date_debut']);
    }
    if (!empty($filters['date_fin'])) {
        $query .= '&date_fin=' . urlencode($filters['date_fin']);
    }
    return $query;
}

function recupererTousLesCourriers()
{
    global $connexion;
    $sql = "SELECT * FROM courrier ORDER BY date DESC";
    $result = $connexion->query($sql);

    $courriers = [];
    while ($row = $result->fetch_assoc()) {
        $courriers[] = $row;
    }
    return $courriers;
}

function recupererCourrierParId($id)
{
    global $connexion;
    $sql = "SELECT * FROM courrier WHERE id_courrier = ?";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

function mettreAJourCourrier($id, $date, $numero, $objet, $nature, $type, $expediteur, $pdf = null)
{
    global $connexion;
    
    try {
        if ($pdf !== null) {
            $sql = "UPDATE courrier SET date = ?, Numero_courrier = ?, Objet = ?, Nature = ?, Type = ?, Expediteur = ?, pdf = ? WHERE id_courrier = ?";
            $stmt = $connexion->prepare($sql);
            $stmt->bind_param("sssssssi", $date, $numero, $objet, $nature, $type, $expediteur, $pdf, $id);
        } else {
            $sql = "UPDATE courrier SET date = ?, Numero_courrier = ?, Objet = ?, Nature = ?, Type = ?, Expediteur = ? WHERE id_courrier = ?";
            $stmt = $connexion->prepare($sql);
            $stmt->bind_param("ssssssi", $date, $numero, $objet, $nature, $type, $expediteur, $id);
        }

        if ($stmt->execute()) {
            header("Location: liste_courrier.php?success=1");
            exit();
        } else {
            header("Location: liste_courrier.php?error=update_failed");
            exit();
        }
    } catch (Exception $e) {
        // Loguer l'erreur dans un fichier log
        error_log("Erreur lors de la mise à jour du courrier: " . $e->getMessage());
        
        // Redirection avec message d'erreur
        header("Location: liste_courrier.php?error=database_error");
        exit();
    }
}

function supprimerCourrier($id) {
    global $connexion;
    
    try {
        // 1. Validation de l'ID
        if (!is_numeric($id) || $id <= 0) {
            throw new Exception("ID de courrier invalide");
        }

        // 2. Vérification de l'existence du courrier
        $verifSql = "SELECT id_courrier FROM courrier WHERE id_courrier = ?";
        $verifStmt = $connexion->prepare($verifSql);
        $verifStmt->bind_param("i", $id);
        $verifStmt->execute();
        
        if ($verifStmt->get_result()->num_rows === 0) {
            throw new Exception("Le courrier n'existe pas");
        }

        // 3. Suppression du PDF associé si existant (optionnel)
        $pdfSql = "SELECT pdf FROM courrier WHERE id_courrier = ?";
        $pdfStmt = $connexion->prepare($pdfSql);
        $pdfStmt->bind_param("i", $id);
        $pdfStmt->execute();
        $pdfResult = $pdfStmt->get_result();
        
        if ($pdfRow = $pdfResult->fetch_assoc()) {
            // Si vous stockez les fichiers sur le système de fichiers
            // if (!empty($pdfRow['pdf']) && file_exists($pdfRow['pdf'])) {
            //     unlink($pdfRow['pdf']);
            // }
        }

        // 4. Suppression dans la base de données
        $deleteSql = "DELETE FROM courrier WHERE id_courrier = ?";
        $deleteStmt = $connexion->prepare($deleteSql);
        $deleteStmt->bind_param("i", $id);
        
        if ($deleteStmt->execute()) {
            // Journalisation de la suppression (optionnel)
            error_log("Courrier ID $id supprimé avec succès");
            return true;
        } else {
            throw new Exception("Erreur lors de la suppression");
        }
    } catch (Exception $e) {
        // Journalisation de l'erreur
        error_log("Erreur suppression courrier: " . $e->getMessage());
        return false;
    } finally {
        // Nettoyage
        if (isset($verifStmt)) $verifStmt->close();
        if (isset($pdfStmt)) $pdfStmt->close();
        if (isset($deleteStmt)) $deleteStmt->close();
    }
}

// ... [Le reste de vos fonctions existantes peut être conservé] ...