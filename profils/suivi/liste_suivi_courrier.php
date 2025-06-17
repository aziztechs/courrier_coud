<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}
require_once('../../traitement/fonction_suivi_courrier.php');

// Récupération des paramètres de filtrage
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_reception = isset($_GET['date_reception']) ? $_GET['date_reception'] : '';
$statut_filter = isset($_GET['statut']) ? $_GET['statut'] : '';

// Construction de la requête SQL avec filtres
$sql = "SELECT * FROM suivi_courrier WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $sql .= " AND (numero LIKE ? OR expediteur LIKE ? OR objet LIKE ? OR destinataire LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
    $types .= 'ssss'; // 4 strings
}

if (!empty($date_reception)) {
    $sql .= " AND DATE(date_reception) = ?";
    $params[] = $date_reception;
    $types .= 's'; // 1 string (date)
}

if (!empty($statut_filter)) {
    $sql .= " AND (statut_1 = ? OR statut_2 = ? OR statut_3 = ?)";
    $params = array_merge($params, [$statut_filter, $statut_filter, $statut_filter]);
    $types .= 'sss'; // 3 strings
}

$sql .= " ORDER BY date_reception DESC";

// Pagination
$results_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $results_per_page;

// Requête pour le nombre total de résultats
$count_sql = str_replace('SELECT *', 'SELECT COUNT(*) as total', $sql);
$stmt = $connexion->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_results = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $results_per_page);

// Requête pour les résultats paginés
$sql .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $results_per_page;
$types .= 'ii'; // 2 integers pour LIMIT

$stmt = $connexion->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$suivis = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COURRIER COUD - GESTION SUIVI</title>
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/styleCourrier.css">
    <link rel="stylesheet" href="css/form.css">
    <link rel="stylesheet" href="css/liste.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Flatpickr pour le sélecteur de date -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
</head>
<body>
    <?php include('../../head.php'); ?>
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 120px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Gestion des Suivis !<br>
                <span>
                    (<?php echo $_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']; ?>)
                </span>
            </p>
        </div>
    </div> 
    
    <div class="container-fluid mt-3"> <!-- Marge top réduite -->
        <div class="row">
            <div class="col-md-12">
               
                    <div class="card">
                        <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white py-2"> <!-- Padding vertical réduit -->
                            <h1 class="h5 mb-0"><i class="fas fa-list"></i> Liste des suivis</h1> <!-- Taille de titre réduite -->
                            <div>
                                <a href="ajout_suivi.php" class="btn btn-light  text-primary btn-sm"> <!-- Bouton plus petit -->
                                    <i class="fas fa-plus"></i> Ajouter suivi
                                </a>
                            </div>
                        </div>
                        
                        <div class="card-body"> <!-- Padding réduit -->
                             <!-- Filtres -->
                            <div class="card filter-card mb-4">
                                <div class="card-body">
                                    <form method="GET" class="row g-2"> <!-- Gutter réduit -->
                                        <div class="col-md-4">
                                            <label for="search" class="form-label mb-1">Recherche</label> <!-- Marge bottom réduite -->
                                            <input type="text" class="form-control form-control-sm" id="search" name="search" 
                                                value="<?= htmlspecialchars($search) ?>" placeholder="Numéro, Expéditeur, Objet...">
                                        </div>
                                        
                                        
                                        <div class="col-md-3">
                                            <label for="statut" class="form-label mb-1">Statut</label>
                                            <select class="form-select form-select-sm" id="statut" name="statut">
                                                <option value="">Tous les statuts</option>
                                                <option value="CSA" <?= $statut_filter == 'CSA' ? 'selected' : '' ?>>CSA</option>
                                                <option value="CHRONO" <?= $statut_filter == 'CHRONO' ? 'selected' : '' ?>>Chrono</option>
                                                <option value="En Cours" <?= $statut_filter == 'En Cours' ? 'selected' : '' ?>>En cours</option>
                                                <option value="En attente" <?= $statut_filter == 'En attente' ? 'selected' : '' ?>>En attente</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="date_reception" class="form-label mb-1">Date de réception</label>
                                            <input type="text" class="form-control form-control-sm" id="date_reception" name="date_reception" 
                                                value="<?= htmlspecialchars($date_reception) ?>" placeholder="JJ-MM-AAAA">
                                        </div>
                                        
                                        <div class="col-md-1 "> <!-- Alignement vertical réduit -->
                                            <label>Filtre</label>
                                            <button type="submit" class="btn btn-primary w-100"> <!-- Bouton plus petit -->
                                                <i class="bi bi-funnel"></i> 
                                            </button> 
                                        </div>
                                        <div class="col-md-1 ">
                                            <label>Réinitialisation</label>
                                            <a href="?" class="btn btn-secondary w-100">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="table-responsive m-2 m"> <!-- Marge bottom réduite -->
                                <table class="table table-striped table-hover table-bordered mb-2"> <!-- Marge bottom réduite -->
                                <thead class="table-primary">
                                    <tr>
                                        <th class="text-center">N° Courrier</th>
                                        <th class="text-center">Date réception</th>
                                        <th class="text-center">Expéditeur</th>
                                        <th class="text-center">Objet</th>
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
                                            <td colspan="9" class="text-center py-1">Aucun suivi trouvé</td> <!-- Padding vertical réduit -->
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($suivis as $suivi): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($suivi['numero']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($suivi['date_reception'])) ?></td>
                                                <td><?= htmlspecialchars($suivi['expediteur']) ?></td>
                                                <td><?= htmlspecialchars($suivi['objet']) ?></td>
                                                <td><?= htmlspecialchars($suivi['destinataire']) ?></td>
                                                <td class="text-center">
                                                    <?php if ($suivi['statut_1'] == 'CSA'): ?>
                                                        <span class="badge text-white badge-status">CSA</span>
                                                    <?php elseif ($suivi['statut_1'] == 'CHRONO'): ?>
                                                        <span class="badge text-white badge-status">Chrono</span>
                                                    <?php elseif ($suivi['statut_1'] == 'En Cours'): ?>
                                                        <span class="badge text-white badge-status">En cours</span>
                                                    <?php elseif ($suivi['statut_1'] == 'En attente'): ?>
                                                        <span class="badge text-white badge-status">En attente</span>
                                                    <?php else: ?>
                                                        <span class="badge text-white text-white   badge-status">Inconnu</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($suivi['statut_2'] == 'CSA'): ?>
                                                        <span class="badge text-white  badge-status">CSA</span>
                                                    <?php elseif ($suivi['statut_2'] == 'CHRONO'): ?>
                                                        <span class="badge text-white badge-status">Chrono</span>
                                                    <?php elseif ($suivi['statut_2'] == 'En Cours'): ?>
                                                        <span class="badge text-white badge-status">En cours</span>
                                                    <?php elseif ($suivi['statut_2'] == 'En attente'): ?>
                                                        <span class="badge text-white badge-status">En attente</span>
                                                    <?php else: ?>
                                                        <span class="badge  text-white badge-status">Inconnu</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($suivi['statut_3'] == 'CSA'): ?>
                                                        <span class="badge text-white  badge-status">CSA</span>
                                                    <?php elseif ($suivi['statut_3'] == 'CHRONO'): ?>
                                                        <span class="badge text-white badge-status">Chrono</span>
                                                    <?php elseif ($suivi['statut_3'] == 'En Cours'): ?>
                                                        <span class="badge text-white  badge-status">En cours</span>
                                                    <?php elseif ($suivi['statut_3'] == 'En attente'): ?>
                                                        <span class="badge text-white badge-status">En attente</span>
                                                    <?php else: ?>
                                                        <span class="badge text-white text-dark badge-status">Inconnu</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center action-buttons">
                                                    <a href="view_suivi.php?id=<?= $suivi['id'] ?>" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="modif_suivi.php?id=<?= $suivi['id'] ?>" 
                                                       class="btn btn-primary btn-sm" 
                                                       title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if (!isset($_SESSION['Fonction']) || $_SESSION['Fonction'] !== 'assistant_courrier'): ?>
                                                    <a href="supprimer_suivi.php?id=<?= $suivi['id'] ?>" 
                                                       class="btn btn-danger btn-sm" 
                                                       title="Supprimer"
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce suivi?')">
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
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center pagination-sm mb-1"> <!-- Pagination plus petite -->
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" aria-label="First">
                                        <span aria-hidden="true"><i class="fas fa-angle-double-left"></i></span>
                                    </a>
                                </li>
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" aria-label="Previous">
                                        <span aria-hidden="true"><i class="fas fa-angle-left"></i></span>
                                    </a>
                                </li>
                                
                                <?php 
                                // Afficher un nombre limité de pages autour de la page courante
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if ($start_page > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '">1</a></li>';
                                    if ($start_page > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }
                                
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                                    echo '<a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => $i])) . '">' . $i . '</a>';
                                    echo '</li>';
                                }
                                
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => $total_pages])) . '">' . $total_pages . '</a></li>';
                                }
                                ?>
                                
                                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" aria-label="Next">
                                        <span aria-hidden="true"><i class="fas fa-angle-right"></i></span>
                                    </a>
                                </li>
                                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" aria-label="Last">
                                        <span aria-hidden="true"><i class="fas fa-angle-double-right"></i></span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
   <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialisation de flatpickr pour le champ de date
            flatpickr("#date_reception", {
                dateFormat: "Y-m-d",
                allowInput: true
            });
        });
    </script>
</body>
</html>