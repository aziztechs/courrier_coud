<?php

session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

require_once('../../traitement/fonction_archive.php');
require_once('../../traitement/fonction.php');
require_once('../../traitement/traitements.php');



$correspondances = getNumerosCorrespondance();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: AJOUTER ARCHIVE</title>
    <link rel="shortcut icon" href="../../assets/img/log.gif" type="image/x-icon">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="css/form.css">
    
    
</head>
<body>
    <?php include('../../head.php'); ?>
     <div class="info-banner  text-white d-flex justify-content-center align-items-center"
         style="height: 120px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Bienvenue !<br>
                <span>
                    (<?php echo $_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']; ?>)
                </span>
            </p>
        </div>
    </div> 
    
    <div class="container-fluid">
        <div class="row">
            <main class="col-md-12 px-md-4">
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="h4 mb-0"><i class="bi bi-archive"></i> Ajouter une archive</h2>
                    </div>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    
                    <form method="post" action="ajouter_archive.php" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row g-3 mb-4">
                            <!-- Type d'archivage -->
                            <div class="col-md-6">
                                <label for="type_archivage" class="form-label required-field">Type d'archivage</label>
                                <select class="form-select" id="type_archivage" name="type_archivage" required>
                                    <option value="" selected disabled>Sélectionner un type</option>
                                    <option value="manuel">Manuel</option>
                                    <option value="automatique">Automatique</option>
                                    <option value="annuel">Annuel</option>
                                </select>
                                <div class="invalid-feedback">
                                    Veuillez sélectionner un type d'archivage.
                                </div>
                            </div>
                            
                            <!-- Numéro de correspondance -->
                            <div class="col-md-6">
                                <label for="num_correspondance" class="form-label required-field">Numéro de correspondance</label>
                                <input type="text" class="form-control" id="num_correspondance" 
                                       name="num_correspondance" list="correspondanceList" required>
                                <datalist id="correspondanceList">
                                    <?php foreach ($correspondances as $corresp): ?>
                                        <option value="<?= htmlspecialchars($corresp) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                                <div class="invalid-feedback">
                                    Veuillez saisir un numéro de correspondance.
                                </div>
                            </div>
                            
                            <!-- Fichier PDF -->
                            <div class="col-12">
                                <label class="form-label required-field">Fichier PDF</label>
                                <div class="file-upload-container">
                                    <i class="bi bi-file-earmark-pdf fs-1 text-danger mb-2"></i>
                                    <p class="mb-3">Glissez-déposez votre fichier PDF ici ou cliquez pour sélectionner</p>
                                    <input type="file" class="form-control" id="pdf_archive" 
                                           name="pdf_archive" accept=".pdf" required>
                                    <div class="form-text mt-2">Taille maximale : 5Mo</div>
                                    <div class="invalid-feedback">
                                        Veuillez sélectionner un fichier PDF valide.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Motif d'archivage -->
                            <div class="col-md-6">
                                <label for="motif_archivage" class="form-label required-field">Motif d'archivage</label>
                                <select class="form-select" id="motif_archivage" name="motif_archivage" required>
                                    <option value="" selected disabled>Sélectionner un motif</option>
                                    <option value="traitement_termine">Traitement terminé</option>
                                    <option value="delai_depasse">Délai dépassé</option>
                                    <option value="demande_specifique">Demande spécifique</option>
                                    <option value="archivage_annuel">Archivage annuel</option>
                                </select>
                                <div class="invalid-feedback">
                                    Veuillez sélectionner un motif d'archivage.
                                </div>
                            </div>
                            
                            <!-- Commentaire -->
                            <div class="col-md-6">
                                <label for="commentaire" class="form-label">Commentaire</label>
                                <textarea class="form-control" id="commentaire" name="commentaire" 
                                          rows="3" placeholder="Facultatif"></textarea>
                            </div>
                        </div>
                        
                        <!-- Boutons -->
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="liste_archive.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-submit text-white">
                                <i class="bi bi-save"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    // Validation côté client
    (function() {
        'use strict';
        
        // Sélection de tous les formulaires avec la classe needs-validation
        var forms = document.querySelectorAll('.needs-validation');
        
        // Boucle sur chaque formulaire pour empêcher la soumission
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
    })();
    
    // Affichage du nom du fichier sélectionné
    document.getElementById('pdf_archive').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'Aucun fichier sélectionné';
        document.querySelector('.file-upload-container p').textContent = fileName;
    });
    
    // Drag and drop amélioré
    const fileUploadContainer = document.querySelector('.file-upload-container');
    const fileInput = document.getElementById('pdf_archive');
    
    fileUploadContainer.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileUploadContainer.style.borderColor = '#0056b3';
        fileUploadContainer.style.backgroundColor = '#e9f0f8';
    });
    
    fileUploadContainer.addEventListener('dragleave', () => {
        fileUploadContainer.style.borderColor = '#dee2e6';
        fileUploadContainer.style.backgroundColor = '';
    });
    
    fileUploadContainer.addEventListener('drop', (e) => {
        e.preventDefault();
        fileUploadContainer.style.borderColor = '#dee2e6';
        fileUploadContainer.style.backgroundColor = '';
        
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            const fileName = e.dataTransfer.files[0].name;
            document.querySelector('.file-upload-container p').textContent = fileName;
        }
    });
    </script>
</body>
</html>