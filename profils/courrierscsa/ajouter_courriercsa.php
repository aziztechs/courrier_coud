<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courier_coud/');
    session_destroy();
    exit();
}

require_once('../../traitement/courriercsa_fonctions.php');
require_once('../../traitement/courriercsa/traitement_ajout_courriercsa.php');
include('../../activite.php');

// Récupération des valeurs soumises en cas d'erreur pour pré-remplissage
$formData = [
    'numero_courrier' => $_POST['numero_courrier'] ?? '',
    'date' => $_POST['date'] ?? date('Y-m-d'),
    'nature' => $_POST['nature'] ?? '',
    'type' => $_POST['type'] ?? '',
    'expediteur' => $_POST['expediteur'] ?? '',
    'objet' => $_POST['objet'] ?? ''
];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: AJOUTER COURRIER</title>
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
</head>

<body>
    <?php include('../../headcsa.php'); ?>
    
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 140px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Ajouter Courrier !<br>
                <span>
                    (<?php echo $_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']; ?>)
                </span>
            </p>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="form-container">
            <div class="form-header d-flex justify-content-between align-items-center">
                <h3 class="text-primary mt-2"><i class="fas fa-plus-circle m-2"></i>NOUVEAU COURRIER</h3>
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
                    <!-- Numéro généré automatiquement -->
                    <div class="col-md-6">
                        <label class="form-label">Numéro du courrier</label>
                            <input type="text" class="form-control" id="numero_courrier" name="numero_courrier"
                                   placeholder="N° Courrier" required>
                            <small class="text-muted d-block">Format: COUR-AAAA-NNN</small>
                    </div>

                    <!-- Date -->
                    <div class="col-md-6">
                        <label for="date" class="form-label required-field">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required
                               value="<?= date('Y-m-d') ?>">
                    </div>

                    <!-- Nature -->
                    <div class="col-md-6">
                        <label for="nature" class="form-label required-field">Nature</label>
                        <select class="form-select" id="nature" name="nature" required>
                            <option value="">Sélectionner...</option>
                            <option value="arrive">Arrivé</option>
                            <option value="depart">Départ</option>
                        </select>
                    </div>

                    <!-- Type -->
                    <div class="col-md-6">
                        <label for="type" class="form-label required-field">Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Sélectionner...</option>
                            <option value="interne">Interne</option>
                            <option value="externe">Externe</option>
                        </select>
                    </div>

                    <!-- Expéditeur -->
                    <div class="col-6">
                        <label for="expediteur" class="form-label required-field">Expéditeur / Destinataire</label>
                        <input type="text" class="form-control" id="expediteur" name="expediteur" required>
                    </div>

                    <!-- Objet -->
                    <div class="col-6">
                        <label for="objet" class="form-label required-field">Objet</label>
                        <textarea class="form-control" id="objet" name="objet" rows="3" required></textarea>
                    </div>

                    <!-- Fichier PDF -->
                    <div class="col-12">
                        <label for="pdf" class="form-label">Fichier PDF</label>
                        <div class="file-upload">
                            <label class="file-upload-label" id="file-label">
                                <i class="fas fa-cloud-upload-alt me-2"></i>
                                <span id="file-text">Choisir un fichier</span>
                            </label>
                            <input type="file" class="file-upload-input" id="pdf" name="pdf" accept=".pdf">
                        </div>
                        <small class="text-muted">Format PDF uniquement (max 5MB)</small>
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
                    <h5 class="modal-title" id="confirmModalLabel">Confirmer l'enregistrement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Voulez-vous vraiment enregistrer ce nouveau courrier ?</p>
                    <div class="alert alert-info">
                        <strong>Numéro attribué :</strong> 
                        <span id="numeroPreview">COUR-<?= date('Y') ?>-NNN</span>
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
                    <h3 class="text-success mb-3">Enregistrement réussi !</h3>
                    <p>Le courrier a été enregistré avec succès.</p>
                    <p class="fw-bold">Numéro: <?= $_SESSION['nouveau_courrier'] ?? '' ?></p>
                    <div class="mt-4">
                        <button type="button" class="btn btn-success me-3" data-bs-dismiss="modal">
                            <i class="fas fa-plus me-1"></i> Ajouter un autre
                        </button>
                        <a href="liste_courrierscsa.php" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i> Voir la liste
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
            const fileName = e.target.files[0]?.name || 'Aucun fichier sélectionné';
            document.getElementById('file-text').textContent = fileName;
        });

        // Initialisation de flatpickr pour la date
        flatpickr("#date", {
            dateFormat: "Y-m-d",
            defaultDate: "today",
            maxDate: "today"
        });

        // Gestion de la confirmation
        document.getElementById('confirmSave').addEventListener('click', function() {
            document.getElementById('courrierForm').submit();
        });

        // Prévisualisation du numéro dans le modal
        document.getElementById('nature').addEventListener('change', updateNumeroPreview);
        document.getElementById('type').addEventListener('change', updateNumeroPreview);

        function updateNumeroPreview() {
            const nature = document.getElementById('nature').value;
            const type = document.getElementById('type').value;
            
            if (nature && type) {
                document.getElementById('numeroPreview').textContent = 
                    `COUR-<?= date('Y') ?>-NNN (${nature === 'arrive' ? 'Arrivé' : 'Départ'} ${type === 'interne' ? 'Interne' : 'Externe'})`;
            }
        }

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