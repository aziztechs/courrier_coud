<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

require('../../traitement/fonction.php');
include('../../activite.php');

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
$query = "SELECT * FROM user WHERE id_user = ?";
$stmt = $connexion->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Courrier - <?= htmlspecialchars($courrier['Numero_Courrier']) ?></title>
    <link rel="icon" href="../../assets/img/log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/usersliste.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .badge-nature {
            font-size: 0.9rem;
            padding: 0.5em 0.75em;
            border-radius: 50px;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .detail-value {
            padding-left: 15px;
            border-left: 3px solid var(--secondary-color);
        }
        
        .action-btn {
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-btn {
            background-color: #f8f9fa;
            color: var(--primary-color);
            border: 1px solid #dee2e6;
        }
        
        .back-btn:hover {
            background-color: #e9ecef;
        }
        
        .pdf-btn {
            background-color: var(--accent-color);
            color: white;
        }
        
        .pdf-btn:hover {
            background-color: #c0392b;
            color: white;
        }
        
        .section-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--secondary-color);
        }
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <?php include('../../head.php'); ?>
    
    <div class="container-fluid py-4 mt-4 animate-fade-in">
        <div class="row justify-content-center">
            <main class="col-lg-10 col-xl-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="dashboard.php" class="btn action-btn back-btn">
                        <i class="bi bi-arrow-left me-2"></i> Retour à la liste
                    </a>
                    <a href="../uploads/<?= htmlspecialchars($courrier['pdf']) ?>" target="_blank" class="btn action-btn pdf-btn">
                        <i class="bi bi-file-earmark-pdf me-2"></i> Ouvrir le PDF
                    </a>
                </div>

                <!-- Détails du courrier -->
                <div class="card mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h4 mb-0 text-white">Détails du courrier</h2>
                            <small class="text-white-50">Informations complètes</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-light text-dark me-2">N° <?= htmlspecialchars($courrier['Numero_Courrier']) ?></span>
                            <span class="badge badge-nature bg-<?= $courrier['Nature'] === 'arrivee' ? 'success' : 'info' ?>">
                                <?= htmlspecialchars(ucfirst($courrier['Nature'])) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="section-title">Informations principales</h5>
                                <dl class="row">
                                    <dt class="col-sm-4 detail-label">Date de réception</dt>
                                    <dd class="col-sm-8 detail-value"><?= date('d/m/Y H:i', strtotime($courrier['date'])) ?></dd>

                                    <dt class="col-sm-4 detail-label">Objet</dt>
                                    <dd class="col-sm-8 detail-value"><?= htmlspecialchars($courrier['Objet']) ?></dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <h5 class="section-title">Classification</h5>
                                <dl class="row">
                                    <dt class="col-sm-4 detail-label">Nature</dt>
                                    <dd class="col-sm-8 detail-value">
                                        <span class="badge badge-nature bg-<?= $courrier['Nature'] === 'arrivee' ? 'success' : 'info' ?>">
                                            <?= htmlspecialchars(ucfirst($courrier['Nature'])) ?>
                                        </span>
                                    </dd>

                                    <dt class="col-sm-4 detail-label">Type</dt>
                                    <dd class="col-sm-8 detail-value">
                                        <span class="badge badge-nature bg-<?= $courrier['Type'] === 'interne' ? 'warning' : 'secondary' ?>">
                                            <?= htmlspecialchars(ucfirst($courrier['Type'])) ?>
                                        </span>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <h5 class="section-title">Détails supplémentaires</h5>
                                <dl class="row">
                                    <dt class="col-sm-2 detail-label">Expéditeur</dt>
                                    <dd class="col-sm-10 detail-value"><?= htmlspecialchars($courrier['Expediteur']) ?></dd>

                                    <dt class="col-sm-2 detail-label">Fichier joint</dt>
                                    <dd class="col-sm-10 detail-value">
                                        <a href="../uploads/<?= htmlspecialchars($courrier['pdf']) ?>" target="_blank" class="d-inline-flex align-items-center text-decoration-none text-primary">
                                            <i class="bi bi-file-earmark-pdf-fill me-2 fs-5"></i>
                                            <span><?= htmlspecialchars($courrier['pdf']) ?></span>
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
    <script>
        // Animation pour les éléments
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.card, .btn');
            elements.forEach((el, index) => {
                el.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>