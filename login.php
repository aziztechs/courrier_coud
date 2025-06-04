<?php
session_start();
if (!empty($_SESSION['username']) && !empty($_SESSION['password'])) {
  session_destroy();
}
include('activite.php');
include('traitement/connect.php');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8" />
  <title>COURRIER COUD</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/css/base.css" />
  <link rel="stylesheet" href="assets/css/vendor.css" />
  <link rel="stylesheet" href="assets/css/main.css" />
  <link rel="stylesheet" href="assets/css/login.css" />
  <link rel="stylesheet" href="assets/css/styles.css" />
  <link rel="stylesheet" href="assets/css/tableau.css" />
  <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/images/backMail.jpg') no-repeat center center fixed;
      background-size: cover;
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      align-items: center;
      margin: 0;
      padding: 0;
    }
    .login-container {
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 30px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.3);
      margin-top: 120px; /* Augmentation de la marge supérieure */
    }
    .form-field input {
      background: rgba(255, 255, 255, 0.8) !important;
      border: 1px solid rgba(0, 0, 0, 0.1) !important;
    }
    .form-title {
      color: white;
      text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
      margin-bottom: 20px;
      text-align: center;
    }
    .btn--primary {
      background: rgba(0, 86, 179, 0.8) !important;
      color: white !important;
      border: none !important;
    }
    .btn--primary:hover {
      background: rgba(0, 86, 179, 1) !important;
    }
    a {
      color: white !important;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
    }
    a:hover {
      color: #e6f0ff !important;
    }
    .s-header {
      background-color: rgba(0, 86, 179, 0.8) !important;
      backdrop-filter: blur(5px);
    }
  </style>
</head>

<body>

  <header class="s-header fixed-top">
    <div class="header-content ">
      <div class="header-logo">
        <a class="site-logo mt-2" href="/courrier_coud/login.php">
            <img src="/courrier_coud/assets/images/logo.png" alt="Logo Campus Coud" />
        </a>
      <div class="institution-name" style="color: #fff; margin-top: 3px;">CAMPUS COUD</div>
    </div>          
    <a class="header-menu-toggle" href="#0" id="menu-toggle"><span>Menu</span></a>
  </header>
  
  <div class="login-container">
      <form id="loginForm" action="/courrier_coud/traitement/connect.php">
        <h3 class="form-title">VEUILLEZ RENSEIGNER LES CHAMPS</h3>
        <span class="login-error text"> 
          <?php
            if (isset($_GET['error'])) {
              echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8');
            }
            if (isset($_GET['success'])) {
              echo htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8');
            }
          ?>
        </span>
        <fieldset>
          <div class="form-field">
            <input onkeydown="upperCaseF(this)" name="username" id="username" required type="text" placeholder="Username" value="" class="full-width"
                    style="border-radius: 20px; border: none; height: 40px; padding: 0 15px;">
          </div>
          <div class="form-field">
            <input name="password" type="password" required id="password" placeholder="Mot de passe" value="" class="full-width"
                    style="border-radius: 20px; border: none; height: 40px; padding: 0 15px; margin-top: 15px;">
          </div>
          <?php if (isset($error_message)) { ?>
            <div id="error-message" class="error-message"><?= $error_message ?></div>
          <?php } ?>
          <div class="form-field" style="margin-top: 20px;">
            <button type="submit" class="full-width btn--primary" style="border-radius: 20px; height: 40px;">
              Se connecter
            </button>
            
            <a href='#' style="display: block; text-align: center; margin-top: 15px;">Mot de passe oublié ?</a>
            <center> <a href='index.php' style="display: inline-block; margin-top: 10px;">Retour</a> </center>
          </div>
        </fieldset>
      </form>
    </div>
  
  <!-- ================================Java Script================================================== -->
  <script src="assets/js/script.js"></script>
  <script src="assets/js/jquery-3.2.1.min.js"></script>
  <script src="assets/js/plugins.js"></script>
  <script src="assets/js/main.js"></script>
</body>

</html>