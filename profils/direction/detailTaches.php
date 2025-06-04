<?php
// Démarre une nouvelle session ou reprend une session existante
    session_start();
    if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
        header('Location: /courrier_coud/');
        session_destroy();
        exit();
    }
    //connexion à la base de données
    require('../../traitement/fonction.php');
    // Sélectionnez les options à partir de la base de données avec une pagination
    require('../../traitement/requete.php');
    require('../../traitement/traitementSuivi.php');
    $id_imputation = $_GET['id_imputation'] ?? null;

    if ($id_imputation) {
        // Préparez la requête pour obtenir toutes les tâches liées à l'imputation
        $query = "
            SELECT s.date_suivi, s.statut,s.pdf , c.Numero_Courrier, i.departement, c.Objet
            FROM suivi s
            JOIN
               imputation i ON i.id_imputation = s.id_imputation
            JOIN
               courrier c ON c.id_courrier = i.id_courrier
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: GESTION_COURRIER</title>
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
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
                <h1 class="text-white">DETAILS DE L'EVOLUTION</h1>
            </div>
        </div>
    </div>
    <div class="container" style="background-color: #fff;">
    <!-- <div class="row">
        <?php foreach ($suivis as $suivi): ?>
            <div class="col-sm-4 text-center pt-4" style="text-align: center; margin-bottom: 10px;">
                <h3 class="text-dark">Département : </h3>
                <span style="color: black !important;"><?= htmlspecialchars($suivi['departement']); ?></span>
            </div>
            <div class="col-sm-4 text-center pt-4" style="text-align: center; margin-bottom: 10px; color: black;">
                <h3 class="text-dark">Numero_Courrier : </h3>
                <span style="color: black !important;"><?= htmlspecialchars($suivi['Numero_Courrier']); ?></span>
            </div>
            <div class="col-sm-4 text-center pt-4" style="text-align: center; margin-bottom: 10px;">
                <h3 class="text-dark">Objet : </h3>
                <span style="color: black !important;"><?= htmlspecialchars($suivi['Objet']); ?></span>
            </div>
        <?php endforeach; ?>
    </div> -->
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suivis as $suivi): ?>
                                <tr>
                                    <td class="text-center pt-4" style="text-align: center;"><?= htmlspecialchars($suivi['date_suivi']); ?></td>
                                    <td class="text-center pt-4" style="text-align: center;"><?= htmlspecialchars($suivi['statut']); ?></td>
                                    <td class="text-center pt-4" style="text-align: center;">
                                    <?php if (!empty($suivi['pdf'])): ?>
                                    <a href="../uploads/<?= htmlspecialchars($suivi['pdf']); ?>"
                                       target="_blank" type="button" class="btn btn-warning">
                                        <i class="bi bi-file-earmark-pdf-fill"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-center">
        <div class="btn-group">
            <button class="btn btn-primary" style="font-size: 15px;" onclick="javascript:history.back()">
                <i class="fa fa-repeat" aria-hidden="true"></i>&nbsp;Retour
            </button>
        </div>
    </div>                            
    <script src="../../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../../assets/js/plugins.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>
