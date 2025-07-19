<?php
session_start();
if (empty($_SESSION['username'])) {
    header('Location: /courrier_coud/');
    exit();
}

require '../../traitement/fonction_user.php';
require '../../traitement/connect.php';
require '../../traitement/users/traitement_editUser.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD - Modification Utilisateur</title>
    <link rel="icon" href="../../assets/img/log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../courrier/css/form.css"/>
    <link rel="stylesheet" href="../../assets/css/usersliste.css"/>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    
    <style>
        .full-width-header {
            width: 100vw;
            position: relative;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
            background-color: #0056b3;
        }
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        .form-section-title {
            color: #0056b3;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .password-toggle {
            cursor: pointer;
            height: 30px;
        }
    </style>
</head>

<body>
    <?php include '../../head.php'; ?>

    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 140px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Gestion des Utilisateurs !<br>
                <span>
                    (<?= htmlspecialchars($_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']) ?>)
                </span>
            </p>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="form-container">
                    <div class="form-header d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 mb-0"><i class="fas fa-user-edit me-2"></i>Modifier Utilisateur</h2>
                        <a href="users.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Retour
                        </a>
                    </div>
                    
                    <?php if (isset($_SESSION['form_errors'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <ul class="mb-0">
                                <?php foreach ($_SESSION['form_errors'] as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['form_errors']); ?>
                    <?php endif; ?>
                    
                    <form id="editForm" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="confirmed" id="confirmed" value="0">
                       
                        <!-- Section Informations personnelles -->
                        <h5 class="form-section-title"><i class="fas fa-user me-2"></i>Informations personnelles</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="Nom" class="form-label required-field">Nom</label>
                                <input type="text" name="Nom" id="Nom" class="form-control" 
                                    value="<?= htmlspecialchars($user['Nom']) ?>" 
                                    required>
                                <div class="invalid-feedback">
                                    Veuillez saisir un nom valide.
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="Prenom" class="form-label required-field">Prénom</label>
                                <input type="text" name="Prenom" id="Prenom" class="form-control" 
                                    value="<?= htmlspecialchars($user['Prenom']) ?>"
                                    required>
                                <div class="invalid-feedback">
                                    Veuillez saisir un prénom valide.
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label required-field">Email</label>
                                <input type="email" name="email" id="email" class="form-control" 
                                    value="<?= htmlspecialchars($user['email']) ?>"
                                    required>
                                <div class="invalid-feedback">
                                    Veuillez saisir un email valide.
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="Tel" class="form-label">Téléphone</label>
                                <input type="tel" name="Tel" id="Tel" class="form-control" 
                                    value="<?= htmlspecialchars($user['Tel']) ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="Matricule" class="form-label">Matricule</label>
                                <input type="text" name="Matricule" id="Matricule" class="form-control" 
                                    value="<?= htmlspecialchars($user['Matricule']) ?>">
                            </div>
                        </div>

                        <!-- Section Compte -->
                        <h5 class="form-section-title"><i class="fas fa-key me-2"></i>Informations de compte</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="Username" class="form-label required-field">Nom d'utilisateur</label>
                                <input type="text" name="Username" id="Username" class="form-control" 
                                    value="<?= htmlspecialchars($user['Username']) ?>"
                                    required>
                                <div class="invalid-feedback">
                                    Veuillez saisir un nom d'utilisateur.
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="Fonction" class="form-label required-field">Fonction</label>
                                <select class="form-select" id="Fonction" name="Fonction" required>
                                    <option value="" disabled>Sélectionnez une fonction</option>
                                    <option value="chef_courrier" <?= $user['Fonction'] === 'chef_courrier' ? 'selected' : '' ?>>Chef de service</option>
                                    <option value="assistant_courrier" <?= $user['Fonction'] === 'assistant_courrier' ? 'selected' : '' ?>>Assistant</option>
                                    <option value="secretaiat_csa" <?= $user['Fonction'] === 'secretaiat_csa' ? 'selected' : '' ?>>Assistant CSA</option>
                                    <option value="secretariat_service_social" <?= $user['Fonction'] === 'secretariat_service_social' ? 'selected' : '' ?>>Assistant SC</option>
                                    <option value="directeur" <?= $user['Fonction'] === 'directeur' ? 'selected' : '' ?>>Directeur</option>
                                    <option value="superadmin" <?= $user['Fonction'] === 'superadmin' ? 'selected' : '' ?>>Administrateur système</option>
                                </select>
                                <div class="invalid-feedback">
                                    Sélectionnez une fonction.
                                </div>
                            </div>
                            
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="Actif" id="Actif" <?= $user['Actif'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="Actif">
                                        Compte actif
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Boutons -->
                        <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                            <a href="users.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Annuler
                            </a>
                            <button type="button" id="submitBtn" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirmation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir modifier cet utilisateur ?</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Les modifications seront appliquées immédiatement.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Annuler
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmSubmitBtn">
                        <i class="fas fa-check me-1"></i> Confirmer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('editForm');
        const confirmModal = new bootstrap.Modal('#confirmationModal');
        
        // Validation du formulaire
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
        
        // Confirmation avant soumission
        document.getElementById('submitBtn').addEventListener('click', function() {
            if (form.checkValidity()) {
                confirmModal.show();
            } else {
                form.classList.add('was-validated');
                // Scroll vers le premier champ invalide
                const invalidField = form.querySelector(':invalid');
                if (invalidField) {
                    invalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    invalidField.focus();
                }
            }
        });
        
        // Soumission du formulaire
        document.getElementById('confirmSubmitBtn').addEventListener('click', function() {
            const btn = this;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Enregistrement...';
            btn.disabled = true;
            
            document.getElementById('confirmed').value = '1';
            form.submit();
        });
        
        // Affichage des messages de session
        <?php if (isset($_SESSION['swal_data'])): ?>
            Swal.fire({
                title: '<?= addslashes($_SESSION['swal_data']['title']) ?>',
                text: '<?= addslashes($_SESSION['swal_data']['text']) ?>',
                icon: '<?= $_SESSION['swal_data']['icon'] ?>',
                timer: <?= $_SESSION['swal_data']['timer'] ?? 3000 ?>,
                timerProgressBar: true,
                showConfirmButton: <?= isset($_SESSION['swal_data']['showConfirmButton']) ? 'true' : 'false' ?>,
                willClose: () => {
                    <?php if (isset($_SESSION['swal_data']['redirect'])): ?>
                        window.location.href = '<?= $_SESSION['swal_data']['redirect'] ?>';
                    <?php endif; ?>
                }
            });
            <?php unset($_SESSION['swal_data']); ?>
        <?php endif; ?>
    });
    </script>
</body>
</html>