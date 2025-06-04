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

$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;

$courriers = recupererTousLesCourriers($search, $page, $itemsPerPage);
$totalCourriers = getTotalCourriers($search);
$totalPages = ceil($totalCourriers / $itemsPerPage);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil Direction - Gestion Courrier</title>
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/base.css" />
    <!-- <link rel="stylesheet" href="../../assets/css/vendor.css" /> -->
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/datatables.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/tableau.css">
    <link rel="stylesheet" href="../../assets/bootstrap/js/bootstrap.bundle.min.js">
    <!-- Lien vers le CSS de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Lien vers les icônes Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Lien vers les icônes Fontawesome -->
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <?php include('../../head.php'); ?>
    <div class="container col-sm-6" style="background-color: #b0cdee">
        <div class="row">
            <div class="card-header" style="background-color: #0056B3;">
                <h1 class="text-white">LISTE DES COURRIERS</h1>
            </div>
        </div>
    </div>
    
       <!-- Formulaire de recherche -->
    <div class="search-container ">
        <form method="GET" action="">
            <div class="box custom-div-rounded">
                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Rechercher ..." >
            </div>
        </form>
    </div>
    
    <div class="container shadow-lg" style="background-color: #0056B3;">
        <div class="row">
            <div class="col-12">
                <div class="data_table">
                    <table  class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Courrier</th>
                                <th >Date</th>
                                <th>Objet</th>
                                <th>Nature</th>
                                <th>Type</th>
                                <th>Expediteur</th>
                                <th>PDF</th>
                                <th>IMPUTATION</th>
                                <!--th>MODIFICATION</th-->
                                <th>Suivre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($courriers)): ?>
                                <?php foreach ($courriers as $courrier):?>
                            <tr style="font-size:20px;">
                                <td class="text-center pt-4" style="text-align: center;"><?= htmlspecialchars($courrier['Numero_courrier'] ?? ''); ?></td>
                                <td class="text-center pt-4" style="text-align: center;"><?= htmlspecialchars($courrier['date'] ?? ''); ?></td>
                                <td class="text-center pt-4" style="text-align: center;"><?= htmlspecialchars($courrier['Objet'] ?? ''); ?></td>
                                <td class="text-center pt-4" style="text-align: center;"><?= htmlspecialchars($courrier['Nature'] ?? ''); ?></td>
                                <td class="text-center pt-4" style="text-align: center;"><?= htmlspecialchars($courrier['Type'] ?? ''); ?></td>
                                <td class="text-center pt-4" style="text-align: center;"><?= htmlspecialchars($courrier['Expediteur'] ?? ''); ?></td>
                                <td class="text-center pt-4" style="text-align: center;">
                                    <?php if (!empty($courrier['pdf'])): ?>
                                    <a href="../uploads/<?= htmlspecialchars($courrier['pdf']); ?>"
                                       target="_blank" type="button" class="btn btn-warning">
                                        <i class="bi bi-file-earmark-pdf-fill"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pt-4" style="text-align: center;">
                                    <a href="handleSession.php?id_courrier=<?= htmlspecialchars($courrier['id_courrier']); ?>" 
                                        class="btn btn-primary">
                                        <i class="fa fa-share-square" aria-hidden="true"></i>
                                    </a>
                                    
                                </td>
                                <!--td>
                                    <?php if (!empty($courrier['id_imputation'])): ?>
                                        <a href="modifImputation.php?id_impu=<?= htmlspecialchars($courrier['id_imputation']); ?>" 
                                            class="btn btn-info">
                                            <i class="fa fa-wrench" aria-hidden="true"></i>&nbsp;
                                        </a>
                                    <?php endif; ?>
                                </td-->
                                <td class="text-center pt-4" style="text-align: center;">
                                    <?php if (!empty($courrier['id_imputation'])): ?>
                                        <a href="suivi.php?id_courrierA=<?= htmlspecialchars($courrier['id_courrier']); ?>" 
                                            class="btn btn-success">
                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                        </a>
                                        
                                    <?php else: ?>
                                        <a href="" 
                                        class="btn btn-danger">
                                            <i class="fa-solid fa-xmark" ria-hidden="true"></i>&nbsp;
                                            Non imputé
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="6">Aucun courrier trouvé.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>
        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?= $i; ?>&search=<?= htmlspecialchars($search); ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
              
     <!-- Inclusion du fichier JavaScript -->
    <script src="../../assets/js/search_update.js"></script>                   
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/plugins.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
<script src="../../assets/js/script.js"></script>

</html>
