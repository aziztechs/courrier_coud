<?php
session_start();
// Vérification de la session
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: /courrier_coud/');
    exit();
}

require_once('../../traitement/fonction_suivi_courrier.php');

// Récupérer l'ID du suivi à modifier
$id_suivi = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Récupérer les données existantes du suivi
$suivi = getSuiviById($id_suivi);

if (!$suivi) {
    $_SESSION['error_message'] = "Le suivi demandé n'existe pas";
    header('Location: liste_suivis.php');
    exit();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'numero' => trim($_POST['numero']),
            'date_reception' => trim($_POST['date_reception']),
            'expediteur' => trim($_POST['expediteur']),
            'objet' => trim($_POST['objet']),
            'destinataire' => trim($_POST['destinataire']),
            'statut_1' => trim($_POST['statut_1']),
            'statut_2' => trim($_POST['statut_2']),
            'statut_3' => trim($_POST['statut_3'])
        ];

        if (modifier_suivi(
            $id_suivi,
            $data['numero'],
            $data['date_reception'],
            $data['expediteur'],
            $data['objet'],
            $data['destinataire'],
            $data['statut_1'],
            $data['statut_2'],
            $data['statut_3']
        )) {
            $_SESSION['success_message'] = "Suivi modifié avec succès";
            header('Location: liste_suivi_courrier.php');
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: modif_suivi.php?id=$id_suivi");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: MODIFIER SUIVI</title>
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
   
    <div class="container-fluid py-4">
        <div class="row">
            <main class="col-12">
                <div class="form-container">
                    <div class="form-header d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 mb-0"><i class="bi bi-pencil-square me-2"></i>Modifier le suivi #<?= $id_suivi ?></h2>
                        <a href="liste_suivi_courrier.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                        </a>
                    </div>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> 
                            <?= htmlspecialchars($_SESSION['error_message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    
                    <form method="post" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="numero" class="form-label">N° de suivi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="numero" name="numero" 
                                       value="<?= htmlspecialchars($suivi['numero']) ?>" required>
                                <div class="invalid-feedback">Veuillez entrer un numéro de suivi.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="date_reception" class="form-label">Date de réception <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_reception" name="date_reception" 
                                       value="<?= htmlspecialchars($suivi['date_reception']) ?>" required>
                                <div class="invalid-feedback">Veuillez sélectionner une date valide.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="expediteur" class="form-label">Expéditeur <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="expediteur" name="expediteur" 
                                       value="<?= htmlspecialchars($suivi['expediteur']) ?>" required>
                                <div class="invalid-feedback">Veuillez entrer l'expéditeur.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="objet" class="form-label">Objet <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="objet" name="objet" 
                                       value="<?= htmlspecialchars($suivi['objet']) ?>" required>
                                <div class="invalid-feedback">Veuillez entrer l'objet.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="destinataire" class="form-label">Destinataire <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="destinataire" name="destinataire" 
                                       value="<?= htmlspecialchars($suivi['destinataire']) ?>" required>
                                <div class="invalid-feedback">Veuillez entrer le destinataire.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="statut_1" class="form-label">Statut 1 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="statut_1" name="statut_1" 
                                       value="<?= htmlspecialchars($suivi['statut_1']) ?>" required>
                                <div class="invalid-feedback">Veuillez entrer le statut 1.</div> 
                            </div>
                            
                            <div class="col-md-6">
                                <label for="statut_2" class="form-label">Statut 2 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="statut_2" name="statut_2" 
                                       value="<?= htmlspecialchars($suivi['statut_2']) ?>" required>
                                <div class="invalid-feedback">Veuillez entrer le statut 2.</div> 
                            </div>
                            
                            <div class="col-md-6">
                                <label for="statut_3" class="form-label">Statut 3 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="statut_3" name="statut_3" 
                                       value="<?= htmlspecialchars($suivi['statut_3']) ?>" required>
                                <div class="invalid-feedback">Veuillez entrer le statut 3.</div> 
                            </div>
                            
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    <i class="bi bi-check-circle me-2"></i>Enregistrer les modifications
                                </button>
                                
                                <a href="liste_suivi_courrier.php" class="btn btn-outline-secondary ms-2 px-4 py-2">
                                    <i class="bi bi-x-circle me-2"></i>Annuler
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Validation du formulaire
        (function() {
            'use strict';
            
            const forms = document.querySelectorAll('.needs-validation');
            
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
            
            // Limiter la date de réception à aujourd'hui ou avant
            document.getElementById('date_reception').max = new Date().toISOString().split('T')[0];
            
            // Gestion des messages avec SweetAlert
            <?php if (isset($_SESSION['error_message'])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: '<?= addslashes($_SESSION['error_message']) ?>',
                    confirmButtonColor: '#0056b3'
                });
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: '<?= addslashes($_SESSION['success_message']) ?>',
                    confirmButtonColor: '#0056b3'
                });
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
        })();
    </script>
</body>
</html>