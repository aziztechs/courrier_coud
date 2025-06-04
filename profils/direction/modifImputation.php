<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

include('../../traitement/fonction.php');
include('../../traitement/requete.php');

// Récupère les informations d'imputation à modifier
$idImputation = $_GET['id_impu']; // Assurez-vous que l'id_imputation est passé dans l'URL
// Si le formulaire est soumis, mettre à jour l'imputation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departements = $_POST['departements'];
    $instructions = $_POST['instructions'];

    // Appel de la fonction pour modifier l'imputation
    $result = modifierImputationParIdImpu($idImputation);

    // Redirection si la mise à jour a été réussie
    if ($result > 0) {
        header('Location: accueil_direction.php');
        exit();
    }
}
// Récupère les valeurs pour les départements et les instructions
$departements = getEnumValues('departement', 'Nom_dept');
$instructions = getEnumValues('imputation', 'Instruction');
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: GESTION_COURRIERS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/base.css" />
    <link rel="stylesheet" href="../../assets/css/vendor.css" />
    <link rel="stylesheet" href="../../assets/css/main.css" />
    <link rel="stylesheet" href="../../assets/css/login.css" />
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <!-- Lien vers le CSS de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Lien vers les icônes Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Lien vers les icônes Fontawesome -->
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
    <?php include('../../head.php'); ?>
    <br>
    <div class="container shadow-lg">
        <div class="d-flex justify-content-center col-height contact__form1">
            <form method="POST" action="mise_a_jour_imputation.php">
                <div class="form-group">
                    <label for="numero_courrier" class="form-label">Numéro de Courrier</label>
                    <input type="text" name="numero_courrier" id="numero_courrier" class="form-control" value="<?= htmlspecialchars($imputation['Numero_Courrier']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="departements">Départements :</label>
                    <div class="row">
                        <?php foreach ($departements as $departement): ?>
                            <div class="col-md-2 col-sm-4 mb-3">
                                <input type="checkbox" name="departements[]" id="dept-<?= htmlspecialchars($departement); ?>" value="<?= htmlspecialchars($departement); ?>" class="btn-check"
                                    >
                                <label class="btn btn-primary text-white text-center department-button" for="dept-<?= htmlspecialchars($departement); ?>" style="margin: 5px;">
                                    <?= htmlspecialchars($departement); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="instructions">Instructions :</label>
                    <div class="row">
                        <?php foreach ($instructions as $instruction): ?>
                            <div class="col-md-2 col-sm-4 mb-3">
                                <input type="checkbox" name="instructions[]" id="instr-<?= htmlspecialchars($instruction); ?>" value="<?= htmlspecialchars($instruction); ?>" class="btn-check"
                                    >
                                <label class="btn btn-primary text-white text-center instruction-button" for="instr-<?= htmlspecialchars($instruction); ?>" style="margin: 5px;">
                                    <?= htmlspecialchars($instruction); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="text-center mt-2" style="font-size: 20px">
                    <button type="submit" class="text-white" style="background-color: #3777B0">Valider la Modification</button>
                </div>
            </form>
        </div>

        <div class="container shadow-lg" style="border-radius: 30px; text-align: center; background-color: #0056B3; height: 50px;">
            <a class="text-primary" style="border: none;" href="javascript:history.back()">
                <i class="fa fa-repeat" aria-hidden="true"></i>&nbsp; RETOUR
            </a>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
