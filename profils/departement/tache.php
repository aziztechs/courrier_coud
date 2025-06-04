<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    exit();
}
//connexion à la base de données
require('../../traitement/fonction.php');
// Sélectionnez les options à partir de la base de données avec une pagination
require('../../traitement/traitOneDepart.php');
require('../../traitement/add_suivi.php');
$departement = isset($_GET['departement']) ? $_GET['departement'] : '';
$courriers = getCourriersByRole();

if (isset($_GET['id_imputation'])) {
    $id_imputation = $_GET['id_imputation'];
    $instruction = !empty($_GET['instruction']) ? $_GET['instruction'] : $_GET['instruction_p'];
    $numCourrier = $_GET['Numero_Courrier'];
    $objet = $_GET['objet'];

    // Utilisez l'id_imputation pour récupérer et afficher les informations nécessaires
    //echo "ID d'imputation: " . htmlspecialchars($id_imputation);

    // Vous pouvez maintenant utiliser $id_imputation pour faire des requêtes supplémentaires,
    // par exemple pour récupérer des informations supplémentaires sur le courrier ou pour ajouter des tâches
} else {
    echo "Aucun ID d'imputation fourni.";
    // Optionnel : rediriger l'utilisateur ou afficher un message d'erreur approprié
}
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
    <link rel="stylesheet" href="../../assets/css/datatables.min.css"/>
    <!-- <link rel="stylesheet" href="../../assets/css/login.css" /> -->
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/tableau.css">
    <link rel="stylesheet" href="../../assets/bootstrap/js/bootstrap.min.js">
    <link rel="stylesheet" href="../../assets/bootstrap/js/bootstrap.bundle.min.js">
     <!-- Lien vers le CSS de Bootstrap -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Lien vers les icônes Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Lien vers les icônes Fontawesome -->
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
</head>

<body>
    <?php include('../../head.php');?>


    <div class="container col-sm-6" style="background-color: #b0cdee">
        <div class="row">
            <div class="card-header" style="background-color: #0056B3;">
                <h1 class="text-white">AJOUTER TÂCHE</h1>
            </div>
        </div>
    </div>
    <div class="container mb-4">
     <label for="statut" class="form-label" style="color: #0056B3;"> <strong> Historique :</strong></label>
    
    <div class="d-flex justify-content-between" style="gap: 10px;">
        <!-- Historique du numéro de courrier -->
        <div class="flex-fill">
            <label for="numeroCourrier" class="form-label" style="font-weight: bold; color: #0056B3;">Numéro de courrier :</label>
            <input type="text" readonly name="numeroCourrier" id="numeroCourrier" class="form-control text-center" 
                   value="<?= htmlspecialchars($numCourrier) ?>" style="background-color: #e9ecef;">
        </div>

        <!-- Historique de l'objet -->
        <div class="flex-fill">
            <label for="objet" class="form-label" style="font-weight: bold; color: #0056B3;">Objet :</label>
            <input type="text" readonly name="objet" id="objet" class="form-control text-center" 
                   value="<?= htmlspecialchars($objet) ?>" style="background-color: #e9ecef;">
        </div>

        <!-- Historique de l'instruction -->
        <div class="flex-fill">
            <label for="instruction" class="form-label" style="font-weight: bold; color: #0056B3;">Instruction :</label>
            <input type="text" readonly name="instruction" id="instruction" class="form-control text-center" 
                   value="<?= htmlspecialchars($instruction) ?>" style="background-color: #e9ecef;">
        </div>
    </div>
</div>
    <div class="container shadow-lg" style="background-color: #0056B3;">
        <div class="row">
            <div class="col-12">
                <div class="data-table">
                    <div class="card ml-4 d-flex justify-content-center">
                        <div class="card-body">
                            <div class="search-container ">
                            <form action="" id="myForm1" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="id_imputation" value="<?= htmlspecialchars($id_imputation)?>">
                                    <input type="hidden"  name="instruction" value="<?= htmlspecialchars($instruction)?>">

                                    <div class="mb-4">
                                        <label for="statut" class="form-label" style="color: #0056B3;">Description :</label>
                                        <input style="height: 50px; border-radius: 30px; font-size: 20px;" type="text" name="statut" id="statut" class="form-control form-control-lg"
                                            placeholder="Entrez la description" required>
                                    </div>

                                    <div class="mb-4">
                                        <label for=""></label>
                                        <input type="file" name="pdf" accept=".pdf" required class="form-control border-radius" style="font-size:20px; border-radius: 30px; height: 50px;"
                                            placeholder="Le courrier en format pdf">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="datetime" style="color: #0056B3;">Date et Heure :</label>
                                        <input style="height: 50px; border-radius: 30px; font-size: 20px;" type="date" name="datetime" id="date" 
                                            class="form-control form-control-lg" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary" style="background-color: #0056B3; border-radius: 30px;">Ajouter Suivi</button><br>
                                     <button class="btn btn-primary mt-4" style="font-size: 10px; background-color: #b0cdee; border-radius: 30px; border: none; color: #0056B3;" onclick="javascript:history.back()">
                                        <i class="fa fa-undo" style="color: #3777b0;" aria-hidden="true"></i>&nbsp;RETOUR
                                    </button> 
                            </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('myForm1').addEventListener('submit', function(event) {
        var dateInput = document.getElementById('date').value;
        var selectedDate = new Date(dateInput);
        var today = new Date();
        
        // On vérifie que la date sélectionnée n'est pas dans le futur
        if (selectedDate > today) {
            Swal.fire({
                icon: 'error',
                title: 'Erreur de date',
                text: 'La date ne peut pas être dans le futur.',
                confirmButtonText: 'OK'
            });
            event.preventDefault();  // Empêche la soumission du formulaire
        }
    });
</script>
    <!-- Scripts JavaScript -->
    <script src="../../assets/js/search_update.js"></script>                   
    <script src="../../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../../assets/js/plugins.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>

</html>