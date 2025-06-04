<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    exit();
}

// Connexion à la base de données
require('../../../traitement/fonction.php');
require('../../../traitement/requete.php');

$id_imputation = $_GET['id_imputation'] ?? null;

if ($id_imputation) {
    // Préparation de la requête pour obtenir toutes les tâches liées à l'imputation
    $query = "
        SELECT s.date_suivi, s.statut, s.id_suivi, s.pdf
        FROM suivi s
        WHERE s.id_imputation = ?
        ORDER BY s.date_suivi DESC;
    ";

    $stmt = $connexion->prepare($query);
    $stmt->bind_param("i", $id_imputation);
    $stmt->execute();
    $result = $stmt->get_result();
    $suivis = [];
    while ($row = $result->fetch_assoc()) {
        $suivis[] = $row;
    }
    $stmt->close();
} else {
    echo "Identifiant d'imputation non spécifié.";
    exit;
}

if (isset($_GET['idT'])) {
    $id = $_GET['idT'];
    // Vous devez définir `$date_suivi`, `$statut`, `$pdf` avant de les passer à `mettreAJourTache`
    // Assurez-vous que ces valeurs sont définies ou récupérées
    $date_suivi = $_POST['date_suivi'] ?? null;
    $statut = $_POST['statut'] ?? null;
    $pdf = $_POST['pdf'] ?? null;
    mettreAJourTache($id, $date_suivi, $statut, $pdf);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: GESTION_COURRIERS</title>
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/base.css" />
    <link rel="stylesheet" href="../../../assets/css/main.css" />
    <link rel="stylesheet" href="../../assets/css/datatables.min.css">
    <link rel="stylesheet" href="../../../assets/css/styles.css">
    <link rel="stylesheet" href="../../../assets/css/tableau.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
    <?php include('../../../head.php'); ?>
    <div class="d-flex justify-content-end">
        <div class="btn-group">
            <button class="btn btn-primary" style="font-size: 15px;" onclick="javascript:history.back()">
                <i class="fa fa-repeat" aria-hidden="true"></i>&nbsp;Retour
            </button>
        </div>
    </div>

    <div class="container col-sm-6" style="background-color: #b0cdee">
        <div class="row">
            <div class="card-header" style="background-color: #0056B3;">
                <h1 class="text-white">DETAILS TÂCHES</h1>
            </div>
        </div>
    </div>
    
    <div class="container shadow-lg" style="background-color: #0056B3;">
        <div class="row">
            <div class="col-12">
                <div class="data_table">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>PDF</th>
                                <th>METTRE A JOUR</th>
                                <th>SUPPRIMER</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suivis as $suivi): ?>
                                <tr>
                                    <td class="text-center pt-4"><?= htmlspecialchars($suivi['date_suivi']); ?></td>
                                    <td class="text-center pt-4"><?= htmlspecialchars($suivi['statut']); ?></td>
                                    <td class="text-center pt-4">
                                        <a href="../../uploads/<?= htmlspecialchars($suivi['pdf']); ?>"
                                            target="_blank" class="btn btn-warning">
                                            <i class="bi bi-file-earmark-pdf-fill"></i>
                                        </a>
                                    </td>
                                    <td class="text-center pt-4">
                                        <a href="miseAjourTache.php?idT=<?= htmlspecialchars($suivi['id_suivi']); ?>"
                                           class="btn btn-primary">
                                           <i class="fa fa-pencil" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                    <td class="text-center pt-4">
                                        <a href="#" class="btn btn-danger" onclick="confirmDeletion('<?= htmlspecialchars($suivi['id_suivi']); ?>', '<?= $_SESSION['Fonction']; ?>')">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmDeletion(idsT, func, role) {
        Swal.fire({
            title: 'Êtes-vous sûr?',
            text: "Cette action est irréversible!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer!',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "../../../traitement/deleteTache.php?idsT=" + idsT  + "&role=" + role;
            }
        });
    }
    </script>

    <script src="../../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../../assets/js/plugins.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>
