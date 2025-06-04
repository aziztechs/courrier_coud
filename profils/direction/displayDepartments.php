<?php
// Activer l'affichage des erreurs pour débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Démarre une nouvelle session ou reprend une session existante
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courier_coud/');
    session_destroy();
    exit();
}

// Supprimer une variable de session spécifique
unset($_SESSION['classe']);

// Inclure les fichiers nécessaires
include('../../traitement/fonction.php');
include('../../traitement/requete.php');

// Récupérer les courriers et départements sélectionnés
$courriers = recupererTousLesCourriers();
$departments = isset($_SESSION['selected_departments']) ? $_SESSION['selected_departments'] : [];
$instructions = getEnumValues('imputation', 'Instruction');
//$instruction_autre = getEnumValues('imputation', 'instruction_autre');
$id_courrier = $_SESSION['id_courrier'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: GESTION_COURRIER</title>
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <!-- <link rel="stylesheet" href="../../assets/css/vendor.css"> -->
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 1000px;
            border-color: #0056B3;
        }
        .form-field {
            padding: 20px;
            background-color: #0056B3;
            border-radius: 15px;
            text-align: center;
        }
        .department-name {
            background-color: #0056B3;
            color: white;
            border-radius: 15px;
            padding: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include('../../head.php'); ?>

    <div class="container col-sm-6" style="background-color: #b0cdee; border-radius: 30px;">
        <div class="row">
            <div class="card-header" style="background-color: #0056B3; border-radius: 30px;">
                <h1 class="text-white">INSTRUCTIONS</h1>
            </div>
        </div>
    </div>

    <div class="container shadow-lg p-4 my-5">
    <form action="../../traitement/fonctionImput.php" method="POST">
        <div class="d-flex flex-wrap justify-content-center">
            <?php foreach ($departments as $dept): ?>
                <div class="form-field col-md-3 mb-3">
                    <input type="text" value="<?php echo htmlspecialchars($dept); ?>" readonly class="form-control department-name mb-2" />
                    <div class="form-group">
                        <label class="text-white" for="instruction_<?php echo htmlspecialchars($dept); ?>">Sélectionner une instruction :</label>
                        <select id="instruction_<?php echo htmlspecialchars($dept); ?>" name="instructions[<?php echo htmlspecialchars($dept); ?>]" class="form-control instruction-select" required onchange="toggleOtherField(this, '<?php echo htmlspecialchars($dept); ?>')">
                            <option value="" disabled selected>Veuillez sélectionner une instruction</option>
                            <?php foreach ($instructions as $instruction): ?>
                                <option value="<?php echo htmlspecialchars($instruction); ?>">
                                    <?php echo htmlspecialchars($instruction); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="Autre">Autre</option>
                        </select>
                        <!-- Champ caché pour "Autre" -->
                        <input type="text" name="instructions_autre[<?php echo htmlspecialchars($dept); ?>]" id="instruction_autre_<?php echo htmlspecialchars($dept); ?>" class="form-control mt-2 d-none" placeholder="Veuillez spécifier l'instruction" />
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="id_courrier" value="<?php echo htmlspecialchars($id_courrier); ?>">
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary">Envoyer</button>
        </div>
        <div class="text-center mt-2">
            <a href="javascript:history.back()">Retour</a>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Fonction pour afficher ou masquer le champ "Autre"
    function toggleOtherField(select, department) {
        const otherField = document.getElementById("instruction_autre_" + department);
        if (select.value === "Autre") {
            otherField.classList.remove("d-none");
            otherField.required = true; // Rendre le champ obligatoire
        } else {
            otherField.classList.add("d-none");
            otherField.required = false;
            otherField.value = ""; // Réinitialiser le champ
        }
    }
</script>
</body>
</html>
