<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

require_once '../../traitement/fonction_facture.php';
require_once '../../traitement/traitements.php'; // Pour la connexion à la base
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bureau Courrier - Gestion Factures</title>
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
            <p class="lead">Espace Administration : Gestion des Factures !<br>
                <span>
                    (<?= htmlspecialchars($_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']) ?>)
                </span>
            </p>
        </div>
    </div>  

    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                <h1 class="h2"><i class="fas fa-list"></i> Liste des Factures</h1>
                <span class="badge bg-light text-dark"><?= $total_factures ?> facture(s)</span>
                <div>
                    <a href="ajouter_facture.php" class="btn btn-sm btn-light text-primary">
                        <i class="bi bi-plus-lg"></i> Ajouter une Facture
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="m-3">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="search" class="form-label small text-muted">Recherche</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($filters['search']) ?>" 
                                   placeholder="N° facture, n° courrier, expéditeur...">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="type_facture" class="form-label small text-muted">Type de facture</label>
                            <select class="form-select" id="type_facture" name="type_facture">
                                <option value="">Tous types</option>
                                <?php foreach ($types as $t): ?>
                                    <option value="<?= htmlspecialchars($t) ?>" <?= $filters['type_facture'] === $t ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="date_arrive" class="form-label small text-muted">Date d'arrivée</label>
                            <input type="date" class="form-control" id="date_arrive" 
                                   name="date_arrive" value="<?= htmlspecialchars($filters['date_arrive']) ?>" 
                                   placeholder="JJ/MM/AAAA">
                        </div>
                        
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100" title="Appliquer les filtres">
                                <i class="bi bi-funnel"></i>
                            </button>
                        </div>

                        <div class="col-md-1">
                            <a href="?" class="btn btn-secondary w-100">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Tableau des factures -->
                <div class="table-responsive mt-3">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">N° Courrier</th>
                                <th class="text-center">Date arrivée</th>
                                <th class="text-center">Expéditeur</th>
                                <th class="text-center">Décade</th>
                                <th class="text-center">N° Facture</th>
                                <th class="text-center">Montant TTC</th>
                                <th class="text-center">ENTITE/FACTURE</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody >
                            <?php if (empty($factures)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Aucune facture trouvée</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($factures as $facture): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($facture['numero_courrier']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($facture['date_arrive'])) ?></td>
                                        <td><?= htmlspecialchars($facture['expediteur']) ?></td>
                                        <td><?= htmlspecialchars($facture['decade']) ?></td>
                                        <td><?= htmlspecialchars($facture['numero_facture']) ?></td>
                                        <td><?= number_format($facture['montant_ttc'], 2, ',', ' ') ?> FCFA</td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill bg-<?= 
                                                strtolower($facture['type_facture']) === 'avoir' ? 'success' : 
                                                (strtolower($facture['type_facture']) === 'proforma' ? 'warning text-dark' : 'primary') 
                                            ?>">
                                                <?= htmlspecialchars($facture['type_facture']) ?>
                                            </span>
                                            <?php if (!empty($facture['facture_pdf'])): ?>
                                                <a href="<?= htmlspecialchars($facture['facture_pdf']) ?>" 
                                                   target="_blank"
                                                   class="btn btn-success btn-sm-pdf" 
                                                   title="PDF">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="action-buttons">
                                           
                                            <a href="modifier_facture.php?id=<?= $facture['id_facture'] ?>"  
                                               class="btn btn-primary btn-sm" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                           
                                            <?php if (!isset($_SESSION['Fonction']) || $_SESSION['Fonction'] !== 'assistant_courrier'): ?>
                                                <a href="supprimer_facture.php?id=<?= $facture['id_facture'] ?>" 
                                                   class="btn btn-danger btn-sm-delete" 
                                                   title="Supprimer"
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette facture?')">
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

                <!-- Pagination améliorée -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Navigation des factures" class="mt-4">
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
                            $startPage = max(1, min($page - 2, $total_pages - 4));
                            $endPage = min($total_pages, $startPage + 4);
                            
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
                            
                            if ($endPage < $total_pages): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            
                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                            
                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $total_pages])) ?>">
                                    <i class="bi bi-chevron-double-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function() {
            // Configuration de Flatpickr pour les dates
            flatpickr(".datepicker", {
                dateFormat: "d/m/Y",
                allowInput: true,
                locale: "fr"
            });

         });   
    </script>
</body>
</html>