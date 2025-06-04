<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    exit();
}

// Connexion à la base de données et inclusion des fonctions nécessaires
require('../../../traitement/fonction.php');
require('../../../traitement/requete.php');
require('../../../traitement/traitOneDepart.php');

$search = $_GET['search'] ?? ''; // Terme de recherche
$page = $_GET['page'] ?? 1; // Page actuelle
$results_per_page = 10; // Nombre de résultats par page

$totalCourriers = getTotaleCourriers($search);
$totalPages = ceil($totalCourriers / $results_per_page);

$courriers = getCourriersByRole($search, $page, $results_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: GESTION_IMPUTATION</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/base.css" />
    <link rel="stylesheet" href="../../../assets/css/main.css" />
    <link rel="stylesheet" href="../../assets/css/datatables.min.css">
    <link rel="stylesheet" href="../../../assets/css/styles.css">
    <link rel="stylesheet" href="../../../assets/css/tableau.css">
    <link rel="stylesheet" href="../../../assets/bootstrap/js/bootstrap.min.js">
    <!-- Lien vers le CSS de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Lien vers les icônes Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Lien vers les icônes Fontawesome -->
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
    /* Agrandir la taille des écritures dans le champ de recherche */
    .search-container .box input[type="text"] {
        font-size: 18px; /* Augmente la taille de la police */
     
    }

    /* Optionnel : Ajuster la taille de l'icône */
   
</style>
</head>
<body>
    <?php include('../../../head.php'); ?>
    <!-- Contenu principal -->
    <div class="container col-sm-6" style="background-color: #b0cdee">
        <div class="row">
            <div class="card-header" style="background-color: #0056B3;">
                <h1 class="text-white">COURRIERS IMPUTES AU DEPARTEMENT "<?= $_SESSION['subrole'] ?>"</h1>
            </div>
        </div>
    </div>

    <!-- Formulaire de recherche -->
    <div class="search-container">
        <form method="GET" action="">
            <div class="box custom-div-rounded">
                <input type="text"class="form-control" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Rechercher ...">
            </div>
        </form>
    </div>

    <div class="container shadow-lg" style="background-color: #0056B3;">
        <div class="row">
            <div class="col-12">
                <div class="data_table">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Date</th>
                                <th>Objet</th>
                                <th>Nature</th>
                                <th>Type</th>
                                <th>Instruction</th>
                                <th>PDF</th>
                                <th>GÉRER TÂCHE</th>
                                <th>VOIR</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($courriers as $courrier): ?>
                            <tr style="font-size:20px;">
                                <td  class="text-center pt-4"><?= htmlspecialchars($courrier['Numero_Courrier']); ?></td>
                                <td class="text-center pt-4"><?= htmlspecialchars($courrier['date_impu']); ?></td>
                                <td class="text-center pt-4"><?= htmlspecialchars($courrier['Objet']); ?></td>
                                <td class="text-center pt-4"><?= htmlspecialchars($courrier['Nature']); ?></td>
                                <td class="text-center pt-4"><?= htmlspecialchars($courrier['Type']); ?></td>
                                <td class="text-center pt-4">
                                <?php 
                                      // Affiche l'instruction personnalisée si elle est disponible, sinon l'instruction générale
                                     echo htmlspecialchars(!empty($courrier['instruction_personnalisee']) ? $courrier['instruction_personnalisee'] : $courrier['Instruction']);
                                  ?>
                                </td>
                                <td class="text-center pt-4">
                                    <a href="../../uploads/<?= htmlspecialchars($courrier['pdf']); ?>" target="_blank" class="btn btn-warning">
                                        <i class="bi bi-file-earmark-pdf-fill"></i>
                                    </a>
                                </td>
                                <td class="text-center pt-4">
                                    <form action="../tache.php" method="GET" style="display:inline;">
                                        <input type="hidden" name="instruction" value="<?= htmlspecialchars($courrier['Instruction']); ?>">
                                        <input type="hidden" name="instruction_p" value="<?= htmlspecialchars($courrier['instruction_personnalisee']); ?>">
                                        <input type="hidden" name="id_imputation" value="<?= htmlspecialchars($courrier['id_imputation']); ?>">
                                        <input type="hidden" name="Numero_Courrier" value="<?= htmlspecialchars($courrier['Numero_Courrier']); ?>">
                                        <input type="hidden" name="objet" value="<?= htmlspecialchars($courrier['Objet']); ?>">
                                        <button type="submit" class="btn btn-secondary" style="background-color: #3777b0;">Gérer Tâche</button>
                                    </form>
                                </td>
                                <td class="text-center pt-4">
                                    <a href="adetailDepCell.php?id_imputation=<?= htmlspecialchars($courrier['id_imputation']); ?>" class="btn btn-success">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                  <!-- Pagination Links -->
                </div>
            </div>
        </div>
       <nav aria-label="Pagination">
    <ul class="pagination justify-content-center">
        <!-- Lien vers la page précédente -->
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $page - 1; ?>">Précédent</a>
            </li>
        <?php endif; ?>

        <!-- Lien vers toutes les pages -->
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
            </li>
        <?php endfor; ?>

        <!-- Lien vers la page suivante -->
        <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $page + 1; ?>">Suivant</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

   
    
 

    <!-- Scripts JavaScript -->
    <script src="../../../assets/js/search_update.js"></script>                   
    <script src="../../../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../../../assets/js/plugins.js"></script>
    <script src="../../../assets/js/main.js"></script>
</body>
</html>
