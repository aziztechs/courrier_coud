<?php
session_start();
if (empty($_SESSION['username'])) {
    header('Location: /courrier_coud/');
    exit();
}

require_once '../../traitement/fonction_facture.php';
require_once '../../traitement/factures/traitement_liste_facture.php';

// Génération d'un token CSRF si inexistant
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 140px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Gestion des factures<br>
                <span>
                    (<?= htmlspecialchars($_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']) ?>)
                </span>
            </p>
        </div>
    </div>  

    <div class="container-table col-md-12 mt-4">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                <h1 class="text-center"><i class="fa fa-list"></i>&nbsp; LISTE DES FACTURES</h1>
                <div class="d-flex align-items-center">
                    <span class="badge bg-light text-dark me-3"><?= $total_factures ?> résultat(s)</span>
                    <div class="d-flex gap-2">
                        <a href="ajouter_facture.php" class="btn btn-sm btn-light text-primary">
                            <i class="fas fa-plus-circle me-1"></i> Nouveau
                        </a>
                    </div>
                </div>
            </div>
            <div class="filter-card m-3 p-3">
                <form method="get" class="row mt-3">
                    <div class="filter-row">
                        <div class="filter-group " style="flex: 4;">
                            <label for="search" class="form-label small">Recherche</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                                       placeholder="N° Courrier, Expéditeur, Type Facture...">
                                <button type="submit" class="btn btn-primary action-btn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                            
                        <div class="filter-group-date">
                            <input type="date" class="form-control" name="date_debut" 
                                value="<?= htmlspecialchars($filters['date_debut'] ?? '') ?>">
                        </div>
                        <div class="filter-group-date">
                            <input type="date" class="form-control" name="date_fin" 
                                value="<?= htmlspecialchars($filters['date_fin'] ?? '') ?>">
                        </div>
                        <div class="filter-group d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-filter me-1"></i> Filtrer
                            </button>
                            <a href="liste_factures.php" class="btn btn-outline-secondary" title="Réinitialiser">
                                <i class="fas fa-sync-alt"></i>
                            </a>
                        </div>
                        </div>
                    </div>
                </form>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">N° COURRIER</th>
                                <th class="text-center">Date réception</th>
                                <th class="text-center">Expéditeur</th>
                                <th class="text-center">N° FACTURE</th>
                                <th class="text-center">DECADE</th>
                                <th class="text-center">Montant</th>
                                <th class="text-center">Type / PDF</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($factures)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Aucune facture trouvée</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($factures as $facture): ?>
                                    <tr style="text-align: center;">
                                        <td class="text-center"><?= htmlspecialchars($facture['numero_courrier']) ?></td>
                                        <td class="text-center"><?= date('d/m/Y', strtotime($facture['date_arrive'])) ?></td>
                                        <td class="text-truncate" title="<?= htmlspecialchars($facture['expediteur']) ?>">
                                            <?= htmlspecialchars($facture['expediteur']) ?>
                                        </td>
                                        <td class="text-truncate" title="<?= htmlspecialchars($facture['numero_facture']) ?>">
                                            <?= htmlspecialchars($facture['numero_facture']) ?>
                                        </td>
                                        <td class="text-truncate" title="<?= htmlspecialchars($facture['decade']) ?>">
                                            <?= htmlspecialchars($facture['decade']) ?>
                                        </td>
                                        <td class="text-truncate">
                                            <?= number_format($facture['montant_ttc'], 2, ',', ' ') ?>
                                        </td>
                                        
                                        <td class="text-center">
                                            <span class="badge bg-<?= 
                                                ($facture['type_facture'] === 'Payée') ? 'success' : 
                                                (($facture['type_facture'] === 'En attente') ? 'warning' : 'secondary') 
                                            ?>">
                                                <?= htmlspecialchars($facture['type_facture']) ?>
                                            </span>
                                            <?php if (!empty($facture['facture_pdf'])): ?>
                                                <a href="../../uploads/factures/<?= htmlspecialchars($facture['facture_pdf']) ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary ms-2"
                                                   title="Voir le document">
                                                   <i class="fas fa-file-pdf"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted ms-2">-</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="modifier_facture.php?id=<?= $facture['id_facture'] ?>" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="Modifier">
                                                   <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($_SESSION['Fonction'] !== 'assistant_courrier'): ?>
                                                <button class="btn btn-sm btn-danger delete-facture" 
                                                        data-id="<?= $facture['id_facture'] ?>" 
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
        </div> 

        <?php if ($total_pages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => max(1, $page - 1)])) ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <?php 
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif;
                
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor;
                
                if ($end_page < $total_pages): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => min($total_pages, $page + 1)])) ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        // Gestion de la suppression avec confirmation
        $('.delete-facture').on('click', function() {
            const factureId = $(this).data('id');
            const csrfToken = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>';
            const baseUrl = '<?= dirname($_SERVER['PHP_SELF']) ?>';
            
            Swal.fire({
                title: 'Confirmer la suppression',
                html: `Êtes-vous sûr de vouloir supprimer la facture #${factureId} ?<br>
                    <small class="text-danger">Cette action supprimera définitivement :</small>
                    <ul class="text-start small mt-2">
                        <li>La fiche facture</li>
                        <li>Le fichier PDF associé</li>
                        <li>Toutes les données liées</li>
                    </ul>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<i class="fas fa-trash"></i> Confirmer',
                cancelButtonText: '<i class="fas fa-times"></i> Annuler',
                backdrop: true,
                allowOutsideClick: false,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: `${baseUrl}/supprimer_facture.php`,
                        type: 'POST',
                        data: {
                            id_facture: factureId,  // Nom du paramètre synchronisé avec le backend
                            csrf_token: csrfToken
                        },
                        dataType: 'json'
                    }).fail(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error.statusText || 'Erreur réseau'}`
                        );
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const response = result.value;
                    if (response.success) {
                        Swal.fire({
                            title: 'Suppression réussie!',
                            html: `<i class="fas fa-check-circle text-success fa-2x mb-3"></i>
                                <p>${response.message}</p>
                                <small class="text-muted">ID: #${response.deleted_id}</small>`,
                            icon: 'success',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            // Rafraîchissement ciblé ou redirection
                            if (window.performance.navigation.type === 2) {
                                // Si navigation via cache
                                window.location.href = 'liste_factures.php';
                            } else {
                                // Rafraîchissement standard
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Erreur',
                            html: `<i class="fas fa-exclamation-triangle text-danger fa-2x mb-3"></i>
                                <p>${response.message || 'Erreur lors de la suppression'}</p>
                                ${response.error_details ? `<small class="text-muted">${response.error_details}</small>` : ''}`,
                            icon: 'error'
                        });
                    }
                }
            });
        });
    });
</script>
</body>
</html>