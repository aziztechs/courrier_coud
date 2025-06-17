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



// Récupération des statistiques
$stats = [
    'total_courriers' => 0,
    'courriers_arrivee' => 0,
    'courriers_depart' => 0,
    'courriers_internes' => 0,
    'courriers_externes' => 0,
    'courriers_non_traites' => 0,
    'courriers_archives' => 0 // Si vous avez une table d'archives, vous pouvez l'ajouter ici
];

// Requêtes pour les statistiques
$queries = [
    'total_courriers' => "SELECT COUNT(*) FROM courrier",
    'courriers_arrivee' => "SELECT COUNT(*) FROM courrier WHERE Nature = 'arrivee'",
    'courriers_depart' => "SELECT COUNT(*) FROM courrier WHERE Nature = 'depart'",
    'courriers_internes' => "SELECT COUNT(*) FROM courrier WHERE Type = 'interne'",
    'courriers_externes' => "SELECT COUNT(*) FROM courrier WHERE Type = 'externe'",
    'courriers_non_traites' => "SELECT COUNT(DISTINCT c.id_courrier) FROM courrier c LEFT JOIN imputation i ON c.id_courrier = i.id_courrier WHERE i.id_imputation IS NULL"
];

// Si vous avez une table d'archives, vous pouvez ajouter une requête pour les courriers archivés
$queries['courriers_archives'] = "SELECT COUNT(*) FROM archive WHERE date_archivage IS NOT NULL";


foreach ($queries as $key => $sql) {
    $result = $connexion->query($sql);
    $stats[$key] = $result->fetch_row()[0];
}

// Récupération des derniers courriers
$query = "SELECT c.*, COUNT(i.id_imputation) as imputations 
          FROM courrier c 
          LEFT JOIN imputation i ON c.id_courrier = i.id_courrier 
          GROUP BY c.id_courrier 
          ORDER BY c.Date DESC 
          LIMIT 5";
$last_courriers = $connexion->query($query)->fetch_all(MYSQLI_ASSOC);


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion Courrier</title>
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
         style="height: 120px; background-color: #0056b3;">
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
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-success stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Courriers Arrivée</h6>
                                        <h2 class="mb-0"><?= $stats['courriers_arrivee'] ?></h2>
                                    </div>
                                    <i class="bi bi-download" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-info stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Courriers Départ</h6>
                                        <h2 class="mb-0"><?= $stats['courriers_depart'] ?></h2>
                                    </div>
                                    <i class="bi bi-upload" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
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
                    </div>
                    <div class="col-md-4 mb-3">
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
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-danger  stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Courriers Archivés</h6>
                                        <h2 class="mb-0"><?= $stats['courriers_archives'] ?></h2>
                                    </div>
                                    <i class="bi bi-globe" style="font-size: 2rem;"></i>
                                </div>
                            </div>
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
                                                <th>N°</th>
                                                <th>Date</th>
                                                <th>Objet</th>
                                                <th>Type</th>
                                                <th>Nature</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($last_courriers as $courrier): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($courrier['Numero_Courrier']) ?></td>
                                                <td><?= date('d/m/Y H:i', strtotime($courrier['Date'])) ?></td>
                                                <td><?= htmlspecialchars(substr($courrier['Objet'], 0, 30)) ?>...</td>
                                                <td><?= htmlspecialchars($courrier['Type']) ?></td>
                                                <td><?= htmlspecialchars($courrier['Nature']) ?></td>
                                                <td>
                                                    <a href="view_courrier.php?id=<?= $courrier['id_courrier'] ?>" class="btn btn-sm btn-primary">
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

                    <?php if (!empty($pending_imputations)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-white">
                                <h5>Imputations en attente</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php foreach ($pending_imputations as $imputation): ?>
                                    <a href="suivi.php?imputation=<?= $imputation['id_imputation'] ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">N° <?= htmlspecialchars($imputation['Numero_Courrier']) ?></h6>
                                            <small><?= date('d/m/Y', strtotime($imputation['date_impu'])) ?></small>
                                        </div>
                                        <p class="mb-1"><?= htmlspecialchars(substr($imputation['Objet'], 0, 50)) ?>...</p>
                                        <small>Instruction: <?= htmlspecialchars($imputation['Instruction'] ?: $imputation['instruction_personnalisee']) ?></small>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
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