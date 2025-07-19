<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

require_once '../../traitement/suivi_courriercsa_fonctions.php';

// Initialisation de la connexion
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données: " . $mysqli->connect_error);
}

// Récupération de l'ID du suivi à modifier
$id_suivi = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_suivi) {
    $_SESSION['error_message'] = "ID de suivi invalide";
    header('Location: liste_suivi_courrierscsa.php');
    exit();
}

// Récupération des données actuelles
$result = getSuivi($mysqli, $id_suivi);
if (!$result['success']) {
    $_SESSION['error_message'] = $result['error'];
    header('Location: liste_suivi_courrierscsa.php');
    exit();
}

$suivi = $result['data'];
$errors = [];

// Convertir les statuts séparés par des virgules en tableaux
$statuts_actuels = [
    'statut_1' => !empty($suivi['statut_1']) ? explode(', ', $suivi['statut_1']) : [],
    'statut_2' => !empty($suivi['statut_2']) ? explode(', ', $suivi['statut_2']) : [],
    'statut_3' => !empty($suivi['statut_3']) ? explode(', ', $suivi['statut_3']) : []
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $data = [
        'id_courrier' => filter_input(INPUT_POST, 'id_courrier', FILTER_SANITIZE_NUMBER_INT),
        'destinataire' => trim(filter_input(INPUT_POST, 'destinataire', FILTER_SANITIZE_STRING)),
        'statut_1' => isset($_POST['statut_1']) ? $_POST['statut_1'] : [],
        'statut_2' => isset($_POST['statut_2']) ? $_POST['statut_2'] : [],
        'statut_3' => isset($_POST['statut_3']) ? $_POST['statut_3'] : [],
        'autre_statut_1' => trim(filter_input(INPUT_POST, 'autre_statut_1', FILTER_SANITIZE_STRING)),
        'autre_statut_2' => trim(filter_input(INPUT_POST, 'autre_statut_2', FILTER_SANITIZE_STRING)),
        'autre_statut_3' => trim(filter_input(INPUT_POST, 'autre_statut_3', FILTER_SANITIZE_STRING))
    ];

    // Nettoyer les tableaux de statuts
    $data['statut_1'] = array_filter(array_map('trim', $data['statut_1']));
    $data['statut_2'] = array_filter(array_map('trim', $data['statut_2']));
    $data['statut_3'] = array_filter(array_map('trim', $data['statut_3']));

    // Ajouter le statut personnalisé s'il est renseigné
    if (!empty($data['autre_statut_1'])) {
        $data['statut_1'][] = $data['autre_statut_1'];
    }
    if (!empty($data['autre_statut_2'])) {
        $data['statut_2'][] = $data['autre_statut_2'];
    }
    if (!empty($data['autre_statut_3'])) {
        $data['statut_3'][] = $data['autre_statut_3'];
    }

    // Préparer les données pour la base de données
    $dataForDb = [
        'id_courrier' => $data['id_courrier'],
        'destinataire' => $data['destinataire'],
        'statut_1' => !empty($data['statut_1']) ? implode(', ', $data['statut_1']) : '',
        'statut_2' => !empty($data['statut_2']) ? implode(', ', $data['statut_2']) : '',
        'statut_3' => !empty($data['statut_3']) ? implode(', ', $data['statut_3']) : ''
    ];

    // Validation des données
    if (empty($dataForDb['id_courrier'])) {
        $errors['id_courrier'] = "Veuillez sélectionner un courrier";
    }

    if (empty($dataForDb['destinataire'])) {
        $errors['destinataire'] = "Le destinataire est obligatoire";
    } elseif (strlen($dataForDb['destinataire']) > 100) {
        $errors['destinataire'] = "Le destinataire ne doit pas dépasser 100 caractères";
    }

    // Si pas d'erreurs, mise à jour
    if (empty($errors)) {
        $result = modifierSuivi($mysqli, $id_suivi, $dataForDb);
        
        if ($result['success']) {
            $_SESSION['show_success_modal'] = true;
            $_SESSION['success_message'] = "Le suivi a été modifié avec succès.";
            header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id_suivi);
            exit();
        } else {
            $errors['general'] = $result['error'] ?? "Une erreur est survenue lors de la modification";
        }
    }
} else {
    // Pré-remplir avec les données actuelles si pas de POST
    $data = [
        'id_courrier' => $suivi['id_courrier'],
        'destinataire' => $suivi['destinataire'],
        'statut_1' => $statuts_actuels['statut_1'],
        'statut_2' => $statuts_actuels['statut_2'],
        'statut_3' => $statuts_actuels['statut_3'],
        'autre_statut_1' => '',
        'autre_statut_2' => '',
        'autre_statut_3' => ''
    ];
}

// Récupérer les données nécessaires
$courriers = getCourriersForSelect($mysqli);
$statuts = getStatutsPossibles();

// Fermer la connexion
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un suivi de courrier</title>
    <link rel="stylesheet" href="../../assets/css/usersliste.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }
        .form-control, .form-select {
            border-radius: 5px;
            padding: 10px;
        }
        .btn {
            border-radius: 5px;
            padding: 10px 20px;
        }
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875em;
        }
        .info-courrier {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .statut-container {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 5px;
            max-height: 200px;
            overflow-y: auto;
            padding: 5px;
        }
        .statut-container .form-check {
            padding: 5px 10px;
            margin: 2px 0;
            border-radius: 3px;
        }
        .statut-container .form-check:hover {
            background-color: #e9ecef;
        }
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .autre-statut {
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php include '../../headcsa.php'; ?>
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 140px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Gestion des Suivis !<br>
                <span>
                    (<?= htmlspecialchars($_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']) ?>)
                </span>
            </p>
        </div>
    </div> 

    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0"><i class="fas fa-edit me-2"></i>Modifier le suivi de courrier</h2>
                    </div>
                    <div class="card-body">
                        <!-- Informations sur le courrier -->
                        <div class="info-courrier">
                            <h5>Informations du courrier</h5>
                            <p class="mb-1"><strong>N°:</strong> <?= htmlspecialchars($suivi['Numero_Courrier']) ?></p>
                            <p class="mb-1"><strong>Date:</strong> <?= date('d/m/Y', strtotime($suivi['date'])) ?></p>
                            <p class="mb-1"><strong>Objet:</strong> <?= htmlspecialchars($suivi['Objet']) ?></p>
                            <p class="mb-0"><strong>Expéditeur:</strong> <?= htmlspecialchars($suivi['Expediteur']) ?></p>
                        </div>

                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= htmlspecialchars($errors['general']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form id="modificationForm" method="POST" novalidate>
                            <div class="row g-3">
                                <!-- Champ Courrier (caché car non modifiable) -->
                                <input type="hidden" name="id_courrier" value="<?= htmlspecialchars($suivi['id_courrier']) ?>">

                                <!-- Champ Destinataire -->
                                <div class="col-md-12">
                                    <label for="destinataire" class="form-label">Destinataire <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= !empty($errors['destinataire']) ? 'is-invalid' : '' ?>" 
                                           id="destinataire" name="destinataire" 
                                           value="<?= htmlspecialchars($data['destinataire']) ?>" required>
                                    <?php if (!empty($errors['destinataire'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['destinataire']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Champ Statut 1 -->
                                <div class="col-md-4">
                                    <label class="form-label">Statut 1</label>
                                    <div class="statut-container">
                                        <?php foreach ($statuts as $value => $label): ?>
                                            <div class="form-check form-check-inline me-2">
                                                <input class="form-check-input statut-checkbox" type="checkbox" 
                                                       id="statut1_<?= htmlspecialchars($value) ?>" 
                                                       name="statut_1[]" 
                                                       value="<?= htmlspecialchars($value) ?>"
                                                       <?= in_array($value, (array)$data['statut_1']) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="statut1_<?= htmlspecialchars($value) ?>">
                                                    <?= htmlspecialchars($label) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="autre-statut mt-2">
                                        <input type="text" class="form-control" id="autre_statut_1" name="autre_statut_1" 
                                               placeholder="Ajouter un statut personnalisé" value="<?= htmlspecialchars($data['autre_statut_1']) ?>">
                                    </div>
                                </div>

                                <!-- Champ Statut 2 -->
                                <div class="col-md-4">
                                    <label class="form-label">Statut 2</label>
                                    <div class="statut-container">
                                        <?php foreach ($statuts as $value => $label): ?>
                                            <div class="form-check form-check-inline me-2">
                                                <input class="form-check-input statut-checkbox" type="checkbox" 
                                                       id="statut2_<?= htmlspecialchars($value) ?>" 
                                                       name="statut_2[]" 
                                                       value="<?= htmlspecialchars($value) ?>"
                                                       <?= in_array($value, (array)$data['statut_2']) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="statut2_<?= htmlspecialchars($value) ?>">
                                                    <?= htmlspecialchars($label) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="autre-statut mt-2">
                                        <input type="text" class="form-control" id="autre_statut_2" name="autre_statut_2" 
                                               placeholder="Ajouter un statut personnalisé" value="<?= htmlspecialchars($data['autre_statut_2']) ?>">
                                    </div>
                                </div>

                                <!-- Champ Statut 3 -->
                                <div class="col-md-4">
                                    <label class="form-label">Statut 3</label>
                                    <div class="statut-container">
                                        <?php foreach ($statuts as $value => $label): ?>
                                            <div class="form-check form-check-inline me-2">
                                                <input class="form-check-input statut-checkbox" type="checkbox" 
                                                       id="statut3_<?= htmlspecialchars($value) ?>" 
                                                       name="statut_3[]" 
                                                       value="<?= htmlspecialchars($value) ?>"
                                                       <?= in_array($value, (array)$data['statut_3']) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="statut3_<?= htmlspecialchars($value) ?>">
                                                    <?= htmlspecialchars($label) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="autre-statut mt-2">
                                        <input type="text" class="form-control" id="autre_statut_3" name="autre_statut_3" 
                                               placeholder="Ajouter un statut personnalisé" value="<?= htmlspecialchars($data['autre_statut_3']) ?>">
                                    </div>
                                </div>

                                <!-- Boutons -->
                                <div class="col-12 mt-4">
                                    <button type="button" id="submitBtn" class="btn btn-primary me-2">
                                        <i class="fas fa-save me-1"></i> Enregistrer
                                    </button>
                                    <a href="liste_suivi_courrierscsa.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Annuler
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirmer la modification</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Êtes-vous sûr de vouloir modifier ce suivi de courrier ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" id="confirmSubmit" class="btn btn-primary">Confirmer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion de la confirmation avant soumission
            const submitBtn = document.getElementById('submitBtn');
            const confirmSubmit = document.getElementById('confirmSubmit');
            const modificationForm = document.getElementById('modificationForm');
            const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));

            submitBtn.addEventListener('click', function() {
                if (modificationForm.checkValidity()) {
                    confirmationModal.show();
                } else {
                    modificationForm.classList.add('was-validated');
                }
            });

            confirmSubmit.addEventListener('click', function() {
                confirmationModal.hide();
                modificationForm.submit();
            });

            // Afficher le modal de succès si nécessaire
            <?php if (isset($_SESSION['show_success_modal']) && $_SESSION['show_success_modal']): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: '<?= $_SESSION['success_message'] ?>',
                    confirmButtonText: 'OK',
                    willClose: () => {
                        window.location.href = 'liste_suivi_courrierscsa.php';
                    }
                });
                <?php 
                unset($_SESSION['show_success_modal']); 
                unset($_SESSION['success_message']); 
                ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>