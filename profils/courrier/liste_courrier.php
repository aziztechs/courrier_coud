<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

// Connexion à la base de données
require('../../traitement/fonction.php');
include('../../activite.php');
require('../../traitement/recupImpuAlldep.php');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
$nature_filter = isset($_GET['nature_filter']) ? $_GET['nature_filter'] : '';
$type_filter = isset($_GET['type_filter']) ? $_GET['type_filter'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 5;

// Validate page number
if ($page < 1) {
    $page = 1;
}

$filters = [
    'search' => $_GET['search'] ?? '',
    'nature' => $_GET['nature'] ?? '',
    'type' => $_GET['type'] ?? '',
    'date_debut' => $_GET['date_debut'] ?? '',
    'date_fin' => $_GET['date_fin'] ?? ''
];

$page = $_GET['page'] ?? 1;
$itemsPerPage = 5;

$courriers = getFilteredCourriers($filters, $page, $itemsPerPage);
$totalCourriers = countFilteredCourriers($filters);


$totalPages = ceil($totalCourriers / $itemsPerPage);

// Adjust page if it's beyond total pages
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
    // Optionally redirect to the last valid page
    $query_params = http_build_query([
        'page' => $page,
        'search' => $search,
        'date_filter' => $date_filter,
        'nature_filter' => $nature_filter,
        'type_filter' => $type_filter
    ]);
    header("Location: ?$query_params");
    exit();
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
    <div class="info-banner  text-white d-flex justify-content-center align-items-center"
         style="height: 120px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Bienvenue !<br>
                <span>
                    (<?php echo $_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']; ?>)
                </span>
            </p>
        </div>
    </div>  
     

    <div class="container-table mt-4">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                <h1 class="h2">Liste des Courriers</h1>
                <div>
                    <a href="accueil_courrier.php" target="_blank" class="btn btn-sm btn-success">
                        <i class="fa fa-add"></i> Ajouter Courrier
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <form method="get" action="liste_courrier.php" class="mb-4">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Recherche..." 
                                value="<?= htmlspecialchars($filters['search']) ?>" style="width: 100%; height: 40px;">
                        </div>
                        <div class="col-md-2">
                            <select name="nature" class="form-select">
                                <option value="">Toutes natures</option>
                                <option value="arrive" <?= ($filters['nature'] === 'arrive') ? 'selected' : '' ?>>Arrivé</option>
                                <option value="depart" <?= ($filters['nature'] === 'depart') ? 'selected' : '' ?>>Départ</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="type" class="form-select">
                                <option value="">Tous types</option>
                                <option value="interne" <?= ($filters['type'] === 'interne') ? 'selected' : '' ?>>Interne</option>
                                <option value="externe" <?= ($filters['type'] === 'externe') ? 'selected' : '' ?>>Externe</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_debut" class="form-control" 
                                value="<?= htmlspecialchars($filters['date_debut']) ?>" placeholder="Date début" style=" height: 40px;"> 
                        </div>

                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100" style="height: 40px;">Filtrer</button>
                        </div>

                        <div class="col-md-2">
                            <a href="?" class="btn btn-secondary w-100" style="height: 40px;">
                                <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                            </a>
                        </div>
                    </div>
                
                </form>
                    
                <div class="table-responsive mt-3">
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
                                    <td colspan="8" class="text-center py-4">Aucun courrier trouvé</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($courriers as $courrier): ?>
                                    <tr>
                                        <td class="text-center" data-label="Numéro"><?= htmlspecialchars($courrier['Numero_courrier']) ?></td>
                                        <td class="text-center" data-label="Date"><?= htmlspecialchars($courrier['date']) ?></td>
                                        <td class="text-center" data-label="Objet"><?= htmlspecialchars($courrier['Objet']) ?></td>
                                        <td class="text-center" data-label="Nature"><?= htmlspecialchars($courrier['Nature']) ?></td>
                                        <td class="text-center" data-label="Type"><?= htmlspecialchars($courrier['Type']) ?></td>
                                        <td class="text-center" data-label="Expéditeur"><?= htmlspecialchars($courrier['Expediteur']) ?></td>
                                        <td class="text-center" data-label="PDF">
                                            <a href="../uploads/<?= htmlspecialchars($courrier['pdf']) ?>" 
                                                target="_blank" 
                                                class="btn btn-warning btn-sm" 
                                                title="Voir le PDF">
                                                <i class="bi bi-file-earmark-pdf-fill"></i>
                                            </a>
                                        </td>
                                        <td class="text-center" data-label="Actions">
                                            <div class="btn-group" role="group">
                                                <a href="mise_a_jour1.php?id=<?= htmlspecialchars($courrier['id_courrier']) ?>" 
                                                    class="btn btn-primary btn-sm" 
                                                    title="Modifier">
                                                    <i class="bi bi-pencil"></i>
                                                </a>&nbsp;
                                                <button class="btn btn-danger btn-sm delete-btn" 
                                                        data-id="<?= htmlspecialchars($courrier['id_courrier']) ?>" 
                                                        title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
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
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query([
                                        'page' => $page - 1,
                                        'search' => $search,
                                        'date_filter' => $date_filter,
                                        'nature_filter' => $nature_filter,
                                        'type_filter' => $type_filter
                                    ]); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?= http_build_query([
                                        'page' => $i,
                                        'search' => $search,
                                        'date_filter' => $date_filter,
                                        'nature_filter' => $nature_filter,
                                        'type_filter' => $type_filter
                                    ]); ?>"><?= $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query([
                                        'page' => $page + 1,
                                        'search' => $search,
                                        'date_filter' => $date_filter,
                                        'nature_filter' => $nature_filter,
                                        'type_filter' => $type_filter
                                    ]); ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr pour le sélecteur de date -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script src="../../assets/js/plugins.js"></script>
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/script.js"></script>
    <script src="../../assets/js/search_update.js"></script>
    
    <script>
    $(document).ready(function() {
        // Initialisation du datepicker
        $(".datepicker").flatpickr({
            dateFormat: "Y-m-d",
            locale: "fr",
            allowInput: true
        });

        // Confirmation de suppression
        $('.delete-btn').click(function() {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce courrier ?')) {
                const id = $(this).data('id');
                window.location.href = 'supprimer.php?id=' + id;
            }
        });
    });
    </script>
</body>
</html>
