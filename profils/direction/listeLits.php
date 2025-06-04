<?php
session_start();

// Vérification de la session utilisateur
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

// Inclusion des fichiers nécessaires
include('../../traitement/fonction.php');

// Vérification de l'ID du courrier
if (isset($_SESSION['id_courrier'])) {
    $id_courrier = $_SESSION['id_courrier'];
} else {
    echo "ID COURRIER INDISPONIBLE";
    exit(); // Arrêter l'exécution si l'ID est indisponible
}

// Initialisation des départements
$departments = isset($_SESSION['selected_departments']) ? $_SESSION['selected_departments'] : [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['departements'])) {
    $selectedDepts = $_POST['departements'];
    // Stocker les départements dans la session
    $_SESSION['selected_departments'] = $selectedDepts;
    // Rediriger vers la page finale
    header('Location: displayDepartments.php');
    exit();
}

// Récupération des départements via une fonction
$departements = getEnumValues('departement', 'Nom_dept');

// Récupération des départements déjà imputés (vous pouvez adapter la requête selon votre base de données)
$alreadyImputedDepartments = getAlreadyImputedDepartments($id_courrier); // Fonction que vous devez implémenter pour obtenir les départements déjà imputés
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: GESTION_COURRIERS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/base.css" />
    <link rel="stylesheet" href="../../assets/css/main.css" />
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <!-- Lien vers le CSS de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Lien vers les icônes Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Lien vers les icônes Fontawesome -->
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> <style>
        .department-button {
            border-radius: 10px;
            margin-bottom: 10px;
            display: block;
            text-align: center;
            line-height: 4.5rem;
        }

        .department-container {
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 51, 102, 0.5);
            background-color: #fff;
        }

        .btn-primary {
            background-color: #8287a2;
        }

        /* Style pour les départements déjà imputés */
        .btn-imputed {
            background-color: #ff1a1a !important; /* Rouge plus intense au survol */
            color: white !important;
        }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>
    <div class="container col-sm-6" style="background-color: #b0cdee">
        <div class="row">
            <div class="card-header" style="background-color: #0056B3 ;">
                <h1 class="text-white">SELECTION LES DEPARTEMENTS OU CELLULES</h1>
            </div>
        </div>
    </div>
    <form method="POST" action="listeLits.php">
        <div class="container department-container">
            <div class="row">
                <?php
                foreach ($departements as $departement) {
                    // Vérifier si le département est déjà imputé
                    $isImputed = in_array($departement, $alreadyImputedDepartments);

                    echo '<div class="col-md-2 col-sm-4 mb-3">';
                    echo '<input type="checkbox" name="departements[]" id="dept-' . htmlspecialchars($departement) . '" value="' . htmlspecialchars($departement) . '" class="btn-check" ' . ($isImputed ? 'disabled' : '') . '>';
                    echo '<label class="btn ' . ($isImputed ? 'btn-imputed' : 'btn-primary') . ' text-white text-center department-button" 
                          for="dept-' . htmlspecialchars($departement) . '">' . htmlspecialchars($departement) . '</label>';
                    echo '</div>';
                }
                ?>
            </div>
            <div class="text-center mt-2" style="font-size: 20px">
                <button type="submit" class="text-white" style="background-color: #0056B3">Valider la Sélection</button>
            </div>
            <div class="container shadow-lg" style="border-radius: 30px; text-align: center; background-color: #8287a2; height: 50px;">
                <a  class="text-white" style="border: none;" href="javascript:history.back()">
                    &nbsp; RETOUR
                </a>
            </div>
        </div>
    </form>
    <script src="../../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../../assets/js/plugins.js"></script>
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
