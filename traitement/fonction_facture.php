<?php
Require_once ('fonction.php'); // Inclure le fichier de configuration pour la connexion à la base de données

// CREATE - Ajouter une facture
function ajouter_facture($data, $pdf_path = null) {
    global $connexion;
    
    $date_arrive = mysqli_real_escape_string($connexion, $data['date_arrive']);
    $numero_courrier = mysqli_real_escape_string($connexion, $data['numero_courrier']);
    $expediteur = mysqli_real_escape_string($connexion, $data['expediteur']);
    $numero_facture = mysqli_real_escape_string($connexion, $data['numero_facture']);
    $decade = mysqli_real_escape_string($connexion, $data['decade']);
    $montant_ttc = floatval($data['montant_ttc']);
    $type_facture = mysqli_real_escape_string($connexion, $data['type_facture']);
    $facture_pdf = mysqli_real_escape_string($connexion, $pdf_path);

    $sql = "INSERT INTO facture 
            (date_arrive, numero_courrier, expediteur, numero_facture, decade, montant_ttc, type_facture, facture_pdf) 
            VALUES 
            ('$date_arrive', '$numero_courrier', '$expediteur', '$numero_facture', '$decade', $montant_ttc, '$type_facture', " . ($pdf_path ? "'$facture_pdf'" : "NULL") . ")";

    return mysqli_query($connexion, $sql);
}

// READ - Obtenir une facture par ID
function get_facture($id_facture) {
    global $connexion;
    
    $id = intval($id_facture);
    $sql = "SELECT * FROM facture WHERE id_facture = $id";
    $result = mysqli_query($connexion, $sql);
    
    return mysqli_fetch_assoc($result);
}

// READ - Lister toutes les factures
function lister_factures($limit = 0, $offset = 0) {
    global $connexion;
    
    $sql = "SELECT * FROM facture ORDER BY date_arrive DESC";
    
    if ($limit > 0) {
        $sql .= " LIMIT " . intval($offset) . ", " . intval($limit);
    }
    
    $result = mysqli_query($connexion, $sql);
    $factures = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $factures[] = $row;
    }
    
    return $factures;
}

// UPDATE - Modifier une facture
function modifier_facture($id_facture, $data, $pdf_path = null) {
    global $connexion;
    
    $id = intval($id_facture);
    $date_arrive = mysqli_real_escape_string($connexion, $data['date_arrive']);
    $numero_courrier = mysqli_real_escape_string($connexion, $data['numero_courrier']);
    $expediteur = mysqli_real_escape_string($connexion, $data['expediteur']);
    $numero_facture = mysqli_real_escape_string($connexion, $data['numero_facture']);
    $decade = mysqli_real_escape_string($connexion, $data['decade']);
    $montant_ttc = floatval($data['montant_ttc']);
    $type_facture = mysqli_real_escape_string($connexion, $data['type_facture']);
    
    $sql = "UPDATE facture SET
            date_arrive = '$date_arrive',
            numero_courrier = '$numero_courrier',
            expediteur = '$expediteur',
            numero_facture = '$numero_facture',
            decade = '$decade',
            montant_ttc = $montant_ttc,
            type_facture = '$type_facture'" .
            ($pdf_path ? ", facture_pdf = '" . mysqli_real_escape_string($connexion, $pdf_path) . "'" : "") .
            " WHERE id_facture = $id";
    
    return mysqli_query($connexion, $sql);
}

// DELETE - Supprimer une facture
function supprimer_facture($id_facture) {
    global $connexion;
    
    $id = intval($id_facture);
    
    // Récupérer le chemin du PDF avant suppression
    $facture = get_facture($id);
    
    $sql = "DELETE FROM facture WHERE id_facture = $id";
    $result = mysqli_query($connexion, $sql);
    
    // Supprimer le fichier PDF si existant
    if ($result && !empty($facture['facture_pdf']) && file_exists($facture['facture_pdf'])) {
        unlink($facture['facture_pdf']);
    }
    
    return $result;
}

// Gestion de l'upload PDF
function upload_pdf($file, $upload_dir = 'uploads/factures/') {
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Vérification du type MIME (PDF)
    $allowed_types = ['application/pdf'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception("Seuls les fichiers PDF sont autorisés");
    }
    
    $file_name = uniqid() . '_' . basename($file['name']);
    $target_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $target_path;
    }
    
    throw new Exception("Erreur lors de l'upload du fichier");
}
?>

