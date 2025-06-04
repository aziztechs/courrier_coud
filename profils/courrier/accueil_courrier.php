<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courier_coud/');
    session_destroy();
    exit();
}
unset($_SESSION['classe']);
include('../../traitement/fonction.php');
include('../../traitement/fonctionCour.php');
include('../../activite.php');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: GESTION_COURRIER</title>
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/formCour.css" />

    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
        
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
                
                <form method="POST" id="myForm" action="../../traitement/fonctionCour.php" enctype="multipart/form-data">
                    <div class="row">
                        <!-- Première ligne -->
                        <div class="col-md-6">
                            <input type="text" name="numero" required class="form-control" placeholder="Numéro du courrier">
                        </div>
                        <div class="col-md-6 mt-2">
                            <input type="datetime-local" name="datetime" id="date" class="form-control"  required>
                        </div>
                        
                        <!-- Deuxième ligne -->
                        <div class="col-md-6 mt-2">
                            <input type="text" name="objet" required class="form-control" placeholder="Objet du courrier">
                        </div>
                        <div class="col-md-6 mt-2">
                            <input type="file" name="pdf" id="pdf" accept=".pdf" required class="form-control">
                        </div>
                        
                        <!-- Troisième ligne -->
                        <div class="col-md-6 mt-2">
                            <select name="nature" required class="form-select">
                                <option value="">Sélectionner la nature</option>
                                <option value="arrive">Arrivé</option>
                                <option value="depart">Départ</option>
                            </select>
                        </div>
                        <div class="col-md-6 mt-2">
                            <select name="Type" required class="form-select">
                                <option value="">Sélectionner le type</option>
                                <option value="interne">Interne</option>
                                <option value="externe">Externe</option>
                            </select>
                        </div>
                        
                        <!-- Quatrième ligne -->
                        <div class="col-md-6 mt-2">
                            <input type="text" name="expediteur" required class="form-control" placeholder="Nom de l'expéditeur">
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-submit" style="background-color: #0056B3;color: #fff; height: 40px;">
                            <i class="fas fa-save me-2"></i>ENREGISTRER LE COURRIER
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../../assets/js/main.js"></script>
    
    <script>
        document.getElementById('myForm').addEventListener('submit', function(event) {
            var dateInput = document.getElementById('date').value;
            if (!dateInput) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Veuillez sélectionner une date et heure',
                    confirmButtonText: 'OK'
                });
                event.preventDefault();
                return;
            }
            
            var selectedDate = new Date(dateInput);
            var today = new Date();
            
            if (selectedDate > today) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur de date',
                    text: 'La date ne peut pas être dans le futur',
                    confirmButtonText: 'OK'
                });
                event.preventDefault();
            }
        });
    </script>
</body>
</html>     