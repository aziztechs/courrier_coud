<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

require_once('../../traitement/fonction_archive.php');
require_once('../../traitement/fonction.php');
require_once('../../traitement/archive/traitement_list_archive.php');

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD - Gestion des Archives</title>
    <link rel="shortcut icon" href="../../assets/img/log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/styleCourrier.css">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .badge-manuel { background-color: #0d6efd; }
        .badge-automatique { background-color: #0dcaf0; }
        .badge-annuel { background-color: #ffc107; color: #000; }
        .table-responsive { max-height: 600px; }
        .action-buttons .btn { margin: 0 2px; }
        .text-truncate { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .filter-card { background-color: #f8f9fa; border-radius: 0.375rem; }
        .filter-label { font-size: 0.875rem; color: #6c757d; }
    </style>
</head>
<body>
    <?php include('../../head.php'); ?>
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 120px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Gestion des archives !<br>
                <span>
                    (<?= htmlspecialchars($_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']) ?>)
                </span>
            </p>
        </div>
    </div>  
    
    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                <h1 class="h2"><i class="fas fa-list"></i> Liste des Archives</h1>
                <span class="badge bg-light text-dark"><?= $totalArchives ?> résultat(s)</span>

                <div class="d-flex gap-2">
                    <a href="ajouter_archive.php" class="btn btn-sm btn-light text-primary">
                        <i class="bi bi-plus-lg"></i> Ajouter Archive
                    </a>
                    <a href="../facture/liste_factures.php" class="btn btn-sm btn-dark text-light">
                        <i class="bi bi-list"></i> Liste des Factures
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Filtres -->
                <div class="card filter-card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="search" class="form-label filter-label">Recherche</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($filters['search']) ?>" 
                                       placeholder="N° correspondance, commentaire...">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="type_archivage" class="form-label filter-label">Type</label>
                                <select class="form-select" id="type_archivage" name="type_archivage">
                                    <option value="">Tous types</option>
                                    <option value="manuel" <?= $filters['type_archivage'] === 'manuel' ? 'selected' : '' ?>>Manuel</option>
                                    <option value="automatique" <?= $filters['type_archivage'] === 'automatique' ? 'selected' : '' ?>>Automatique</option>
                                    <option value="annuel" <?= $filters['type_archivage'] === 'annuel' ? 'selected' : '' ?>>Annuel</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="motif_archivage" class="form-label filter-label">Motif</label>
                                <select class="form-select" id="motif_archivage" name="motif_archivage">
                                    <option value="">Tous motifs</option>
                                    <option value="traitement_termine" <?= $filters['motif_archivage'] === 'traitement_termine' ? 'selected' : '' ?>>Traitement terminé</option>
                                    <option value="delai_depasse" <?= $filters['motif_archivage'] === 'delai_depasse' ? 'selected' : '' ?>>Délai dépassé</option>
                                    <option value="demande_specifique" <?= $filters['motif_archivage'] === 'demande_specifique' ? 'selected' : '' ?>>Demande spécifique</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="date_archivage" class="form-label filter-label">Date d'archivage</label>
                                <input type="date" class="form-control" id="date_archivage" 
                                    name="date_archivage" value="<?= htmlspecialchars($filters['date_archivage']) ?>" 
                                    max="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-1 "> <!-- Alignement vertical réduit -->
                                    <label>Filtre</label>
                                    <button type="submit" class="btn btn-primary w-100" title="Appliquer les filtres"> <!-- Bouton plus petit -->
                                        <i class="bi bi-funnel"></i> 
                                    </button> 
                                </div>
                                <div class="col-md-1 ">
                                    <label>Réinitialisation</label>
                                    <a href="?" class="btn btn-secondary w-100">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                </div>
                            </div>   
                            
                        </form>
                    </div>
                </div>
                
                <!-- Messages d'alerte -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-check-circle-fill"></i> L'opération a été effectuée avec succès.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif (isset($errorMessage)): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?= $errorMessage ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Tableau des archives -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">Date archivage</th>
                                <th class="text-center">N° Correspondance</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Motif</th>
                                <th class="text-center">Commentaire</th>
                                <th class="text-center">Document</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($archives)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="bi bi-exclamation-circle me-2"></i>Aucune archive correspondant aux critères
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($archives as $archive): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($archive['date_archivage'])) ?></td>
                                        <td><?= htmlspecialchars($archive['num_correspondance']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= htmlspecialchars($archive['type_archivage']) ?>">
                                                <?= ucfirst(htmlspecialchars($archive['type_archivage'])) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($archive['motif_archivage']) ?></td>
                                        <td>
                                            <div class="text-truncate" data-bs-toggle="tooltip" 
                                                 title="<?= htmlspecialchars($archive['commentaire']) ?>">
                                                <?= $archive['commentaire'] ? nl2br(htmlspecialchars($archive['commentaire'])) : '-' ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($archive['pdf_archive'])): ?>
                                                <a href="../uploads/<?= htmlspecialchars($archive['pdf_archive']) ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   data-bs-toggle="tooltip" 
                                                   title="Voir le document">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center action-buttons">
                                            <a href="modifier_archive.php?id=<?= htmlspecialchars($archive['id_archive']) ?>"  
                                               class="btn btn-primary btn-sm" 
                                               title="Modifier">
                                               <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if (!isset($_SESSION['Fonction']) || $_SESSION['Fonction'] !== 'assistant_courrier'): ?>
                                            <a href="supprimer_archive.php?id=<?= htmlspecialchars($archive['id_archive']) ?>" 
                                               class="btn btn-danger btn-sm archive-delete" 
                                               title="Supprimer">
                                               <i class="fas fa-trash-alt"></i>
                                            </a>
                                            <?php endif; ?>
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
                    <nav aria-label="Navigation des archives" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => 1])) ?>">
                                    <i class="bi bi-chevron-double-left"></i>
                                </a>
                            </li>
                            
                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $currentPage - 1])) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            
                            <?php 
                            $startPage = max(1, min($currentPage - 2, $totalPages - 4));
                            $endPage = min($totalPages, $startPage + 4);
                            
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
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                            
                            <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation des tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Gestion de la suppression avec confirmation
        document.querySelectorAll('.archive-delete').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const deleteUrl = this.getAttribute('href');
                
                Swal.fire({
                    title: 'Confirmer la suppression',
                    text: "Cette action est irréversible. Voulez-vous vraiment supprimer cette archive?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });
        });
        
        // Limiter la date maximale à aujourd'hui
        document.getElementById('date_archivage').max = new Date().toISOString().split('T')[0];
    });
    </script>
</body>
</html>