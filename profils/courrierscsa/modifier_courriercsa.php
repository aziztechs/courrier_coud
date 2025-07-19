<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courier_coud/');
    session_destroy();
    exit();
}

require_once('../../traitement/courriercsa_fonctions.php');
require_once('../../traitement/courriercsa/traitement_modif_courriercsa.php');
include('../../activite.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: MODIFIER COURRIER</title>
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="css/form.css" />
    <link rel="stylesheet" href="../../assets/css/usersliste.css" />
    <link rel="stylesheet" href="../courrier/css/form.css"/>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .form-container {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .form-header {
            border-bottom: 2px solid #0056b3;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        .file-upload-input {
            position: absolute;
            font-size: 100px;
            opacity: 0;
            right: 0;
            top: 0;
            cursor: pointer;
        }
        .file-upload-label {
            display: block;
            padding: 0.375rem 0.75rem;
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            cursor: pointer;
        }
        .success-modal-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        .current-file {
            margin-top: 5px;
            font-size: 0.9rem;
        }
        .current-file a {
            color: #0d6efd;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>
    
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 140px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Bienvenue !<br>
                <span>
                    (<?php echo $_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']; ?>)
                </span>
            </p>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="form-container">
            <div class="form-header">
                <h1 class="text-primary"><i class="fas fa-edit me-2"></i>MODIFIER COURRIER</h1>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show">
                    <?= $_SESSION['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            <?php endif; ?>

            <form id="courrierForm" method="post" enctype="multipart/form-data">
                
                <div class="row g-3">
                    <!-- Numéro du courrier (non modifiable) -->
                    <div class="col-md-6">
                        <label class="form-label">Numéro du courrier</label>
                        <input placeholder="N° Courrier" name="numero_courrier" value="<?= htmlspecialchars($courrier['Numero_Courrier']) ?>" 
                               class="form-control" id="numero_courrier" required>
                    </div>

                    <!-- Date -->
                    <div class="col-md-6">
                        <label for="date" class="form-label required-field">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required
                               value="<?= htmlspecialchars($courrier['date']) ?>">
                    </div>

                    <!-- Nature -->
                    <div class="col-md-6">
                        <label for="nature" class="form-label required-field">Nature</label>
                        <select class="form-select" id="nature" name="nature" required>
                            <option value="arrive" <?= $courrier['Nature'] == 'arrive' ? 'selected' : '' ?>>Arrivé</option>
                            <option value="depart" <?= $courrier['Nature'] == 'depart' ? 'selected' : '' ?>>Départ</option>
                        </select>
                    </div>

                    <!-- Type -->
                    <div class="col-md-6">
                        <label for="type" class="form-label required-field">Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="interne" <?= $courrier['Type'] == 'interne' ? 'selected' : '' ?>>Interne</option>
                            <option value="externe" <?= $courrier['Type'] == 'externe' ? 'selected' : '' ?>>Externe</option>
                        </select>
                    </div>

                    <!-- Expéditeur -->
                    <div class="col-6">
                        <label for="expediteur" class="form-label required-field">Expéditeur / Destinataire</label>
                        <input type="text" class="form-control" id="expediteur" name="expediteur" required
                               value="<?= htmlspecialchars($courrier['Expediteur']) ?>">
                    </div>

                    <!-- Objet -->
                    <div class="col-6">
                        <label for="objet" class="form-label required-field">Objet</label>
                        <textarea class="form-control" id="objet" name="objet" rows="3" required><?= htmlspecialchars($courrier['Objet']) ?></textarea>
                    </div>

                    <!-- Fichier PDF -->
                    <div class="col-12">
                        <label for="pdf" class="form-label">Fichier PDF</label>
                        <div class="file-upload">
                            <label class="file-upload-label" id="file-label">
                                <i class="fas fa-cloud-upload-alt me-2"></i>
                                <span id="file-text">Choisir un nouveau fichier</span>
                            </label>
                            <input type="file" class="file-upload-input" id="pdf" name="pdf" accept=".pdf">
                        </div>
                        <small class="text-muted">Format PDF uniquement (max 5MB)</small>
                        
                        <?php if (!empty($courrier['pdf'])): ?>
                            <div class="current-file mt-2">
                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                Fichier actuel: 
                                <a href="<?= $courrier['pdf'] ?>" target="_blank">
                                    <?= basename($courrier['pdf']) ?>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="removeFile">
                                    <i class="fas fa-trash-alt"></i> Supprimer
                                </button>
                                <input type="hidden" name="current_pdf" value="<?= $courrier['pdf'] ?>">
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Boutons -->
                    <div class="col-12 mt-4">
                        <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#confirmModal">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                        <a href="liste_courrierscsa.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmation -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmer la modification</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Voulez-vous vraiment modifier ce courrier ?</p>
                    <div class="alert alert-info">
                        <strong>Numéro :</strong> <?= htmlspecialchars($courrier['Numero_Courrier']) ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="confirmSave">
                        <i class="fas fa-check me-2"></i>Confirmer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de succès -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <div class="success-modal-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="text-success mb-3">Modification réussie !</h3>
                    <p>Le courrier a été modifié avec succès.</p>
                    <p class="fw-bold">Numéro: <?= $_SESSION['courrier_modifie'] ?? htmlspecialchars($courrier['Numero_Courrier']) ?></p>
                    <div class="mt-4">
                        <a href="liste_courrierscsa.php" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i> Retour à la liste
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Gestion de l'affichage du nom du fichier
        document.getElementById('pdf').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Choisir un nouveau fichier';
            document.getElementById('file-text').textContent = fileName;
        });

        // Initialisation de flatpickr pour la date
        flatpickr("#date", {
            dateFormat: "Y-m-d",
            defaultDate: "<?= $courrier['date'] ?>",
            maxDate: "today"
        });

        // Gestion de la confirmation
        document.getElementById('confirmSave').addEventListener('click', function() {
            document.getElementById('courrierForm').submit();
        });

        // Gestion de la suppression du fichier actuel
        document.getElementById('removeFile')?.addEventListener('click', function() {
            if (confirm('Voulez-vous vraiment supprimer le fichier PDF actuel ?')) {
                // Créer un champ caché pour indiquer la suppression
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'remove_pdf';
                input.value = '1';
                document.getElementById('courrierForm').appendChild(input);
                
                // Masquer l'affichage du fichier actuel
                this.parentElement.style.display = 'none';
            }
        });

        // Afficher le modal de succès si le paramètre est présent
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            }
            
            // Fermer les alertes automatiquement après 5 secondes
            const alert = document.querySelector('.alert');
            if (alert) {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            }
        });
    </script>
</body>
</html>