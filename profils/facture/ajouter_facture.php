<?php
session_start();

// Vérification de l'authentification
if (empty($_SESSION['username'])) {
    header('Location: /courrier_coud/');
    exit();
}

require_once __DIR__ . '/../../traitement/fonction_facture.php';

// Initialisation des variables
$errors = [];
$formData = [
    'date_arrive' => date('Y-m-d'),
    'numero_courrier' => '',
    'expediteur' => '',
    'numero_facture' => '',
    'decade' => '',
    'montant_ttc' => '',
    'type_facture' => '',
    'facture_pdf' => ''
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $formData = [
        'date_arrive' => trim($_POST['date_arrive'] ?? ''),
        'numero_courrier' => trim($_POST['numero_courrier'] ?? ''),
        'expediteur' => trim($_POST['expediteur'] ?? ''),
        'numero_facture' => trim($_POST['numero_facture'] ?? ''),
        'decade' => trim($_POST['decade'] ?? ''),
        'montant_ttc' => str_replace(',', '.', trim($_POST['montant_ttc'] ?? '')),
        'type_facture' => trim($_POST['type_facture'] ?? ''),
        'facture_pdf' => ''
    ];

    // Validation des données
    if (empty($formData['date_arrive'])) {
        $errors['date_arrive'] = 'La date est obligatoire';
    }

    if (empty($formData['numero_courrier'])) {
        $errors['numero_courrier'] = 'Le numéro de courrier est obligatoire';
    } elseif (!preg_match('/^[A-Z]{2,4}-\d{4}-\d{3}$/', $formData['numero_courrier'])) {
        $errors['numero_courrier'] = 'Format invalide (ex: COUR-2025-001)';
    }

    if (empty($formData['expediteur'])) {
        $errors['expediteur'] = "L'expéditeur est obligatoire";
    }

    if (empty($formData['numero_facture'])) {
        $errors['numero_facture'] = 'Le numéro de facture est obligatoire';
    } elseif (!preg_match('/^[A-Z]{2,4}-\d{4}-\d{3}$/', $formData['numero_facture'])) {
        $errors['numero_facture'] = 'Format invalide (ex: FACT-2025-001)';
    }

    if (empty($formData['montant_ttc']) || !is_numeric($formData['montant_ttc'])) {
        $errors['montant_ttc'] = 'Montant invalide';
    }

    if (empty($formData['type_facture'])) {
        $errors['type_facture'] = 'Le type de facture est obligatoire';
    }

    // Gestion du fichier PDF
    if (empty($errors) && isset($_FILES['facture_pdf']) && $_FILES['facture_pdf']['error'] === UPLOAD_ERR_OK) {
        $upload = uploaderPDF($_FILES['facture_pdf']);
        if (isset($upload['error'])) {
            $errors['facture_pdf'] = $upload['error'];
        } else {
            $formData['facture_pdf'] = basename($upload['success']);
        }
    }

    // Si aucune erreur, procéder à l'insertion
    if (empty($errors)) {
        $id = ajouterFacture($formData);
        if ($id) {
            $_SESSION['success_message'] = [
                'title' => 'Succès!',
                'text' => 'La facture #' . $id . ' a été ajoutée',
                'timer' => 3000,
                'redirect' => 'liste_factures.php'
            ];
            header('Location: ajouter_facture.php?success=1');
            exit();
        } else {
            $errors['general'] = "Une erreur est survenue lors de l'ajout";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: AJOUTER FACTURE</title>
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
    </style>
</head>
<body>
    <?php include('../../head.php'); ?>
    <div class="info-banner text-white d-flex justify-content-center align-items-center" style="height: 120px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Ajouter une Facture !<br>
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
                        <h2 class="h4 mb-0"><i class="fas fa-plus"></i> AJOUTER UNE FACTURE</h2>
                        <a href="liste_factures.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Retour à la liste
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
                                <label for="facture_pdf" class="form-label required-field">Document PDF</label>
                                <input type="file" class="form-control <?= isset($errors['facture_pdf']) ? 'is-invalid' : '' ?>" 
                                    id="facture_pdf" name="facture_pdf" accept=".pdf" required>
                                <?php if (isset($errors['facture_pdf'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['facture_pdf']) ?></div>
                                <?php endif; ?>
                                <div class="form-text">Taille max: 5MB - Format PDF uniquement</div>
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

    <!-- Modal de Confirmation -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmer l'ajout</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Veuillez vérifier les informations avant confirmation :</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Annuler
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmActionBtn">
                        <i class="bi bi-check-circle me-1"></i> Confirmer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            // Interception de la soumission du formulaire
            $('#factureForm').on('submit', function(e) {
                e.preventDefault();
                
                // Validation du formulaire
                if (!this.checkValidity()) {
                    e.stopPropagation();
                    $(this).addClass('was-validated');
                    return;
                }

                // Remplir les données de confirmation
                $('#confirm-numero').text($('#numero_courrier').val());
                $('#confirm-expediteur').text($('#expediteur').val());
                
                // Afficher la modal
                const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
                confirmModal.show();
            });

            // Gestion du bouton de confirmation
            $('#confirmActionBtn').on('click', function() {
                // Envoyer le formulaire après confirmation
                $('#factureForm').off('submit').submit();
            });

            // Affichage du message de succès
            <?php if (isset($_GET['success'])): ?>
                Swal.fire({
                    title: '<?= $_SESSION['success_message']['title'] ?>',
                    text: '<?= $_SESSION['success_message']['text'] ?>',
                    icon: 'success',
                    timer: <?= $_SESSION['success_message']['timer'] ?>,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    willClose: () => {
                        window.location.href = '<?= $_SESSION['success_message']['redirect'] ?>';
                    }
                });
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            // Validation en temps réel
            document.querySelectorAll('input[pattern]').forEach(input => {
                input.addEventListener('input', function() {
                    const pattern = new RegExp(this.pattern);
                    if (this.value && !pattern.test(this.value)) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });
            });
        });
    </script>
</body>
</html>