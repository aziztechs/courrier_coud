<?php
session_start();
if (empty($_SESSION['username'])) {
    header('Location: /courrier_coud/');
    exit();
}

require_once('../../traitement/fonction_archive.php');
require_once('../../traitement/fonction.php');

// Configuration de la pagination
$perPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, $currentPage);
$offset = ($currentPage - 1) * $perPage;

// Fonction pour récupérer les valeurs ENUM avec MySQLi
function getEnumValuesFromDatabase() {
    global $connexion; // Utilisation de la connexion MySQLi globale
    
    $table = 'archive';
    $column = 'type_archivage';
    
    $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
    $result = $connexion->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (isset($row['Type'])) {
            preg_match("/^enum\(\'(.*)\'\)$/", $row['Type'], $matches);
            if ($matches) {
                return explode("','", $matches[1]);
            }
        }
    }
    
    // Valeurs par défaut si la récupération échoue
    return [
        'depart', 'decision', 'attestation', 'note_service', 
        'etat_paiement', 'etat_salaire', 'remboursement',
        'circulaire', 'note_information', 'autorisation_engagement', 
        'bordereau'
    ];
}

// Récupération des valeurs ENUM
$enumValues = getEnumValuesFromDatabase();

// Définition des filtres avec validation
$filters = [
    'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
    'type_archivage' => isset($_GET['type_archivage']) && in_array($_GET['type_archivage'], $enumValues) 
        ? $_GET['type_archivage'] : '',
    'date_debut' => isset($_GET['date_debut']) ? $_GET['date_debut'] : '',
    'date_fin' => isset($_GET['date_fin']) ? $_GET['date_fin'] : ''
];

// Validation des dates
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Nettoyage des dates invalides
if (!empty($filters['date_debut']) && !validateDate($filters['date_debut'])) {
    $filters['date_debut'] = '';
}

if (!empty($filters['date_fin']) && !validateDate($filters['date_fin'])) {
    $filters['date_fin'] = '';
}

// Correction si date_debut > date_fin
if (!empty($filters['date_debut']) && !empty($filters['date_fin']) 
    && $filters['date_debut'] > $filters['date_fin']) {
    list($filters['date_fin'], $filters['date_debut']) = [$filters['date_debut'], $filters['date_fin']];
}

// Adaptation des filtres pour utiliser nos fonctions
$adaptedFilters = [
    'search' => $filters['search'],
    'type_archivage' => $filters['type_archivage']
];

// Ajout du filtre de date si valide
if (!empty($filters['date_debut'])) {
    $adaptedFilters['date_archivage'] = $filters['date_debut'];
}

// Récupération des données avec nos fonctions
try {
    // Récupération des archives filtrées
    $archives = getArchivesWithFilters($adaptedFilters, $offset, $perPage);
    
    // Comptage total des archives filtrées
    $totalArchives = countArchivesWithFilters($adaptedFilters);
    
    // Calcul du nombre total de pages
    $totalPages = max(1, ceil($totalArchives / $perPage));
    
    // Correction de la page courante si nécessaire
    if ($currentPage > $totalPages && $totalPages > 0) {
        $currentPage = $totalPages;
        $offset = ($currentPage - 1) * $perPage;
        $archives = getArchivesWithFilters($adaptedFilters, $offset, $perPage);
    }
    
    $errorMessage = '';
} catch (Exception $e) {
    $errorMessage = "Erreur lors de la récupération des archives: " . $e->getMessage();
    $archives = [];
    $totalArchives = 0;
    $totalPages = 1;
}

// Gestion des messages
$alertMessage = '';
$alertClass = '';
if (isset($_SESSION['delete_success'])) {
    $alertMessage = $_SESSION['delete_success'];
    $alertClass = 'alert-success';
    unset($_SESSION['delete_success']);
} elseif (isset($_SESSION['delete_error'])) {
    $alertMessage = $_SESSION['delete_error'];
    $alertClass = 'alert-danger';
    unset($_SESSION['delete_error']);
} elseif (isset($_SESSION['operation_success'])) {
    $alertMessage = $_SESSION['operation_success'];
    $alertClass = 'alert-success';
    unset($_SESSION['operation_success']);
} elseif (isset($errorMessage)) {
    $alertMessage = $errorMessage;
    $alertClass = 'alert-danger';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil Direction - Gestion Courrier</title>
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/usersliste.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .filter-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filter-row {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }
        .filter-group {
            flex: 1;
            min-width: 100px;
        }
        .filter-group-date {
            flex: 1;
            min-width: 150px;
        }
        .action-btn {
            width: 40px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .table-actions {
            white-space: nowrap;
        }
        .btn-action:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }
        .badge-manuel { background-color: #6c757d; }
        .badge-automatique { background-color: #0d6efd; }
        .badge-annuel { background-color: #198754; }
        .badge-depart { background-color: #6c757d; }
        .badge-decision { background-color: #0d6efd; }
        .badge-attestation { background-color: #198754; }
        .badge-note_service { background-color: #fd7e14; }
        .badge-etat_paiement { background-color: #6f42c1; }
        .badge-etat_salaire { background-color: #20c997; }
        .badge-remboursement { background-color: #d63384; }
        .badge-circulaire { background-color: #0dcaf0; }
        .badge-note_information { background-color: #ffc107; }
        .badge-autorisation_engagement { background-color: #6610f2; }
        .badge-bordereau { background-color: #212529; }
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

    <div class="container-table col-md-12 mt-4">
        <?php if ($alertMessage): ?>
            <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($alertMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                <h1 class="text-center"><i class="fa fa-list"></i>&nbsp; LISTE DES ARCHIVES</h1>
                <div class="d-flex align-items-center">
                    <span class="badge bg-light text-dark me-3"><?= $totalArchives ?> résultat(s)</span>
                    <div class="d-flex gap-2">
                        <a href="ajouter_archive.php" class="btn btn-sm btn-light text-primary">
                            <i class="fas fa-plus-circle me-1"></i> Nouveau
                        </a>
                        <?php if(!isset($_SESSION['Fonction']) || $_SESSION['Fonction'] !== 'assistant_courrier'): ?>
                        <a href="../facture/liste_factures.php" class="btn btn-sm btn-dark">
                            <i class="fas fa-file-invoice me-1"></i> Factures
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Filtres -->
                <div class="filter-card mb-4">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="filter-row">
                            <div class="filter-group" style="flex: 4;">
                                <label for="search" class="form-label small">Recherche</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search" 
                                    value="<?= htmlspecialchars($filters['search']) ?>" 
                                    placeholder="N° correspondance, description, type_archive...">
                                    <button type="submit" class="btn btn-primary action-btn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="filter-group">
                                <label for="type_archivage" class="form-label small">Type archivage</label>
                                <select class="form-select" id="type_archivage" name="type_archivage">
                                    <option value="">Tous les types</option>
                                    <?php foreach ($enumValues as $value): 
                                        $label = ucfirst(str_replace('_', ' ', $value));
                                    ?>
                                        <option value="<?= htmlspecialchars($value) ?>" 
                                            <?= $filters['type_archivage'] === $value ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group-date">
                                <label for="date_debut" class="form-label small">Date début</label>
                                <input type="date" class="form-control" id="date_debut" 
                                    name="date_debut" value="<?= htmlspecialchars($filters['date_debut']) ?>" 
                                    max="<?= date('Y-m-d') ?>">
                            </div>
                            
                            <div class="filter-group-date">
                                <label for="date_fin" class="form-label small">Date fin</label>
                                <input type="date" class="form-control" id="date_fin" 
                                    name="date_fin" value="<?= htmlspecialchars($filters['date_fin']) ?>" 
                                    max="<?= date('Y-m-d') ?>">
                            </div>

                            <div class="filter-group d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="fas fa-filter me-1"></i> 
                                </button>
                                <a href="liste_archive.php" class="btn btn-outline-secondary" title="Réinitialiser">
                                    <i class="fas fa-sync-alt"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
              
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">Date archivage</th>
                                <th class="text-center">N° Correspondance</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Description</th>
                                <th class="text-center">Document</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($archives)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">
                                        <i class="fas fa-info-circle me-2"></i>Aucune archive trouvée
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($archives as $archive): ?>
                                    <tr>
                                        <td class="text-center"><?= date('d/m/Y H:i', strtotime($archive['date_archivage'])) ?></td>
                                        <td class="text-center fw-bold"><?= htmlspecialchars($archive['num_correspondance']) ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-<?= htmlspecialchars($archive['type_archivage']) ?>">
                                                <?= ucfirst(str_replace('_', ' ', htmlspecialchars($archive['type_archivage']))) ?>
                                            </span>
                                        </td>
                                        <td class="text-truncate" title="<?= htmlspecialchars($archive['commentaire']) ?>">
                                            <?= $archive['commentaire'] ? nl2br(htmlspecialchars($archive['commentaire'])) : '-' ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($archive['pdf_archive'])): ?>
                                                <a href="../../uploads/<?= htmlspecialchars($archive['pdf_archive']) ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="Voir le document">
                                                   <i class="fas fa-file-pdf"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center action-btns">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="modifier_archive.php?id=<?= htmlspecialchars($archive['id_archive']) ?>"  
                                                   class="btn btn-sm btn-primary" 
                                                   title="Modifier">
                                                   <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($_SESSION['Fonction'] !== 'assistant_courrier'): ?>
                                                <button class="btn btn-sm btn-danger archive-delete" 
                                                        data-id="<?= htmlspecialchars($archive['id_archive']) ?>"
                                                        data-num="<?= htmlspecialchars($archive['num_correspondance']) ?>" 
                                                        title="Supprimer">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>          
            </div>                
       
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Navigation des archives" class="mb-3">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => 1])) ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        
                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $currentPage - 1])) ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                        
                        <?php 
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        
                        if ($startPage > 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif;
                        
                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor;
                        
                        if ($endPage < $totalPages): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        
                        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $currentPage + 1])) ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        
                        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $totalPages])) ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation des tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Gestion de la suppression avec confirmation
        document.querySelectorAll('.archive-delete').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const num = this.getAttribute('data-num');
                
                Swal.fire({
                    title: 'Confirmer la suppression',
                    html: `Êtes-vous sûr de vouloir supprimer l'archive <b>${num}</b> ?<br>Cette action est irréversible.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Ajout d'un indicateur de chargement
                        Swal.fire({
                            title: 'Suppression en cours',
                            html: 'Veuillez patienter...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Redirection vers la page de suppression
                        window.location.href = 'supprimer_archive.php?id=' + id;
                    }
                });
            });
        });
        
        // Afficher un message de succès après suppression si présent dans l'URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('delete_success')) {
            Swal.fire({
                title: 'Succès',
                text: 'L\'archive a été supprimée avec succès',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                // Nettoyer l'URL
                urlParams.delete('delete_success');
                window.history.replaceState({}, '', `${window.location.pathname}?${urlParams}`);
            });
        }
    });
    </script>
</body>
</html>