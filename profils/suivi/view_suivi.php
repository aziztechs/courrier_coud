<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

require_once('../../traitement/fonction_suivi_courrier.php');

if (!isset($_GET['id'])) {
    header("Location: liste_suivi.php");
    exit();
}

$id = $_GET['id'];
$suivi = $connexion->query("SELECT * FROM suivi_courrier WHERE id = $id")->fetch_assoc();

if (!$suivi) {
    header("Location: liste_suivi_courrier.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du suivi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-details {
            max-width: 1200px;
            margin: 30px auto;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: none;
        }
        .card-header {
            background-color: #0056b3;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        .card-body {
            padding: 25px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        .detail-item {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }
        .logo-item {
            max-width: 150px;
           
            
        }
        .detail-label {
            font-weight: 600;
            color: #f8f9fa;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        .detail-value {
            color: #f8f9fa;
            padding: 8px 12px;
            background-color: #f8f9fa;
            border-radius: 5px;
            min-height: 30px;
            display: flex;
            align-items: center;
        }
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .action-buttons {
            margin-top: 25px;
            display: flex;
            gap: 10px;
        }
        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include('../../head.php'); ?>
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 120px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Gestion des Courriers<br>
                <span>
                    (<?= htmlspecialchars($_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']) ?>)
                </span>
            </p>
        </div>
    </div>


    <div class="container-fluid mt-4">
        <div class="card card-details">
            <div class="card-header">
                <h2 class="h4 mb-0"><i class="bi bi-card-checklist me-2"></i>Détails du suivi #<?= htmlspecialchars($suivi['id']) ?></h2>
            </div>
            <div class="card-body">
                <div class="details-grid">
                    <!-- Colonne 1 -->
                     <div class="detail-item logo-item">
                       <img src="../../assets/images/logo.png" alt="logo" sizes="" srcset="">
                    </div>
                    <div class="detail-item">
                        <span class="detail-label text-dark">Numéro</span>
                        <span class="detail-value text-dark"><?= htmlspecialchars($suivi['numero']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label text-dark">Date réception</span>
                        <span class="detail-value text-dark"><?= date('d/m/Y H:i', strtotime($suivi['date_reception'])) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label text-dark">Expéditeur</span>
                        <span class="detail-value text-dark"><?= htmlspecialchars($suivi['expediteur']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label text-dark">Objet</span>
                        <span class="detail-value text-dark"><?= htmlspecialchars($suivi['objet']) ?></span>
                    </div>
                    
                    
                    <!-- Colonne 2 -->
                    <div class="detail-item">
                        <span class="detail-label text-dark">Destinataire</span>
                        <span class="detail-value text-dark"><?= htmlspecialchars($suivi['destinataire']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label text-dark">Statut 1</span>
                        <span class="detail-value text-dark">
                            <span class="badge-status 
                                <?= $suivi['statut_1'] == 'CSA' ? 'bg-primary text-white' : 
                                   ($suivi['statut_1'] == 'En cours' ? 'bg-warning text-dark' : 
                                   ($suivi['statut_1'] == 'CHRONO' ? 'bg-success text-white' : 'bg-secondary text-white')) ?>">
                                <?= htmlspecialchars($suivi['statut_1']) ?>
                            </span>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label text-dark">Statut 2</span>
                        <span class="detail-value text-dark">
                            <span class="badge-status 
                                <?= $suivi['statut_2'] == 'CSA' ? 'bg-primary text-white' : 
                                   ($suivi['statut_2'] == 'En cours' ? 'bg-warning text-white' : 
                                   ($suivi['statut_2'] == 'CHRONO' ? 'bg-success text-white' : 'bg-secondary text-white')) ?>">
                                <?= htmlspecialchars($suivi['statut_2']) ?>
                            </span>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label text-dark">Statut 3</span>
                        <span class="detail-value text-dark">
                            <span class="badge-status 
                                <?= $suivi['statut_3'] == 'CSA' ? 'bg-primary text-white' : 
                                   ($suivi['statut_3'] == 'En cours' ? 'bg-warning text-white' : 
                                   ($suivi['statut_3'] == 'CHRONO' ? 'bg-success text-white' : 'bg-secondary text-white ')) ?>">
                                <?= htmlspecialchars($suivi['statut_3']) ?>
                            </span>
                        </span>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="modif_suivi.php?id=<?= $suivi['id'] ?>" class="btn btn-primary">
                        <i class="bi bi-pencil-square me-1"></i> Modifier
                    </a>
                    <a href="liste_suivi_courrier.php" class="btn btn-outline-secondary bg-secondary text-white">
                        <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>