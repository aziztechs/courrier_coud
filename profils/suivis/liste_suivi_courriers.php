<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

require_once '../../traitement/suivi_courrier_fonctions.php';


$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;
$filters = [
    'search' => filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING),
    'date_debut' => filter_input(INPUT_GET, 'date_debut', FILTER_SANITIZE_STRING),
    'date_fin' => filter_input(INPUT_GET, 'date_fin', FILTER_SANITIZE_STRING),
    'statut' => filter_input(INPUT_GET, 'statut', FILTER_SANITIZE_STRING),
    'nature' => filter_input(INPUT_GET, 'nature', FILTER_SANITIZE_STRING)
];

// Récupération des données
$result = getSuivis($mysqli, $page, $filters);
if (!$result['success']) {
    die("Erreur: " . $result['error']);
}

$suivis = $result['data'];
$pagination = $result['pagination'];
$statuts = getStatutsPossibles($mysqli);
$totalSuivis = getCountTotalSuiviCourrier($mysqli);

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
            <p class="lead">Espace Administration : Gestion des Suivis !<br>
                <span>
                    (<?= htmlspecialchars($_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']) ?>)
                </span>
            </p>
        </div>
    </div> 

    <div class="container-table col-md-12 mt-4">

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                <h2 class="text-center m-0"><i class="fa fa-list"></i>&nbsp;LISTE DES SUIVIS</h2>
                <div class="d-flex align-items-center">
                    <span class="badge bg-light text-dark me-3"><?= $totalSuivis ?> résultat(s)</span>
                    <a href="../courrier/liste_courriers.php" class="btn btn-light">
                        <i class="fas fa-list"></i> Liste les courriers
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Formulaire de filtres -->
                <div class="filter-card mb-4">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="filter-row mb-3">
                            <div class="filter-group" style="flex: 4;">
                                <label for="search" class="form-label small">Recherche</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search" 
                                        value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                                        placeholder="Rechercher...">
                                    <button type="submit" class="btn btn-primary action-btn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label for="date_debut" class="form-label">Date début</label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                    value="<?= htmlspecialchars($filters['date_debut'] ?? '') ?>">
                            </div>

                            <div class="col-md-2">
                                <label for="date_fin" class="form-label">Date fin</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                    value="<?= htmlspecialchars($filters['date_fin'] ?? '') ?>">
                            </div>

                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter"></i>
                                </button>
                                <a href="liste_suivi_courriers.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-sync-alt"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>


                <!-- Tableau des résultats -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">N° Courrier</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Objet</th>
                                <th class="text-center">Expéditeur</th>
                                <th class="text-center">Destinataire</th>
                                <th class="text-center">Statut 1</th>
                                <th class="text-center">Statut 2</th>
                                <th class="text-center">Statut 3</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($suivis)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">Aucun suivi trouvé</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($suivis as $suivi): ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($suivi['Numero_Courrier']) ?></td>
                                        <td class="text-center"><?= date('d/m/Y', strtotime($suivi['date'])) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($suivi['Objet']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($suivi['Expediteur']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($suivi['destinataire']) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= getStatusBadgeClass($suivi['statut_1']) ?>">
                                                <?= htmlspecialchars($suivi['statut_1'] ?? '') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= getStatusBadgeClass($suivi['statut_2']) ?>">
                                                <?= htmlspecialchars($suivi['statut_2'] ?? '') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= getStatusBadgeClass($suivi['statut_3']) ?>">
                                                <?= htmlspecialchars($suivi['statut_3'] ?? '') ?>
                                            </span>
                                        </td>
                                        <td class="text-nowrap text-center">
                                            <a href="modifier_suivi_courrier.php?id=<?= $suivi['id_suivi'] ?>" 
                                            class="btn btn-sm btn-primary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger delete-btn" 
                                                    data-id="<?= $suivi['id_suivi'] ?>"
                                                    data-num="<?= $suivi['Numero_Courrier'] ?>"
                                                    title="Supprimer">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            <?php if ($pagination['page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" 
                                    href="?<?= http_build_query(array_merge($filters, ['page' => 1])) ?>">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['page'] - 1])) ?>">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                                    <a class="page-link" 
                                    href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>">
                                    <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" 
                                    href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['page'] + 1])) ?>">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                                 <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['total_pages']])) ?>" aria-label="Last">
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

    <!-- JavaScript pour la suppression avec confirmation -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const num = this.getAttribute('data-num');
            
            Swal.fire({
                title: 'Confirmer la suppression',
                html: `Voulez-vous vraiment supprimer le suivi du courrier <b>${num}</b> ?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#d33',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `supprimer_suivi_courrier.php?id=${id}`;
                }
            });
        });
    });
    </script>
</body>
</html>
