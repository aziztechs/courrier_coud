<?php
session_start();
if (empty($_SESSION['username'])) {
    header('Location: /courrier_coud/');
    exit();
}

require_once('../../traitement/fonction_archive.php');
require_once('../../traitement/fonction.php');
require_once('../../traitement/archive/traitement_modifier_archive.php');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
   <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil Direction - Gestion Courrier</title>
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/usersliste.css">
    <link rel="stylesheet" href="../courrier/css/form.css" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        .file-upload-container {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .file-upload-container:hover {
            border-color: #0056b3;
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        .form-container {
            max-width: 900px;
            margin: 2rem auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .form-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .btn-submit {
            background-color: #0056b3;
        }
        .current-file {
            font-weight: bold;
            color: #0056b3;
        }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 140px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Gestion des Archives !<br>
                <span>
                    (<?= htmlspecialchars($_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']) ?>)
                </span>
            </p>
        </div>
    </div>
    
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="h4 mb-0"><i class="bi bi-archive me-2"></i>Modifier Archive #<?= $id_archive ?></h2>
                    </div>
                    
                    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate id="archiveForm">
                        <input type="hidden" name="id_archive" value="<?= $id_archive ?>">
                        
                        <div class="row g-3 mb-4">
                            <!-- Type d'archivage -->
                            <div class="col-md-6">
                                <label for="type_archivage" class="form-label required-field">Type d'archivage</label>
                               <select class="form-select" id="type_archivage" name="type_archivage" required>
                                    <option value="" disabled>Sélectionner...</option>
                                    <?php foreach ($enumValues as $value): 
                                        $label = ucfirst(str_replace('_', ' ', $value));
                                    ?>
                                        <option value="<?= htmlspecialchars($value) ?>" 
                                                <?= $archive['type_archivage'] === $value ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Sélectionnez un type d'archivage.
                                </div>
                            </div>
                            
                            <!-- Numéro de correspondance -->
                            <div class="col-md-6">
                                <label for="num_correspondance" class="form-label required-field">N° Correspondance</label>
                                <input type="text" class="form-control" id="num_correspondance" 
                                       name="num_correspondance" value="<?= htmlspecialchars($archive['num_correspondance']) ?>" 
                                       list="correspondanceList" required
                                       placeholder="Ex: ARCH-2023-001">
                                <datalist id="correspondanceList">
                                    <?php foreach ($correspondances as $corresp): ?>
                                        <option value="<?= htmlspecialchars($corresp) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                                <div class="invalid-feedback">
                                    Saisissez un numéro valide.
                                </div>
                            </div>
                            
                            <!-- Fichier PDF -->
                            <div class="col-12">
                                <label class="form-label">Document PDF</label>
                                <div class="file-upload-container" onclick="document.getElementById('pdf_archive').click()">
                                    <i class="bi bi-file-earmark-pdf fs-1 text-danger mb-2"></i>
                                    <p class="mb-3" id="fileLabel">
                                        <?php if (!empty($archive['pdf_archive'])): ?>
                                            <span class="current-file">Fichier actuel:</span> <?= basename($archive['pdf_archive']) ?>
                                        <?php else: ?>
                                            Glissez-déposez ou cliquez pour sélectionner
                                        <?php endif; ?>
                                    </p>
                                    <input type="file" class="d-none" id="pdf_archive" 
                                           name="pdf_archive" accept=".pdf">
                                    <small class="text-muted">Taille max: 5MB - Format: PDF uniquement (Laissez vide pour conserver le fichier actuel)</small>
                                </div>
                            </div>
                            
                            <!-- Commentaire -->
                            <div class="col-12">
                                <label for="commentaire" class="form-label">Commentaire</label>
                                <textarea class="form-control" id="commentaire" name="commentaire" 
                                          rows="4" placeholder="Description ou notes supplémentaires..."><?= htmlspecialchars($archive['commentaire']) ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Boutons -->
                        <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                            <a href="liste_archive.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Annuler
                            </a>
                            <button type="button" class="btn btn-submit text-white" id="submitBtn">
                                <i class="bi bi-save me-1"></i> Enregistrer
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
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Voulez-vous vraiment enregistrer ces modifications ?</p>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Cette action mettra à jour définitivement l'archive.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Annuler
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmSubmitBtn">
                        <i class="bi bi-check-circle me-1"></i> Confirmer
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
        const form = document.getElementById('archiveForm');
        const fileInput = document.getElementById('pdf_archive');
        const fileLabel = document.getElementById('fileLabel');
        const submitBtn = document.getElementById('submitBtn');
        const confirmModal = new bootstrap.Modal('#confirmationModal');
        
        // Validation du formulaire
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
        
        // Gestion du fichier
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileLabel.innerHTML = `<span class="current-file">Nouveau fichier:</span> ${this.files[0].name}`;
            } else if (<?= !empty($archive['pdf_archive']) ? 'true' : 'false' ?>) {
                fileLabel.innerHTML = `<span class="current-file">Fichier actuel:</span> <?= !empty($archive['pdf_archive']) ? basename($archive['pdf_archive']) : '' ?>`;
            } else {
                fileLabel.textContent = 'Glissez-déposez ou cliquez pour sélectionner';
            }
        });
        
        // Drag and drop
        const uploadContainer = document.querySelector('.file-upload-container');
        
        ['dragover', 'dragenter'].forEach(event => {
            uploadContainer.addEventListener(event, (e) => {
                e.preventDefault();
                uploadContainer.style.borderColor = '#0056b3';
                uploadContainer.style.backgroundColor = '#f0f7ff';
            });
        });
        
        ['dragleave', 'dragend'].forEach(event => {
            uploadContainer.addEventListener(event, () => {
                uploadContainer.style.borderColor = '#dee2e6';
                uploadContainer.style.backgroundColor = '';
            });
        });
        
        uploadContainer.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadContainer.style.borderColor = '#dee2e6';
            uploadContainer.style.backgroundColor = '';
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                fileLabel.innerHTML = `<span class="current-file">Nouveau fichier:</span> ${e.dataTransfer.files[0].name}`;
            }
        });
        
        // Confirmation avant soumission
        submitBtn.addEventListener('click', function() {
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
        
        // Soumission AJAX
        document.getElementById('confirmSubmitBtn').addEventListener('click', function() {
            const btn = this;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Enregistrement...';
            btn.disabled = true;
            
            const formData = new FormData(form);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    confirmModal.hide();
                    Swal.fire({
                        title: 'Succès!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = data.redirect || 'liste_archive.php';
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Confirmer';
                btn.disabled = false;
                Swal.fire({
                    title: 'Erreur!',
                    text: error.message,
                    icon: 'error'
                });
            });
        });
        
        // Affichage des messages d'erreur de session
        <?php if (isset($_SESSION['error_message'])): ?>
            Swal.fire({
                title: 'Erreur!',
                text: '<?= addslashes($_SESSION['error_message']) ?>',
                icon: 'error'
            });
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    });
    </script>
</body>
</html>