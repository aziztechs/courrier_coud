<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

require_once('../../traitement/fonction.php');
include('../../activite.php');
require_once('../../traitement/recupImpuAlldep.php');

// Validation des entrées
$filters = [
    'search' => filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '',
    'nature' => filter_input(INPUT_GET, 'nature', FILTER_SANITIZE_STRING) ?? '',
    'type' => filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING) ?? '',
    'date' => filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING) ?? ''
];

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
$itemsPerPage = 5;

try {
    // Récupération des courriers filtrés
    $courriers = getFilteredCourriers($filters, $page, $itemsPerPage);
    $totalCourriers = countFilteredCourriers($filters);
    $totalPages = max(1, ceil($totalCourriers / $itemsPerPage));
    
    // Ajustement de la page si elle dépasse le total
    if ($page > $totalPages && $totalPages > 0) {
        $page = $totalPages;
        $query_params = http_build_query(array_merge($filters, ['page' => $page]));
        header("Location: ?$query_params");
        exit();
    }
} catch (Exception $e) {
    error_log("Erreur base de données: " . $e->getMessage());
    $courriers = [];
    $totalCourriers = 0;
    $totalPages = 1;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil Direction - Gestion Courrier</title>
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/styleCourrier.css">
    <link rel="stylesheet" href="css/liste.css">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
   
</head>

<body>
    <?php include('../../head.php'); ?>
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 120px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Gestion des Courriers<br>
                <span>
                    (<?= htmlspecialchars($_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']) ?>)
                </span>
            </p>
        </div>
    </div>  

    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                <h1 class="h4 mb-0"><i class="fas fa-list"></i> Liste des Courriers</h1>
                <span class="badge bg-light text-dark"><?= $totalCourriers ?> résultat(s)</span>
                <div>
                    <a href="accueil_courrier.php" class="btn btn-sm btn-light text-primary">
                        <i class="bi bi-plus-lg"></i> Ajouter Courrier
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="filter-card">
                    <form method="get" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Recherche</label>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Numéro, objet, expéditeur..." 
                                       value="<?= htmlspecialchars($filters['search']) ?>">
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Nature</label>
                                <select name="nature" class="form-select">
                                    <option value="">Toutes natures</option>
                                    <option value="arrive" <?= $filters['nature'] === 'arrive' ? 'selected' : '' ?>>Arrivé</option>
                                    <option value="depart" <?= $filters['nature'] === 'depart' ? 'selected' : '' ?>>Départ</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Type</label>
                                <select name="type" class="form-select">
                                    <option value="">Tous types</option>
                                    <option value="interne" <?= $filters['type'] === 'interne' ? 'selected' : '' ?>>Interne</option>
                                    <option value="externe" <?= $filters['type'] === 'externe' ? 'selected' : '' ?>>Externe</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Date</label>
                                <input type="date" name="date" class="form-control datepicker" 
                                       value="<?= htmlspecialchars($filters['date']) ?>">
                            </div>
                            
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-funnel"></i> Filtrer
                                </button>
                            </div>
                            
                            <div class="col-md-2">
                                <a href="?" class="btn btn-secondary btn-sm w-100">
                                    <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">Numéro</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Objet</th>
                                <th class="text-center">Nature</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Expéditeur</th>
                                <th class="text-center">PDF</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($courriers)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-exclamation-circle me-2"></i>Aucun courrier trouvé
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($courriers as $courrier): ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($courrier['Numero_courrier']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($courrier['date']) ?></td>
                                        <td><?= htmlspecialchars($courrier['Objet']) ?></td>
                                        <td class="text-center">
                                            <span class="badge text-white badge-nature">
                                                <?= htmlspecialchars($courrier['Nature']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge text-white badge-type">
                                                <?= htmlspecialchars($courrier['Type']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($courrier['Expediteur']) ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($courrier['pdf'])): ?>
                                                <a href="../uploads/<?= htmlspecialchars($courrier['pdf']) ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="Voir le PDF">
                                                   <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="mise_a_jour1.php?id=<?= htmlspecialchars($courrier['id_courrier']) ?>" 
                                                   class="btn btn-primary btn-sm me-2" 
                                                   title="Modifier">
                                                   <i class="fas fa-edit "></i>
                                                </a>
                                                <?php if (!isset($_SESSION['Fonction']) || $_SESSION['Fonction'] !== 'assistant_courrier'): ?>
                                                <button class="btn btn-danger btn-sm delete-btn" 
                                                        data-id="<?= htmlspecialchars($courrier['id_courrier']) ?>" 
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

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Navigation des courriers" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => 1])) ?>">
                                    <i class="bi bi-chevron-double-left"></i>
                                </a>
                            </li>
                            
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            
                            <?php 
                            $startPage = max(1, min($page - 2, $totalPages - 4));
                            $endPage = min($totalPages, $startPage + 4);
                            
                            if ($startPage > 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif;
                            
                            for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor;
                            
                            if ($endPage < $totalPages): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                            
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $totalPages])) ?>">
                                    <i class="bi bi-chevron-double-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation des datepickers
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });
        
        // Confirmation de suppression
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                Swal.fire({
                    title: 'Confirmer la suppression',
                    text: "Êtes-vous sûr de vouloir supprimer ce courrier ?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'supprimer_courrier.php?id=' + id;
                    }
                });
            });
        });
    });
    </script>
</body>
</html>