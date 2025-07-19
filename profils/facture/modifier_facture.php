<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    $_SESSION['error_message'] = "Session expirée ou non authentifiée";
    header('Location: /courrier_coud/');
    exit();
}

require_once '../../traitement/fonction_facture.php';

// Initialisation des variables
$errors = [];
$formData = [];
$success = isset($_GET['success']);

// Vérification et récupération de la facture
try {
    if (!isset($_GET['id'])) {
        throw new Exception("ID de facture manquant");
    }

    $facture = getFactureById($_GET['id']);
    if (!$facture) {
        throw new Exception("Facture introuvable");
    }
    
    $formData = $facture;
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: liste_factures.php');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Nettoyage des données
        $formData = [
            'date_arrive' => trim($_POST['date_arrive'] ?? ''),
            'numero_courrier' => trim($_POST['numero_courrier'] ?? ''),
            'expediteur' => trim($_POST['expediteur'] ?? ''),
            'numero_facture' => trim($_POST['numero_facture'] ?? ''),
            'decade' => trim($_POST['decade'] ?? ''),
            'montant_ttc' => (float)($_POST['montant_ttc'] ?? 0),
            'type_facture' => $_POST['type_facture'] ?? '',
            'facture_pdf' => $facture['facture_pdf']
        ];

        // Validation
        $errors = validerFacture($formData);
        
        if (empty($errors)) {
            // Gestion du fichier PDF
            if (isset($_FILES['facture_pdf']) && $_FILES['facture_pdf']['error'] === UPLOAD_ERR_OK) {
                $upload = uploaderPDF($_FILES['facture_pdf']);
                if (isset($upload['error'])) {
                    throw new Exception($upload['error']);
                }
                
                // Suppression de l'ancien fichier
                if (!empty($formData['facture_pdf']) && file_exists($formData['facture_pdf'])) {
                    if (!unlink($formData['facture_pdf'])) {
                        throw new Exception("Échec de la suppression du PDF existant");
                    }
                }
                $formData['facture_pdf'] = $upload['success'];
            }
            
            // Mise à jour en base de données
            if (!modifierFacture($facture['id_facture'], $formData)) {
                throw new Exception("Échec de la mise à jour en base de données");
            }
            
            $_SESSION['success_message'] = "Facture #{$facture['id_facture']} mise à jour avec succès";
            $_SESSION['redirect_delay'] = 3; // 3 secondes
            $_SESSION['redirect_url'] = 'liste_factures.php?id='.$facture['id_facture'].'&success=1';
            header('Location: modifier_facture.php?id='.$facture['id_facture']);
            exit();
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
        error_log("Erreur modification facture - ".date('Y-m-d H:i:s')." - ".$e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: MODIFIER FACTURE #<?= htmlspecialchars($facture['id_facture']) ?></title>
    <link rel="shortcut icon" href="../../assets/img/log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/usersliste.css">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="css/form.css">
    <style>
        .form-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        .pdf-preview {
            max-height: 150px;
        }
    </style>
</head>
<body>
    <?php include('../../head.php'); ?>
    <div class="info-banner text-white d-flex justify-content-center align-items-center" style="height: 120px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Modifier Facture #<?= htmlspecialchars($facture['id_facture']) ?><br>
                <span>
                    (<?= htmlspecialchars($_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']) ?>)
                </span>
            </p>
        </div>
    </div>
    
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="form-container">
                    <!-- En-tête du formulaire -->
                    <div class="form-header d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 mb-0"><i class="bi bi-pencil-square"></i> Modifier Facture #<?= htmlspecialchars($facture['id_facture']) ?></h2>
                        <a href="liste_factures.php" class="btn btn-outline-secondary text-white btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Retour 
                        </a>
                    </div>
                    
                    <!-- Messages d'erreur -->
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($errors['general']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Formulaire -->
                    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate id="factureForm">
                        <div class="row g-3">
                            <!-- Numéro de Courrier -->
                            <div class="col-md-6">
                                <label for="numero_courrier" class="form-label required-field">N° Courrier</label>
                                <input type="text" class="form-control <?= isset($errors['numero_courrier']) ? 'is-invalid' : '' ?>" 
                                    id="numero_courrier" name="numero_courrier" 
                                    value="<?= htmlspecialchars($formData['numero_courrier']) ?>" 
                                    pattern="[A-Z]{2,4}-\d{4}-\d{3}" required>
                                <?php if (isset($errors['numero_courrier'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['numero_courrier']) ?></div>
                                <?php endif; ?>
                                <div class="form-text">Format: XXX-YYYY-NNN (ex: COUR-2025-001)</div>
                            </div>
                            
                            <!-- Date d'arrivée -->
                            <div class="col-md-6">
                                <label for="date_arrive" class="form-label required-field">Date d'arrivée</label>
                                <input type="date" class="form-control <?= isset($errors['date_arrive']) ? 'is-invalid' : '' ?>" 
                                    id="date_arrive" name="date_arrive" 
                                    value="<?= htmlspecialchars($formData['date_arrive']) ?>" required>
                                <?php if (isset($errors['date_arrive'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['date_arrive']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Expéditeur -->
                            <div class="col-md-6">
                                <label for="expediteur" class="form-label required-field">Expéditeur</label>
                                <input type="text" class="form-control <?= isset($errors['expediteur']) ? 'is-invalid' : '' ?>" 
                                    id="expediteur" name="expediteur" 
                                    value="<?= htmlspecialchars($formData['expediteur']) ?>" required>
                                <?php if (isset($errors['expediteur'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['expediteur']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Numéro de Facture -->
                            <div class="col-md-6">
                                <label for="numero_facture" class="form-label required-field">N° Facture</label>
                                <input type="text" class="form-control <?= isset($errors['numero_facture']) ? 'is-invalid' : '' ?>" 
                                    id="numero_facture" name="numero_facture" 
                                    value="<?= htmlspecialchars($formData['numero_facture']) ?>" 
                                    pattern="[A-Z]{2,4}-\d{4}-\d{3}" required>
                                <?php if (isset($errors['numero_facture'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['numero_facture']) ?></div>
                                <?php endif; ?>
                                <div class="form-text">Format: XXX-YYYY-NNN (ex: FACT-2025-001)</div>
                            </div>
                            
                            <!-- Décade -->
                            <div class="col-md-4">
                                <label for="decade" class="form-label">Période/Décade</label>
                                <input type="text" class="form-control <?= isset($errors['decade']) ? 'is-invalid' : '' ?>" 
                                    id="decade" name="decade" 
                                    value="<?= htmlspecialchars($formData['decade']) ?>">
                                <?php if (isset($errors['decade'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['decade']) ?></div>
                                <?php endif; ?>
                                <div class="form-text">Ex: Du 01-01-2025 au 31-01-2025</div>
                            </div>
                            
                            <!-- Montant TTC -->
                            <div class="col-md-4">
                                <label for="montant_ttc" class="form-label required-field">Montant TTC</label>
                                <div class="input-group">
                                    <input type="text" class="form-control <?= isset($errors['montant_ttc']) ? 'is-invalid' : '' ?>" 
                                        id="montant_ttc" name="montant_ttc" 
                                        value="<?= htmlspecialchars($formData['montant_ttc']) ?>" 
                                        pattern="^\d+(\.\d{1,2})?$" required>
                                    <span class="input-group-text">FCFA</span>
                                    <?php if (isset($errors['montant_ttc'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['montant_ttc']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-text">Format: 9999.99 (2 décimales max)</div>
                            </div>
                            
                            <!-- Type de Facture -->
                            <div class="col-md-4">
                                <label for="type_facture" class="form-label required-field">Type de facture</label>
                                <select class="form-select <?= isset($errors['type_facture']) ? 'is-invalid' : '' ?>" 
                                    id="type_facture" name="type_facture" required>
                                    <option value="">Sélectionnez...</option>
                                    <option value="Electricité" <?= $formData['type_facture'] === 'Electricité' ? 'selected' : '' ?>>Electricité</option>
                                    <option value="Eau" <?= $formData['type_facture'] === 'Eau' ? 'selected' : '' ?>>Eau</option>
                                    <option value="Téléphone" <?= $formData['type_facture'] === 'Téléphone' ? 'selected' : '' ?>>Téléphone</option>
                                    <option value="Internet" <?= $formData['type_facture'] === 'Internet' ? 'selected' : '' ?>>Internet</option>
                                    <option value="Fournitures" <?= $formData['type_facture'] === 'Fournitures' ? 'selected' : '' ?>>Fournitures</option>
                                    <option value="Restaurant" <?= $formData['type_facture'] === 'Restaurant' ? 'selected' : '' ?>>Restaurant</option>
                                </select>
                                <?php if (isset($errors['type_facture'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['type_facture']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Fichier PDF -->
                            <div class="col-12">
                                <label for="facture_pdf" class="form-label">Document PDF</label>
                                <input type="file" class="form-control <?= isset($errors['facture_pdf']) ? 'is-invalid' : '' ?>" 
                                    id="facture_pdf" name="facture_pdf" accept=".pdf">
                                <?php if (isset($errors['facture_pdf'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['facture_pdf']) ?></div>
                                <?php endif; ?>
                                <div class="form-text">Taille max: 5MB - Format PDF uniquement</div>
                                
                                <?php if (!empty($facture['facture_pdf']) && file_exists($facture['facture_pdf'])): ?>
                                <div class="mt-3">
                                    <p class="mb-1">Fichier actuel :</p>
                                    <a href="<?= htmlspecialchars($facture['facture_pdf']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Visualiser le PDF
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Boutons -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    <i class="bi bi-save me-2"></i>Enregistrer
                                </button>
                                <a href="liste_factures.php" class="btn btn-outline-secondary ms-2 px-4 py-2">
                                    <i class="bi bi-x-circle me-2"></i>Annuler
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-check-circle"></i> Confirmation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Veuillez vérifier les informations avant confirmation :</p>
                    <div id="confirmationDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Annuler
                    </button>
                    <button type="button" class="btn btn-success" id="confirmButton">
                        <i class="bi bi-check-circle"></i> Confirmer
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Scripts JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        // Gestion des messages de session
        <?php if (isset($_SESSION['success_message'])): ?>
            let seconds = <?= $_SESSION['redirect_delay'] ?? 3 ?>;
            const timerInterval = setInterval(() => {
                seconds--;
                if (seconds <= 0) {
                    clearInterval(timerInterval);
                    window.location.href = '<?= $_SESSION['redirect_url'] ?? "liste_factures.php" ?>';
                }
            }, 1000);

            Swal.fire({
                title: 'Succès',
                html: '<?= addslashes($_SESSION['success_message']) ?><br><br>Redirection dans <b>${seconds}</b> secondes...',
                icon: 'success',
                timer: <?= ($_SESSION['redirect_delay'] ?? 3) * 1000 ?>,
                timerProgressBar: true,
                showConfirmButton: false,
                willClose: () => {
                    window.location.href = '<?= $_SESSION['redirect_url'] ?? "liste_factures.php" ?>';
                }
            });
            <?php 
            unset($_SESSION['success_message']);
            unset($_SESSION['redirect_delay']);
            unset($_SESSION['redirect_url']);
            ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            Swal.fire({
                title: 'Erreur',
                text: '<?= addslashes($_SESSION['error_message']) ?>',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        // Validation du formulaire
        $('#factureForm').on('submit', function(e) {
            e.preventDefault();
            
            // Réinitialiser les validations
            $(this).find('.is-invalid').removeClass('is-invalid');
            
            // Validation simple côté client
            let isValid = true;
            
            if (!$('#date_arrive').val()) {
                $('#date_arrive').addClass('is-invalid');
                isValid = false;
            }
            
            if (!$('#numero_facture').val()) {
                $('#numero_facture').addClass('is-invalid');
                isValid = false;
            }
            
            if (!$('#type_facture').val()) {
                $('#type_facture').addClass('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                Swal.fire({
                    title: 'Champs manquants',
                    text: 'Veuillez remplir tous les champs obligatoires',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Préparer les détails de confirmation
            const details = `
                <ul class="list-group mb-3">
                    <li class="list-group-item"><strong>Date:</strong> ${$('#date_arrive').val()}</li>
                    <li class="list-group-item"><strong>N° Facture:</strong> ${$('#numero_facture').val()}</li>
                    <li class="list-group-item"><strong>Montant:</strong> ${$('#montant_ttc').val()} €</li>
                    <li class="list-group-item"><strong>Type:</strong> ${$('#type_facture option:selected').text()}</li>
                </ul>
                <p>Confirmez-vous ces modifications ?</p>
            `;
            
            $('#confirmationDetails').html(details);
            
            // Afficher le modal
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            confirmModal.show();
        });
        
        // Confirmation finale
        $('#confirmButton').on('click', function() {
            $('#factureForm').off('submit').submit();
        });
        
        // Affichage du PDF sélectionné
        $('#facture_pdf').on('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type === 'application/pdf') {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Vous pourriez afficher un aperçu ici si nécessaire
                };
                reader.readAsDataURL(file);
            }
        });
    });
    </script>
</body>
</html>