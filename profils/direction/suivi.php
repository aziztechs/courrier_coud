<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

// Connexion à la base de données et inclusion des fichiers nécessaires
require('../../traitement/fonction.php');
require('../../traitement/recupImpuAlldep.php');
require('../../traitement/traitOneDepart.php');

// Fonction pour vérifier si un courrier a des suivis
function hasSuivi($id_imputation) {
    global $connexion;
    $query = "SELECT COUNT(*) AS count FROM suivi WHERE id_imputation = ?";
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("i", $id_imputation);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] > 0;
}

// Récupérer l'ID d'imputation
$id_imputation = isset($_GET['id_imputation']) ? (int)$_GET['id_imputation'] : 0;

if ($id_imputation > 0) {
    // Vérifiez s'il y a déjà un suivi pour ce courrier
    if (!hasSuivi($id_imputation)) {
        // Si non, procéder à la suppression
        $stmt = $connexion->prepare("DELETE FROM imputation WHERE id_imputation = ?");
        $stmt->bind_param("i", $id_imputation);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: /courrier_coud/profils/direction/accueil_direction.php"); // Rediriger vers la page de succès
            exit();
        } else {
            $stmt->close();
            header("Location: /courrier_coud/somePage.php?message=error"); // En cas d'erreur
            exit();
        }
    } else {
        // Si le courrier a déjà un suivi, rediriger avec un message d'erreur
        header("Location: /courrier_coud/somePage.php?message=has_suivi");
        exit();
    }
} 

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: GESTION_COURRIER</title>
    <!-- Vos liens CSS -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: GESTION_COURRIER</title>
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
    <link rel="icon" href="log.gif" type="image/x-icon">
    <!-- <link rel="stylesheet" href="../../assets/css/vendor.css" /> -->
    <link rel="stylesheet" href="../../assets/css/main.css" />
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/tableau.css">
    <link rel="stylesheet" href="../../assets/css/datatables.min.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <!-- Lien vers le CSS de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Lien vers les icônes Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Lien vers les icônes Fontawesome -->
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>
    <?php include('../../head.php');?>
    <div class="container col-sm-6" style="background-color: #b0cdee">
        <div class="row">
            <div class="card-header" style="background-color: #0056B3;">
                <h1 class="text-white">SUIVRE L'EVOLUTION DES COURRIERS</h1>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end" style="margin-right: 300px">
        <div class="btn-group">
            <button class="btn btn-primary" style="font-size: 20px; background-color: #0056B3;" onclick="javascript:history.back()">
                <i class="fa fa-repeat" style="font-size: 20px;" aria-hidden="true"></i>&nbsp;Retour
            </button>
        </div>
    </div>

    <div class="container SHA" style="background-color: #0056B3;">
        <div class="row">
            <div class="col-12">
                <div class="data_table">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Numero_corrier</th>
                                <th>Département</th>
                                <th>Instruction</th>
                                <!-- <th>Évolution</th> -->
                                <th>Gérer Tache</th>
                                <th>Évolution</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suiv_par_departement as $departement => $suivis): ?>
                                <?php foreach ($suivis as $suivi): ?>
                                    <tr style="font-size: 15px;">
                                        <td class="text-center pt-4"><?= htmlspecialchars($suivi['Numero_Courrier']); ?></td>
                                        <td class="text-center pt-4"><?= htmlspecialchars($departement); ?></td>
                                        <td class="text-center pt-4">
                                            <?= htmlspecialchars(!empty($suivi['instruction_personnalisee']) ? $suivi['instruction_personnalisee'] : $suivi['Instruction']); ?>
                                        </td>
                                        <!-- <td class="text-center pt-4">
                                            <a class="btn btn-primary" href="detailTaches.php?id_imputation=<?= htmlspecialchars($suivi['id_imputation']); ?>">Évolution</a>
                                        </td> -->
                                        <!-- Ajoutez le bouton "Gérer Tâche" -->
                                        <td class="text-center pt-4">
                                            <form action="../departement/tache.php" method="GET" style="display:inline;">
                                                <input type="hidden" name="instruction" value="<?= htmlspecialchars($suivi['Instruction']); ?>">
                                                <input type="hidden" name="instruction_p" value="<?= htmlspecialchars($suivi['instruction_personnalisee']); ?>">
                                                <input type="hidden" name="id_imputation" value="<?= htmlspecialchars($suivi['id_imputation']); ?>">
                                                <input type="hidden" name="Numero_Courrier" value="<?= htmlspecialchars($suivi['Numero_Courrier']); ?>">
                                                <input type="hidden" name="objet" value="<?= htmlspecialchars($suivi['Objet']); ?>">
                                                <button type="submit" class="btn btn-secondary" style="background-color: #3777b0;">Gérer Tâche</button>
                                            </form>
                                        </td>
                                        <td class="text-center pt-4">
                                            <a href="../departement/pageDepCell/adetailDepCell.php?id_imputation=<?= htmlspecialchars($suivi['id_imputation']); ?>" class="btn btn-success">
                                                <i class="fa fa-eye" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                        <td class="text-center pt-4">
                                            <?php if (!hasSuivi($suivi['id_imputation'])): ?>
                                                <a class="btn btn-danger" href="suivi.php?id_imputation=<?= htmlspecialchars($suivi['id_imputation']); ?>" onclick="return confirm('Voulez-vous vraiment supprimer ce courrier ?');">Supprimer</a>
                                            <?php else: ?>
                                                <span class="text-muted">Non supprimable</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../../assets/js/plugins.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>
