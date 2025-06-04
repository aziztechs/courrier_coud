<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}
include '../../traitement/fonction_user.php';
include '../../traitement/connect.php';

$user = null;

// Récupérer l'utilisateur à modifier
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = getUserById($id);
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    } else {
        header("Location: users.php");
        exit();
    }
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $nom = $_POST['Nom'];
    $prenom = $_POST['Prenom'];
    $username = $_POST['Username'];
    $email = $_POST['email'];
    $tel = $_POST['Tel'];
    $fonction = $_POST['Fonction'];
    $matricule = $_POST['Matricule'];
    $actif = isset($_POST['Actif']) ? 1 : 0;
    $password = !empty($_POST['Password']) ? $_POST['Password'] : null;

    if (modifierUtilisateur($id, $nom, $prenom, $username, $email, $tel, $fonction, $matricule, $actif, $password)) {
        header("Location: users.php?success=1");
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Erreur lors de la modification.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un utilisateur</title>
    <link rel="shortcut icon" href="../../assets/img/log.gif" type="image/x-icon">
    <link rel="icon" href="../../assets/img/log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/form.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
                <h1 class="mb-0"><i class="fas fa-pencel me-2"></i> Modifier un utilisateur</h1>
            </div>
            <div class="form-content">
                <?php if (isset($message)) echo $message; ?>
                <?php if ($user): ?>
                <form method="post" action="">
                    <input type="hidden" name="id" value="<?= $user['id_user'] ?>">

                    <div class="form-group-row">
                        <div class="form-group-col">
                            <input type="text" name="Nom" class="form-control" value="<?= htmlspecialchars($user['Nom']) ?>" required>
                        </div>
                        <div class="form-group-col">
                            <input type="text" name="Prenom" class="form-control" value="<?= htmlspecialchars($user['Prenom']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group-row">
                        <div class="form-group-col">
                            <input type="text" name="Username" class="form-control" value="<?= htmlspecialchars($user['Username']) ?>" required>
                        </div>
                        <div class="form-group-col">
                            <input type="password" name="Password" class="form-control" placeholder="Nouveau mot de passe (laisser vide pour ne pas changer) :">
                        </div>
                    </div>
                    
                    <div class="form-group-row">
                        <div class="form-group-col">
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>"  placeholder="Entrez Email" required>
                        </div>
                        <div class="form-group-col">
                            <input type="text" name="Tel" class="form-control" value="<?= htmlspecialchars($user['Tel']) ?>"  placeholder="Entrez le numéro de téléphone" required>
                        </div>
                    </div>

                     <div class="form-group-row">
                        <div class="form-group-col">
                            <input type="text" name="Fonction" class="form-control" value="<?= htmlspecialchars($user['Fonction']) ?>" placeholder="Entrez la fonction">
                        </div>
                        <div class="form-group-col">
                            <input type="text" name="Matricule" class="form-control" value="<?= htmlspecialchars($user['Matricule']) ?>" placeholder="Entrez le matricule">
                        </div>
                    </div>

                     <div class="form-check  mb-2 ps-4">
                        <input type="checkbox" name="Actif" class="form-check-input" id="actifCheck" <?= $user['Actif'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="actifCheck">Actif</label>
                            Compte actif
                        </label>
                    </div>
                    
                    <div class="text-center mb-3">
                        <a href="users.php" class="btn btn-secondary mt-3" style="width: 200px; height: 40px; font-size: 18px; color: #fff; background-color:rgb(62, 62, 63);">Annuler</a>
                        <button type="submit" class="btn btn-submit" style="width: 200px; height: 40px; font-size: 18px; color: #fff; background-color: #0056b3;">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php else: ?>
    <div class="alert alert-danger">Utilisateur non trouvé.</div>
    <?php endif; ?>
</div>
</body>
</html>