<?php
require_once('fonction.php');

/** ############# Traitement du formulaire d'ajout d'archive ###############*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validation des données
        $requiredFields = ['type_archivage', 'num_correspondance', 'motif_archivage'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Le champ $field est obligatoire.");
            }
        }
        
        // Vérification du fichier PDF
        if (!isset($_FILES['pdf_archive']) || $_FILES['pdf_archive']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Veuillez sélectionner un fichier PDF valide.");
        }
        
        $fileInfo = pathinfo($_FILES['pdf_archive']['name']);
        if (strtolower($fileInfo['extension']) !== 'pdf') {
            throw new Exception("Seuls les fichiers PDF sont acceptés.");
        }

        if ($_FILES['pdf_archive']['size'] > 5 * 1024 * 1024) {
            throw new Exception("Le fichier est trop volumineux (max 5Mo).");
        }

        // Préparation du dossier et du nom de fichier
        $uploadDir = '../uploads/archives/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['pdf_archive']['name']);
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['pdf_archive']['tmp_name'], $uploadPath)) {
            $data = [
                'type_archivage' => $_POST['type_archivage'],
                'num_correspondance' => $_POST['num_correspondance'],
                'pdf_archive' => 'archives/' . $fileName,
                'motif_archivage' => $_POST['motif_archivage'],
                'commentaire' => $_POST['commentaire'] ?? null
            ];
            
            if (ajouterArchive($data)) {
                $_SESSION['success_message'] = "L'archive a été ajoutée avec succès.";
                header('Location: liste_archive.php');
                exit();
            } else {
                throw new Exception("Erreur lors de l'ajout dans la base de données.");
            }
        } else {
            throw new Exception("Erreur lors du téléchargement du fichier.");
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}

/** ############# fin Traitement du formulaire d'ajout d'archive ###############*/

/** #############  Traitement du formulaire d'ajout suivi ###############*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $connexion->prepare("INSERT INTO suivi_courrier
                          (numero, date_reception, expediteur, objet, destinataire, statut_1, statut_2, statut_3) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", 
        $_POST['numero'],
        $_POST['date_reception'],
        $_POST['expediteur'],
        $_POST['objet'],
        $_POST['destinataire'],
        $_POST['statut_1'],
        $_POST['statut_2'],
        $_POST['statut_3']
    );
    
    if ($stmt->execute()) {
        header("Location: ../profils/suivi/liste_suivi_courrier.php");
        exit();
    } else {
        $erreur = "Erreur lors de l'ajout";
    }
}

/** ############# fin Traitement du formulaire d'ajout suivi ###############*/

/** ############# Traitement liste facture  ###############*/

// Configuration de la pagination
$factures_par_page = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $factures_par_page;

// Récupération des paramètres de recherche
$filters = [
    'search' => isset($_GET['search']) ? mysqli_real_escape_string($connexion, $_GET['search']) : '',
    'type_facture' => isset($_GET['type_facture']) ? mysqli_real_escape_string($connexion, $_GET['type_facture']) : '',
    'date_arrive' => isset($_GET['date_arrive']) ? mysqli_real_escape_string($connexion, $_GET['date_arrive']) : ''
];

// Construction de la requête de recherche
$where = [];
if (!empty($filters['search'])) {
    $where[] = "(numero_facture LIKE '%{$filters['search']}%' OR 
    numero_courrier LIKE '%{$filters['search']}%' OR 
    expediteur LIKE '%{$filters['search']}%')";
}
if (!empty($filters['type_facture'])) {
    $where[] = "type_facture = '{$filters['type_facture']}'";
}
if (!empty($filters['date_arrive'])) {
    $where[] = "date_arrive = '{$filters['date_arrive']}'";
}
$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Récupération des factures
$sql = "SELECT * FROM facture $where_clause ORDER BY date_arrive DESC LIMIT $offset, $factures_par_page";
$result = mysqli_query($connexion, $sql);
$factures = [];
while ($row = mysqli_fetch_assoc($result)) {
    $factures[] = $row;
}

// Récupération du nombre total de factures pour la pagination
$count_sql = "SELECT COUNT(*) as total FROM facture $where_clause";
$count_result = mysqli_query($connexion, $count_sql);
$total_factures = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_factures / $factures_par_page);

// Récupération des types de factures disponibles
$types_sql = "SELECT DISTINCT type_facture FROM facture ORDER BY type_facture";
$types_result = mysqli_query($connexion, $types_sql);
$types = [];
while ($row = mysqli_fetch_assoc($types_result)) {
    $types[] = $row['type_facture'];
}
/** ############# Fin Traitement liste facture  ###############*/