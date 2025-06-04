<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courier_coud/');
    session_destroy();
    exit();
}
// Supprimer une variable de session spécifique
unset($_SESSION['classe']);
// Sélectionnez les options à partir de la base de données avec une pagination
include('../../traitement/fonction.php');
include('../../traitement/requete.php');
$courriers = recupererTousLesCourriers();
// Récupérer l'ID du courrier à partir de l'URL ou d'un autre moyen
$id = $_GET['id'] ?? null; // Assurez-vous que l'ID est passé dans l'URL
$courrier = recupererCourrierParId($id);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maj'])) {
    // Récupérer les valeurs du formulaire
    $numero = $_POST['numero'];
    $objet = $_POST['objet'];
    $nature = $_POST['nature'];
    $type = $_POST['type'];
    $date = $_POST['datetime'];
    $expediteur = $_POST['expediteur'];
    // Mettre à jour le courrier
    mettreAJourCourrier($id , $date, $numero, $objet, $nature,$type, $expediteur );
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
  
    <link rel="stylesheet" href="../../assets/css/formCour.css" />
   
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
    <?php include('../../head.php'); ?> 
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 140px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Bienvenue !<br>
                <span>
                    (<?php echo $_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']; ?>)
                </span>
            </p>
        </div>
    </div>
    <div class="main-container">
        <div class="form-container">
            <div class="form-section">
                <div class="form-header" style="background: linear-gradient(135deg, #0056B3 0%, #0056b3; 100%);">
                    <h2><i class="fas fa-envelope me-2"></i>ENREGISTREMENT DE COURRIER</h2>
                </div>
                <form id="myForm" method="POST" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mt-2">
                            <input style="height: 40px; border-radius: 30px" type="text" name="numero" id="numero" class="form-control" placeholder="Numéro du courrier" value="<?= htmlspecialchars($courrier['Numero_Courrier']); ?>" required>
                        </div>
                        <div class="col-md-6 mt-2">
                            <input style="height: 40px; border-radius: 30px" type="text" name="objet" id="objet" class="form-control" placeholder="Objet du courrier" value="<?= htmlspecialchars($courrier['Objet'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mt-2">
                            <input style="height: 40px; border-radius: 30px" type="text" name="expediteur" id="expediteur" class="form-control" placeholder="Nom de l'expediteur" value="<?= htmlspecialchars($courrier['Expediteur'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mt-2">
                            <input style="height: 40px; border-radius: 30px" type="datetime-local" name="datetime" id="date" class="form-control" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($courrier['Date'])) ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mt-2">
                            <select style="height: 40px; border-radius: 30px" name="nature" id="nature" class="form-control" required>
                                <option value="arrivee" <?= isset($courrier['Nature']) && $courrier['Nature'] == 'arrivee' ? 'selected' : ''; ?>>Arrivée</option>
                                <option value="depart" <?= isset($courrier['Nature']) && $courrier['Nature'] == 'depart' ? 'selected' : ''; ?>>Départ</option>
                            </select>
                        </div>
                        <div class="col-md-6 mt-2">
                            <select style="height: 40px; border-radius: 30px" name="type" id="nature" class="form-control" required>
                                <option value="interne" <?= isset($courrier['Type']) && $courrier['Type'] == 'interne' ? 'selected' : ''; ?>>Interne</option>
                                <option value="externe" <?= isset($courrier['Type']) && $courrier['Type'] == 'externe' ? 'selected' : ''; ?>>Externe</option>
                            </select>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button class="btn btn-secondary" style="font-size: 15px; background-color:rgb(72, 73, 74);" onclick="javascript:history.back()">
                            <i class="fa fa-repeat" aria-hidden="true"></i>&nbsp;Retour
                        </button>
                        <button type="submit"  name="maj" class="btn btn-submit" style="background-color: #0056B3;color: #fff; height: 40px;">
                            <i class="fas fa-edit me-2"></i>METTRE A JOUR
                        </button>
                    </div>
            </form>                  
            </div>
        </div>
    </div>
    
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script>
        document.getElementById('myForm').addEventListener('submit', function(event) {
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
    <script>
window.onbeforeunload = function() {
    document.forms['myForm'].reset();
};
</script>
    <script src="../../assets/js/search_update.js"></script> 
    <script src="../../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../../assets/js/plugins.js"></script>
    <script src="../../assets/js/main.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
<script src="../../assets/js/script.js"></script>

</html>