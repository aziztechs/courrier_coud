<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

// Connexion à la base de données
require('../../traitement/courrier_fonctions.php');
require('dash_traitements/traitement_dash.php');
include('../../activite.php');


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion Courrier</title>
    <link rel="icon" href="/courrier_coud/assets/images/log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/usersliste.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .stat-card {
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>
    <?php include('../../head.php'); ?>
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 140px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Tableau de Bord Bureau Courrier !<br>
                <span>
                    (<?= htmlspecialchars($_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']) ?>)
                </span>
            </p>
        </div>
    </div> 
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <a href="../courrier/liste_courriers.php?filter=all" style="text-decoration: none;">
                        <div class="card text-white bg-primary stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Courriers</h6>
                                        <h2 class="mb-0"><?= $stats['total_courriers'] ?></h2>
                                    </div>
                                    <i class="bi bi-envelope-fill" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="../courrier/liste_courriers.php?search=arrive&date_debut=&date_fin=" style="text-decoration: none;">
                        <div class="card text-white bg-success stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Courriers Arrivés</h6>
                                        <h2 class="mb-0"><?= $stats['courriers_arrivee'] ?></h2>
                                    </div>
                                    <i class="bi bi-download" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="../courrier/liste_courriers.php?search=Départ&date_debut=&date_fin=" style="text-decoration: none;">
                        <div class="card text-white bg-info stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Courriers Départs</h6>
                                        <h2 class="mb-0"><?= $stats['courriers_depart'] ?></h2>
                                    </div>
                                    <i class="bi bi-upload" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="../courrier/liste_courriers.php?search=interne&date_debut=&date_fin=" style="text-decoration: none;">
                        <div class="card text-white bg-warning stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Courriers Internes</h6>
                                        <h2 class="mb-0"><?= $stats['courriers_internes'] ?></h2>
                                    </div>
                                    <i class="bi bi-house" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="../courrier/liste_courriers.php?search=externe&date_debut=&date_fin=" style="text-decoration: none;">
                        <div class="card text-white bg-secondary stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Courriers Externes</h6>
                                        <h2 class="mb-0"><?= $stats['courriers_externes'] ?></h2>
                                    </div>
                                    <i class="bi bi-globe" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="../archives/liste_archive.php" style="text-decoration: none;">
                        <div class="card text-white bg-danger stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Courriers Archivés</h6>
                                        <h2 class="mb-0"><?= $stats['courriers_archives'] ?></h2>
                                    </div>
                                    <i class="bi bi-archive" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Derniers courriers et imputations en attente -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header text-white bg-primary">
                        <h5>Derniers courriers</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">N°</th>
                                        <th class="text-center">Date</th>
                                        <th class="text-center">Objet</th>
                                        <th class="text-center">Type</th>
                                        <th class="text-center">Nature</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($last_courriers as $courrier): ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($courrier['Numero_Courrier']) ?></td>
                                        <td class="text-center"><?= date('d/m/Y H:i', strtotime($courrier['date'])) ?></td>
                                        <td class="text-center"><?= htmlspecialchars(substr($courrier['Objet'], 0, 30)) ?>...</td>
                                        <td class="text-center"><?= htmlspecialchars($courrier['Type']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($courrier['Nature']) ?></td>
                                        <td class="text-center">
                                            <a href="../courrier/view_courrier.php?id=<?= $courrier['id_courrier'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script pour les graphiques (si vous voulez en ajouter)
        document.addEventListener('DOMContentLoaded', function() {
            // Vous pouvez ajouter ici l'initialisation de Chart.js pour des graphiques
        });
    </script>
</body>
</html>