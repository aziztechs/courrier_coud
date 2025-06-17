<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

require_once('../../traitement/fonction_facture.php');
require_once('../../traitement/factures/traitement_ajout_facture.php');



?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD - Ajouter une Facture</title>
    <link rel="shortcut icon" href="../../assets/img/log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/styleCourrier.css">
    <link rel="stylesheet" href="css/liste.css">
    <link rel="stylesheet" href="css/form.css">

    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .required:after {
            content: " *";
            color: red;
        }
        .form-container {
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <?php include('../../head.php'); ?>
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 120px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Ajout de Facture<br>
                <span>
                    (<?= htmlspecialchars($_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']) ?>)
                </span>
            </p>
        </div>
    </div>
    
    <div class="container_fluid mt-4">
        <div class="card form-container">
            <div class="card-header bg-primary text-white">
                <h2 class="h4"><i class="bi bi-file-earmark-plus"></i> Nouvelle Facture</h2>
            </div>
            
            <div class="card-body">
                <?php if (isset($show_redirect_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i> La facture a été ajoutée avec succès. Redirection vers la liste dans 2 secondes...
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                        </div>
                    </div>
                <?php elseif (!empty($errors['general'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?= $errors['general'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="post" enctype="multipart/form-data" novalidate>
                    <div class="row g-3">
                        <!-- Date d'arrivée -->
                        <div class="col-md-6">
                            <label for="date_arrive" class="form-label required">Date d'arrivée</label>
                            <input type="date" class="form-control <?= isset($errors['date_arrive']) ? 'is-invalid' : '' ?>" 
                                   id="date_arrive" name="date_arrive" 
                                   value="<?= htmlspecialchars($data['date_arrive']) ?>" required>
                            <?php if (isset($errors['date_arrive'])): ?>
                                <div class="invalid-feedback"><?= $errors['date_arrive'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Numéro de courrier -->
                        <div class="col-md-6">
                            <label for="numero_courrier" class="form-label required">Numéro de courrier</label>
                            <input type="text" class="form-control <?= isset($errors['numero_courrier']) ? 'is-invalid' : '' ?>" 
                                   id="numero_courrier" name="numero_courrier" 
                                   value="<?= htmlspecialchars($data['numero_courrier']) ?>" required>
                            <?php if (isset($errors['numero_courrier'])): ?>
                                <div class="invalid-feedback"><?= $errors['numero_courrier'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Expéditeur -->
                        <div class="col-6">
                            <label for="expediteur" class="form-label required">Expéditeur</label>
                            <input type="text" class="form-control <?= isset($errors['expediteur']) ? 'is-invalid' : '' ?>" 
                                   id="expediteur" name="expediteur" 
                                   value="<?= htmlspecialchars($data['expediteur']) ?>" required>
                            <?php if (isset($errors['expediteur'])): ?>
                                <div class="invalid-feedback"><?= $errors['expediteur'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Numéro de facture -->
                        <div class="col-md-6">
                            <label for="numero_facture" class="form-label required">Numéro de facture</label>
                            <input type="text" class="form-control <?= isset($errors['numero_facture']) ? 'is-invalid' : '' ?>" 
                                   id="numero_facture" name="numero_facture" 
                                   value="<?= htmlspecialchars($data['numero_facture']) ?>" required>
                            <?php if (isset($errors['numero_facture'])): ?>
                                <div class="invalid-feedback"><?= $errors['numero_facture'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Décade -->
                        <div class="col-md-6">
                            <label for="decade" class="form-label">Période (décade)</label>
                            <input type="text" class="form-control" id="decade" name="decade" 
                                   value="<?= htmlspecialchars($data['decade']) ?>">
                        </div>
                        
                        <!-- Montant TTC -->
                        <div class="col-md-6">
                            <label for="montant_ttc" class="form-label required">Montant TTC (FCFA)</label>
                            <input type="text" class="form-control <?= isset($errors['montant_ttc']) ? 'is-invalid' : '' ?>" 
                                   id="montant_ttc" name="montant_ttc" 
                                   value="<?= htmlspecialchars($data['montant_ttc']) ?>" required>
                            <?php if (isset($errors['montant_ttc'])): ?>
                                <div class="invalid-feedback"><?= $errors['montant_ttc'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Type de facture -->
                        <div class="col-md-6">
                            <label for="type_facture" class="form-label required">Type de facture</label>
                            <input type="text" class="form-control <?= isset($errors['type_facture']) ? 'is-invalid' : '' ?>" 
                                   id="type_facture" name="type_facture" 
                                   value="<?= htmlspecialchars($data['type_facture']) ?>" required>
                            <?php if (isset($errors['type_facture'])): ?>
                                <div class="invalid-feedback"><?= $errors['type_facture'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Fichier PDF -->
                        <div class="col-6">
                            <label for="facture_pdf" class="form-label">Fichier PDF (optionnel)</label>
                            <input type="file" class="form-control <?= isset($errors['facture_pdf']) ? 'is-invalid' : '' ?>" 
                                   id="facture_pdf" name="facture_pdf" accept=".pdf">
                            <?php if (isset($errors['facture_pdf'])): ?>
                                <div class="invalid-feedback"><?= $errors['facture_pdf'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">Taille maximale : 5MB - Format accepté : PDF</div>
                        </div>
                        
                        <!-- Boutons -->
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-save"></i> Enregistrer
                            </button>
                            <a href="liste_factures.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configuration de Flatpickr pour la date
        flatpickr("#date_arrive", {
            dateFormat: "Y-m-d",
            allowInput: true,
            locale: "fr",
            defaultDate: "today"
        });
        
        // Formatage du montant
        document.getElementById('montant_ttc').addEventListener('blur', function(e) {
            let value = e.target.value.replace(',', '.');
            if (!isNaN(value) && value !== '') {
                e.target.value = parseFloat(value).toFixed(2);
            }
        });
    });
    </script>
</body>
</html>