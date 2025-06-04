<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}
// Supprimer une variable de session spécifique
unset($_SESSION['classe']);
// Sélectionnez les options à partir de la base de données avec une pagination
include('../../traitement/fonction.php');
include('../../traitement/requete.php');
$departements = getEnumValues('departement', 'Nom_dept');
$instructions = getEnumValues('imputation', 'Instruction');
$courriers = recupererNumCourriers();


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: GESTION_COURRIER</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/base.css" />
    <!-- <link rel="stylesheet" href="../../assets/css/vendor.css" /> -->
    <link rel="stylesheet" href="../../assets/css/main.css" />
    <!-- <link rel="stylesheet" href="../../assets/css/login.css" /> -->
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/bootstrap/js/bootstrap.min.js">
    <link rel="stylesheet" href="../../assets/bootstrap/js/bootstrap.bundle.min.js">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

</head>

<body>
    <?php include('../../head.php'); ?>
    <br>
    <tr>
             <td colspan="4">
                 <center>
                 <strong>Imputer un Courrier</strong>
                 </center>
                </td>
             </tr>
    <div class="container shadow-lg">
        <div class=" d-flex justify-content-center col-height   contact__form1">
        <div class=" row g-3 contact__form1 shadow"></div>
           
            
        <form method="POST" id="formImput" action="">
        <div class="form-group">
            <label for="numero_courrier" class="form-label" >Numéro de Courrier</label>
            <select name="numero_courrier" id="numero_courrier" class="form-control" style="font-size:17px;" required>
                <option value="">Sélectionnez un numéro de courrier</option>
                <?php foreach ($courriers as $courrier): ?>
                    <option value="<?= htmlspecialchars($courrier['Numero_Courrier']); ?>">
                        <?= htmlspecialchars($courrier['Numero_Courrier']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
            <div class="form-group">
                <label for="departements">Départements :</label>
                <select name="departements[]" id="departements" multiple class="form-control" style="font-size:17px;">
                    <?php foreach ($departements as $departement): ?>
                        <option value="<?= htmlspecialchars($departement); ?>"><?= htmlspecialchars($departement); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="instructions">Instructions :</label>
                <select name="instructions[]" id="instructions" multiple class="form-control"  style="font-size:20px;">
                    <?php foreach ($instructions as $instruction): ?>
                        <option value="<?= htmlspecialchars($instruction); ?>"><?= htmlspecialchars($instruction); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" href="accueil_direction.php?save=<?php htmlspecialchars($courrier['Numero_Courrier']);  ?>" class="btn btn-primary">Imputer</button>
        </form>
        </div>
        <div class="container shadow-lg" style="border-radius: 30px; text-align: center; background-color: #cddbe8 ; height: 50px;">
            <a class="text-primary" style="background-color: #cddbe8 ; border: none"  href="javascript:history.back()">
                <i class="fa fa-repeat" aria-hidden="true"></i>&nbsp; RETOUR
            </a>
        </div>
    </div>
 </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->

    <script>
window.onbeforeunload = function() {
    document.forms['formImput'].reset();
};
</script>
    <script src="../../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../../assets/js/plugins.js"></script>
    <script src="../../assets/js/main.js"></script>

</body>
<script src="../../assets/js/script.js"></script>

</html>