<?php
// Démarrer la session
session_start();

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

// Inclure les fichiers nécessaires
require_once('../../traitement/fonction_archive.php');
require_once('../../traitement/fonction.php');

require_once('../../traitement/archive/traitement_modif_archive.php');


// Récupérer la liste des numéros de correspondance existants
$correspondances = getNumerosCorrespondance();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: MODIFIER ARCHIVE</title>
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
    <link rel="icon" href="log.gif" type="image/x-icon">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../assets/css/formCour.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <?php include('../../head.php'); ?>
    
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 120px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Modification d'archive<br>
                <span>
                    (<?php echo htmlspecialchars($_SESSION['Prenom']) . ' ' . htmlspecialchars($_SESSION['Nom']) . ' - ' . htmlspecialchars($_SESSION['Fonction']); ?>)
                </span>
            </p>
        </div>
    </div>
    
    <div class="container-form mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1 class="h2">Modifier une archive</h1>
            </div>
            
            <div class="card-body">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['error_message']); ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="modifier_archive.php?id=<?= $id_archive ?>" enctype="multipart/form-data">
                    <div class="row g-3">
                        <!-- Type d'archivage -->
                        <div class="col-md-6">
                            <label for="type_archivage" class="form-label">Type d'archivage *</label>
                            <select class="form-select" id="type_archivage" name="type_archivage" required>
                                <option value="">Sélectionner un type</option>
                                <option value="manuel" <?= $archive['type_archivage'] === 'manuel' ? 'selected' : '' ?>>Manuel</option>
                                <option value="automatique" <?= $archive['type_archivage'] === 'automatique' ? 'selected' : '' ?>>Automatique</option>
                                <option value="annuel" <?= $archive['type_archivage'] === 'annuel' ? 'selected' : '' ?>>Annuel</option>
                            </select>
                        </div>
                        
                        <!-- Numéro de correspondance -->
                        <div class="col-md-6">
                            <label for="num_correspondance" class="form-label">Numéro de correspondance *</label>
                            <input type="text" class="form-control" id="num_correspondance" name="num_correspondance" 
                                   value="<?= htmlspecialchars($archive['num_correspondance']) ?>" required>
                        </div>
                        
                        <!-- Fichier PDF actuel -->
                        <div class="col-12">
                            <label class="form-label">Fichier PDF actuel</label>
                            <?php if (!empty($archive['pdf_archive'])): ?>
                                <div class="mb-2">
                                    <a href="../../uploads/<?= htmlspecialchars($archive['pdf_archive']) ?>" 
                                       target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-file-pdf"></i> Voir le fichier actuel
                                    </a>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Aucun fichier actuellement</p>
                            <?php endif; ?>
                            
                            <label for="pdf_archive" class="form-label">Nouveau fichier PDF (laisser vide pour ne pas changer)</label>
                            <input type="file" class="form-control" id="pdf_archive" name="pdf_archive" accept=".pdf">
                            <div class="form-text">Format accepté : PDF (taille max : 5Mo)</div>
                        </div>
                        
                        <!-- Motif d'archivage -->
                        <div class="col-md-6">
                            <label for="motif_archivage" class="form-label">Motif d'archivage *</label>
                            <select class="form-select" id="motif_archivage" name="motif_archivage" required>
                                <option value="">Sélectionner un motif</option>
                                <option value="traitement_termine" <?= $archive['motif_archivage'] === 'traitement_termine' ? 'selected' : '' ?>>Traitement terminé</option>
                                <option value="delai_depasse" <?= $archive['motif_archivage'] === 'delai_depasse' ? 'selected' : '' ?>>Délai dépassé</option>
                                <option value="demande_specifique" <?= $archive['motif_archivage'] === 'demande_specifique' ? 'selected' : '' ?>>Demande spécifique</option>
                                <option value="archivage_annuel" <?= $archive['motif_archivage'] === 'archivage_annuel' ? 'selected' : '' ?>>Archivage annuel</option>
                            </select>
                        </div>
                        
                        <!-- Commentaire -->
                        <div class="col-md-6">
                            <label for="commentaire" class="form-label">Commentaire</label>
                            <textarea class="form-control" id="commentaire" name="commentaire" rows="1"><?= htmlspecialchars($archive['commentaire']) ?></textarea>
                        </div>
                        
                        <!-- Boutons -->
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                            <a href="liste_archive.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/plugins.js"></script>
    
    <script>
    $(document).ready(function() {
        // Validation du formulaire
        $('form').submit(function() {
            // Vérification des champs obligatoires
            if ($('#type_archivage').val() === '') {
                alert('Veuillez sélectionner un type d\'archivage');
                return false;
            }
            
            if ($('#num_correspondance').val() === '') {
                alert('Veuillez saisir un numéro de correspondance');
                return false;
            }
            
            if ($('#motif_archivage').val() === '') {
                alert('Veuillez sélectionner un motif d\'archivage');
                return false;
            }
            
            return true;
        });
    });
    </script>
</body>
</html>