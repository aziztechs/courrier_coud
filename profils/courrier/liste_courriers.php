<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courier_coud/');
    session_destroy();
    exit();
}

unset($_SESSION['classe']);
require_once('../../traitement/courrier_fonctions.php');
require_once('../../traitement/courriers/traitement_liste_courrier.php');
include('../../activite.php');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: GESTION_COURRIER</title>
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="css/form.css" />
    <link rel="stylesheet" href="../../assets/css/usersliste.css" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>
    
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 140px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Gestion des Courriers !<br>
                <span>
                    (<?= htmlspecialchars($_SESSION['Prenom'] . ' ' . htmlspecialchars($_SESSION['Nom']) . ' - ' . htmlspecialchars($_SESSION['Fonction'])) ?>)
                </span>
            </p>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="container-table col-md-12 mt-4">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                <h1 class="text-center m-0"><i class="fas fa-envelope me-2"></i>LISTE DES COURRIERS</h1>
                <div class="d-flex align-items-center">
                    <span class="badge bg-light text-dark me-3"><?= $totalCourriers ?> résultat(s)</span>
                    <a href="ajouter_courrier.php" class="btn btn-sm btn-dark text-white">
                        <i class="fas fa-plus-circle me-1"></i> ATOUTER COURRIER
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Filtres -->
                <div class="filter-card mb-4">
                    <form method="get" action="liste_courriers.php">
                        
                        <div class="filter-row">
                            <div class="filter-group" style="flex: 4;">
                                <label for="search" class="form-label small">Recherche</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search" 
                                        value="<?= htmlspecialchars($search_term) ?>" 
                                        placeholder="N°, Nature, Type, Expéditeur...">
                                    <button type="submit" class="btn btn-primary action-btn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="filter-group-date">
                                <label for="date_debut" class="form-label small">Date début</label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                    value="<?= htmlspecialchars($date_debut) ?>">
                            </div>
                            
                            <div class="filter-group-date">
                                <label for="date_fin" class="form-label small">Date fin</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                    value="<?= htmlspecialchars($date_fin) ?>">
                            </div>
                            
                            <div class="filter-group d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i>
                                </button>
                                <a href="liste_courriers.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-sync-alt me-1"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Tableau -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">N° Courrier</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Objet</th>
                                <th class="text-center">Nature</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Voir</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($courriers)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="alert alert-info m-0">Aucun courrier trouvé.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($courriers as $courrier): ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($courrier['Numero_Courrier']) ?></td>
                                        <td class="text-nowrap"><?= date('d/m/Y', strtotime($courrier['date'])) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($courrier['Objet']) ?></td>
                                        <td class="text-center">
                                            <?php if ($courrier['Nature'] == 'arrive'): ?>
                                                <span class="badge bg-success">Arrivée</span>
                                            <?php else: ?>
                                                <span class="badge bg-info text-dark">Départ</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($courrier['Type'] == 'interne'): ?>
                                                <span class="badge bg-warning text-dark">Interne</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Externe</span>
                                            <?php endif; ?>
                                        </td>
                                         <td class="text-center">
                                            <a href="view_courrier.php?id=<?= $courrier['id_courrier'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                        <td class="table-actions text-center">
                                            <!-- Bouton Modifier - Désactivé si le courrier a un suivi -->
                                            <a href="modifier_courrier.php?id=<?= $courrier['id_courrier'] ?>" 
                                            class="btn btn-sm btn-warning btn-action <?= $courrier['has_suivi'] ? 'disabled' : '' ?>"
                                            title="Modifier"
                                            <?= $courrier['has_suivi'] ? 'onclick="return false;"' : '' ?>>
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- Bouton Supprimer - Désactivé si le courrier a un suivi -->
                                            <button class="btn btn-sm btn-danger btn-action delete-btn <?= $courrier['has_suivi'] ? 'disabled' : '' ?>" 
                                                    data-id="<?= $courrier['id_courrier'] ?>"
                                                    title="Supprimer"
                                                    <?= $courrier['has_suivi'] ? 'disabled' : '' ?>>
                                                <i class="fas fa-trash-alt"></i>
                                            </button>

                                            <?php if ($courrier['has_suivi']): ?>
                                                <button class="btn btn-sm btn-secondary btn-action" 
                                                        title="Ce courrier est déjà suivi" disabled>
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php else: ?>
                                                <a href="../suivis/ajouter_suivi_courrier.php?courrier_id=<?= $courrier['id_courrier'] ?>" 
                                                class="btn btn-sm btn-primary btn-action"
                                                title="Suivre ce courrier">
                                                    <i class="fas fa-tasks"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($courrier['pdf'])): ?>
                                                <a href="<?= htmlspecialchars($courrier['pdf']) ?>" 
                                                class="btn btn-sm btn-info btn-action"
                                                target="_blank"
                                                title="Voir PDF">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= buildPaginationLink(1, $_GET) ?>" aria-label="First" style="height: 30px;">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?= buildPaginationLink($page - 1, $_GET) ?>" aria-label="Previous" style="height: 30px;">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php 
                            // Afficher plus de pages autour de la page courante
                            $startPage = max(1, $page - 3);
                            $endPage = min($page + 3, $totalPages);
                            
                            // Afficher toujours la première page
                            if ($startPage > 1): ?>
                                <li class="page-item <?= 1 == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= buildPaginationLink(1, $_GET) ?>" style="height: 30px;">1</a>
                                </li>
                                <?php if ($startPage > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= buildPaginationLink($i, $_GET) ?>" style="height: 30px;"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php 
                            // Afficher toujours la dernière page
                            if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item <?= $totalPages == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= buildPaginationLink($totalPages, $_GET) ?>" style="height: 30px;"><?= $totalPages ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= buildPaginationLink($page + 1, $_GET) ?>" aria-label="Next" style="height: 30px;">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?= buildPaginationLink($totalPages, $_GET) ?>" aria-label="Last" style="height: 30px;">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer ce courrier ? Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <a id="confirmDelete" href="#" class="btn btn-danger">Supprimer</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Gestion de la suppression avec modal
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-btn');
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            const confirmDeleteBtn = document.getElementById('confirmDelete');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const courrierId = this.getAttribute('data-id');
                    confirmDeleteBtn.href = `liste_courriers.php?action=supprimer&id=${courrierId}`;
                    deleteModal.show();
                });
            });
            
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

<?php
// Fonction pour construire les liens de pagination
function buildPaginationLink($page, $queryParams) {
    $queryParams['page'] = $page;
    return 'liste_courriers.php?' . http_build_query($queryParams);
}
?>