<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}
include '../../traitement/fonction_user.php';
include '../../traitement/connect.php';

$limit = 5; // Nombre d'utilisateurs par page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$totalUsers = getAllUsersCount();
$totalPages = ceil($totalUsers / $limit);

$users = getAllUsersPaginated($limit, $offset);
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search) {
    $users = searchUsers($search, $limit, $offset);
    $totalUsers = getAllUsersCount();
    $totalPages = ceil($totalUsers / $limit);
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
    <!-- Flatpickr pour le sélecteur de date -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

</style>
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
            <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white" >
                <h1 class="text-center"><i class="fa fa-users"></i>&nbsp; LISTE DES UTILISATEIRS</h1>
                <div>
                    <a href="addUser.php" target="_blank" class="btn btn-sm btn-success">
                        <i class="fa fa-add"></i> Ajouter Utilisateur
                    </a>
                </div>
            </div>
            <form method="get" class="row mt-3">
                <div class="col-md-10 offset-md-1">
                    <div class="d-flex">
                        <input type="text" name="search" class="form-control rounded-end-0" placeholder="Rechercher un utilisateur..." value="<?= htmlspecialchars($search) ?>"
                         style="width: 100%; height: 40px;">
                        <button type="submit" class="btn btn-primary rounded-start-0" style="height: 40px;">
                            <i class="bi bi-search"></i> 
                        </button>
                    </div>
                </div>
            </form>

            <div class="card-body">
             <div class="table-responsive ">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th  class="text-center">Nom</th>
                            <th  class="text-center">Prénom</th>
                            <th  class="text-center">Username</th>
                            <th  class="text-center">Email</th>
                            <th  class="text-center">Téléphone</th>
                            <th  class="text-center">Fonction</th>
                            <th  class="text-center">Matricule</th>
                            <th  class="text-center">Actif</th>
                            <th  class="text-center">Actions</th> <!-- Nouvelle colonne -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = mysqli_fetch_assoc($users)) : ?>
                        <tr style="text-align: center; font-size: 1.3em; ">
                            <td class="text-center"><?= htmlspecialchars($user['Nom']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($user['Prenom']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($user['Username']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($user['Tel']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($user['Fonction']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($user['Matricule']) ?></td>
                            <td class="text-center"><?= $user['Actif'] ? 'Oui' : 'Non' ?></td>
                            <td class="text-center">
                                <a href="editUser.php?id=<?= $user['id_user'] ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil"></i>&nbsp;
                                </a>
                                <a href="delete.php?id=<?= $user['id_user'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');">
                                <i class="bi bi-trash"></i>&nbsp; 
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>          
            </div>                
        </div> 
    </div>

    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?>">Précédent</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?>">Suivant</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

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


