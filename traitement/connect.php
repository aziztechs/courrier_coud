<?php
include('fonction.php');
$error = "";

if (!empty($_GET['username']) && !empty($_GET['password'])) {
    $username = $_GET['username'];
    $password = $_GET['password'];

    // Appeler la fonction login pour vérifier l'utilisateur et son mot de passe
    $row = login($username, $password);

    if ($row) {
        // Démarrer la session
        session_start();

        // Stocker les informations de l'utilisateur dans la session
        $_SESSION['id_user'] = $row['id_user'];
        $_SESSION['username'] = $row['Username'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['password'] = $row['password'];
        $_SESSION['Fonction'] = $row['Fonction'];
        $_SESSION['Prenom'] = $row['Prenom'];
        $_SESSION['Nom'] = $row['Nom'];
        $_SESSION['Matricule'] = $row['Matricule'];
        $_SESSION['Tel'] = $row['Tel'];
        $_SESSION['subrole'] = $row['subrole'];

        // Rediriger en fonction de la fonction de l'utilisateur
        if ($row['Fonction'] == 'assistant_courrier') {
            header('Location: /courrier_coud/profils/dashboards/dashboard.php');
            exit();
        } else if ($row['Fonction'] == 'chef_courrier') {
            header('Location: /courrier_coud/profils/dashboards/dashboard.php');
            exit();
        }else if ($row['Fonction'] == 'directeur') {
            header('Location: /courrier_coud/profils/dashboards/dashboard.php');
            exit();
        }else if ($row['Fonction'] == 'secretariat_csa') {
            header('Location: /courrier_coud/profils/dashboards/dashboard_csa.php');
            exit();
        } else if ($row['Fonction'] == 'secretariat_service_social') {
            header('Location: /courrier_coud/profils/dashboards/dashboard.php');
            exit();
        }else if ($row['Fonction'] == 'secretariat_budget') {
            header('Location: /courrier_coud/profils/dashboards/dashboard.php');
            exit();
        }else if ($row['Fonction'] == 'superadmin') {
            header('Location: /courrier_coud/profils/dashboards/dashboard.php');
            exit();
        } else {
        // Si les identifiants sont incorrects
        header('Location: /courrier_coud/login.php?error=Username ou Passeword incorrecte.');
        exit(); 
    }
}
}
      