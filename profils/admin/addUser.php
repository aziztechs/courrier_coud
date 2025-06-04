<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}
include '../../traitement/fonction_user.php';
include '../../traitement/connect.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['Nom'];
    $prenom = $_POST['Prenom'];
    $username = $_POST['Username'];
    $password = $_POST['Password'];
    $email = $_POST['email'];
    $tel = $_POST['Tel'];
    $fonction = $_POST['Fonction'];
    $matricule = $_POST['Matricule'];
    $actif = isset($_POST['Actif']) ? 1 : 0;

    $result = ajouterUtilisateur($nom, $prenom, $username, $password, $email, $tel, $fonction, $matricule, $actif);
    
    if ($result) {
        $message = "<div class='alert alert-success'>Utilisateur ajouté avec succès.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Erreur lors de l'ajout.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUD: GESTION_COURRIER</title>
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/base.css" />
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/datatables.min.css">

    <link rel="stylesheet" href="../../assets/css/form.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
</head>
<body>
<?php include '../../head.php'; ?>

<div class="info-banner text-white d-flex justify-content-center align-items-center"
     style="height: 140px; background-color: #0056b3; max-width: 100%; width: 100%; margin: auto;">
    <div class="welcome-text"></div>
    <div class="user-info-section">
        <p class="lead">Espace Administration : Bienvenue !<br>
            <span>
                (<?php echo $_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']; ?>)
            </span>
        </p>
    </div>
</div>

<div class="col-md-12 mb-5">
    <div class="form-container">
        <div class="form-section">
            <div class="form-header">
                <h2 class="mb-0"><i class="fas fa-user-plus me-2"></i>AJOUT D'UN NOUVEL UTILISATEUR</h2>
            </div>
            
            <div class="form-content">
                <?= $message ?>
                
                <form method="post" action="">
                    <div class="form-group-row">
                        <div class="form-group-col">
                            <input type="text" name="Nom" class="form-control" placeholder="Entrez le nom" required>
                        </div>
                        <div class="form-group-col">
                            <input type="text" name="Prenom" class="form-control" placeholder="Entrez le prénom" required>
                        </div>
                    </div>
                    
                    <div class="form-group-row">
                        <div class="form-group-col">
                            <input type="text" name="Username" class="form-control" placeholder="Entrez le nom d'utilisateur" required>
                        </div>
                        <div class="form-group-col">
                            <input type="password" name="Password" class="form-control" placeholder="Créez un mot de passe" required>
                        </div>
                    </div>
                    
                    <div class="form-group-row">
                        <div class="form-group-col">
                            <input type="email" name="email" class="form-control" placeholder="Entrez l'email" required>
                        </div>
                        <div class="form-group-col">
                            <input type="text" name="Tel" class="form-control" placeholder="Entrez le numéro de téléphone" required>
                        </div>
                    </div>
                    
                    <div class="form-group-row">
                        <div class="form-group-col">
                            <input type="text" name="Fonction" class="form-control" placeholder="Entrez la fonction">
                        </div>
                        <div class="form-group-col">
                            <input type="text" name="Matricule" class="form-control" placeholder="Entrez le matricule">
                        </div>
                    </div>
                    
                    <div class="form-check  mb-2 ps-4">
                        <input class="form-check-input me-2" type="checkbox" name="Actif" id="actifCheck" checked style="width: 18px; height: 18px;">
                        <label class="form-check-label" for="actifCheck">
                            Compte actif
                        </label>
                    </div>
                    
                    <div class="text-center mb-3">
                        <button type="submit" class="btn btn-submit" style="width: 200px; height: 50px; font-size: 18px; color: #fff; background-color: #0056b3;">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../assets/js/jquery-3.2.1.min.js"></script>
<script src="../../assets/js/main.js"></script>
</body>
</html>