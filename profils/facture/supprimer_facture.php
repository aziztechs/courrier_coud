<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

require_once('../../traitement/fonction_facture.php');
require_once('../../traitement/factures/traitement_sup_facture.php');


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD - Supprimer Facture</title>
    <link rel="shortcut icon" href="../../assets/img/log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/styleCourrier.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .confirmation-box {
            max-width: 600px;
            margin: 2rem auto;
            border-left: 5px solid #dc3545;
        }
    </style>
</head>
<body>
    <?php include('../../head.php'); ?>
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 120px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Suppression de Facture<br>
                <span>
                    (<?= htmlspecialchars($_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']) ?>)
                </span>
            </p>
        </div>
    </div>
    
    <div class="container_fluid mt-5">
        <div class="card confirmation-box">
            <div class="card-header bg-danger text-white">
                <h2 class="h4"><i class="bi bi-exclamation-triangle"></i> Confirmation de suppression</h2>
            </div>
            <div class="card-body">
                <h3 class="h5">Êtes-vous sûr de vouloir supprimer cette facture ?</h3>
                
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i> Cette action est irréversible et supprimera définitivement :
                    <ul class="mt-2">
                        <li>La facture <strong>#<?= htmlspecialchars($facture['numero_facture']) ?></strong></li>
                        <li>Le courrier associé <strong><?= htmlspecialchars($facture['numero_courrier']) ?></strong></li>
                        <?php if (!empty($facture['facture_pdf'])): ?>
                            <li>Le fichier PDF joint</li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <form method="post">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Confirmer la suppression
                        </button>
                    </form>
                    <a href="liste_factures.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Annuler et retour
                    </a>
                </div>
            </div>
            <div class="card-footer text-muted small">
                <i class="bi bi-clock-history"></i> Action effectuée le <?= date('d/m/Y à H:i') ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>