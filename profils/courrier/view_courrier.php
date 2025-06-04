<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

// Connexion à la base de données
require('../../traitement/fonction.php');
include('../../activite.php');
require('../../traitement/recupImpuAlldep.php');



// Vérifier si l'ID du courrier est présent
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$courrier_id = intval($_GET['id']);

// Récupérer les informations du courrier
$query = "SELECT * FROM courrier WHERE id_courrier = ?";
$stmt = $connexion->prepare($query);
$stmt->bind_param("i", $courrier_id);
$stmt->execute();
$courrier = $stmt->get_result()->fetch_assoc();

if (!$courrier) {
    header('Location: dashboard.php?error=courrier_not_found');
    exit();
}

// Récupérer l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM user WHERE id_user = ?";
$stmt = $connexion->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Vérifier si l'utilisateur peut effectuer des actions


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Courrier - <?= htmlspecialchars($courrier['Numero_Courrier']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include('../../head.php'); ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <main class="col-md-12">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div>
                        <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                        <a href="../uploads/<?= htmlspecialchars($courrier['pdf']) ?>" target="_blank" class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-pdf"></i> Voir PDF
                        </a>
                    </div>
                </div>

                <!-- Détails du courrier -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        
                        <h1 class="h2">Détails du Courrier</h1>
                        <h5>Courrier N° <?= htmlspecialchars($courrier['Numero_Courrier']) ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-sm-4">Date</dt>
                                    <dd class="col-sm-8"><?= date('d/m/Y H:i', strtotime($courrier['Date'])) ?></dd>

                                    <dt class="col-sm-4">Objet</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($courrier['Objet']) ?></dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-sm-4">Nature</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge bg-<?= $courrier['Nature'] === 'arrivee' ? 'success' : 'info' ?>">
                                            <?= htmlspecialchars(ucfirst($courrier['Nature'])) ?>
                                        </span>
                                    </dd>

                                    <dt class="col-sm-4">Type</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge bg-<?= $courrier['Type'] === 'interne' ? 'warning' : 'secondary' ?>">
                                            <?= htmlspecialchars(ucfirst($courrier['Type'])) ?>
                                        </span>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <dl class="row">
                                    <dt class="col-sm-2">Expéditeur</dt>
                                    <dd class="col-sm-10"><?= htmlspecialchars($courrier['Expediteur']) ?></dd>

                                    <dt class="col-sm-2">Fichier PDF</dt>
                                    <dd class="col-sm-10">
                                        <a href="../uploads/<?= htmlspecialchars($courrier['pdf']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="bi bi-file-earmark-pdf"></i> <?= htmlspecialchars($courrier['pdf']) ?>
                                        </a>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>