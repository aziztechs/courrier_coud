<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

require_once('../../traitement/fonction_suivi_courrier.php');

// Récupérer les valeurs ENUM pour les statuts
function get_enum_values($connexion, $column) {
    $result = $connexion->query("SHOW COLUMNS FROM suivi_courrier LIKE '$column'");
    $row = $result->fetch_assoc();
    preg_match("/^enum\(\'(.*)\'\)$/", $row['Type'], $matches);
    return explode("','", $matches[1]);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: AJOUTER SUIVI</title>
    <link rel="shortcut icon" href="../../assets/img/log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/styleCourrier.css">
    <link rel="stylesheet" href="css/liste.css">
    
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
            <p class="lead">Espace Administration : Ajouter un Suivi !<br>
                <span>
                    (<?php echo $_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']; ?>)
                </span>
            </p>
        </div>
    </div> 
    
    <div class="container-fluid mt-4">
        <div class="row g-3">

            <main class="col-md-12 px-md-4">
                <div class="form-container">
                    <div class="form-header d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 mb-0"><i class="fas fa-plus"></i> Ajouter un suivi</h2>
                        <a href="liste_suivi_courrier.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                        </a>
                    </div>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    
                    <form method="post" action="../../traitement/traitements.php" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="numero" class="form-label">N° de suivi<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="numero" name="numero" required>
                                <div class="invalid-feedback">Veuillez entrer un numéro de suivi.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="date_reception" class="form-label">Date de réception<span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_reception" name="date_reception" required>
                                <div class="invalid-feedback">Veuillez sélectionner une date de réception.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="expediteur" class="form-label">Expéditeur<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="expediteur" name="expediteur" required>
                                <div class="invalid-feedback">Veuillez entrer le nom de l'expéditeur.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="objet" class="form-label">Objet<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="objet" name="objet" required>
                                <div class="invalid-feedback">Veuillez entrer l'objet du suivi.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="destinataire" class="form-label">Destinataire<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="destinataire" name="destinataire" required>
                                <div class="invalid-feedback">Veuillez entrer le nom du destinataire.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="statut_1" class="form-label">Statut 1:<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="statut_1" name="statut_1" required>
                                <div class="invalid-feedback">Veuillez entrer le statut.</div> 
                            </div>
                            <div class="col-md-6">
                                <label for="statut_2" class="form-label">Statut 2:<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="statut_2" name="statut_2" required>
                                <div class="invalid-feedback">Veuillez entrer le statut.</div> 
                            </div>
                            <div class="col-md-6">
                                <label for="statut_3" class="form-label">Statut 3:<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="statut_3" name="statut_3" required>
                                <div class="invalid-feedback">Veuillez entrer le statut.</div> 
                            </div>
                            
                            
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    <i class="bi bi-save me-2"></i>Enregistrer
                                </button>
                                
                                <a href="liste_suivi_courrier.php" class="btn btn-outline-secondary ms-2 px-4 py-2">
                                    <i class="bi bi-arrow-left me-2"></i>Retour
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <script>
                        // Validation du formulaire
                        (function() {
                            'use strict';
                            window.addEventListener('load', function() {
                                var forms = document.getElementsByClassName('needs-validation');
                                Array.prototype.filter.call(forms, function(form) {
                                    form.addEventListener('submit', function(event) {
                                        if (form.checkValidity() === false) {
                                            event.preventDefault();
                                            event.stopPropagation();
                                        }
                                        form.classList.add('was-validated');
                                    }, false);
                                });
                            }, false);
                        })();
                    </script>
                </div>
            </main>
        </div>
    </div>


                            
    

</body>
</html>