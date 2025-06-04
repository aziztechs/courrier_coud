<?php
session_start();
if (!empty($_SESSION['username']) && !empty($_SESSION['password'])) {
  session_destroy();
}
include('activite.php');
include('traitement/connect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/images/backMail.jpg') no-repeat center center fixed;
      background-size: cover;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .card {
      background: rgba(255, 255, 255, 0.9);
      border-radius: 20px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 400px;
    }
    .card-header {
      border-radius: 10px 10px 0 0;
    }
  </style>
</head>
<body>
  <?php include('headers.php'); ?>
  <div class="card mt-5 border-0 bg-transparent">
      <div class="card-header text-center border-0 bg-transparent">
          <h3 class="text-white mb-4">Bienvenue dans l'espace de connexion</h3>
          <?php if (isset($_GET['error'])): ?>
              <div class="alert alert-danger rounded-pill">
                  <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
              </div>
          <?php endif; ?>
      </div>
      
      <div class="card-body p-4">
          <form id="loginForm" action="/courrier_coud/traitement/connect.php" method="post" class="full-width" novalidate>
              <div class="mb-4">
                  <input onkeydown="upperCaseF(this)" 
                        name="username" 
                        id="username" 
                        type="text" 
                        value=""
                        placeholder="Nom d'utilisateur" 
                        class="form-control form-control-lg rounded-pill border-0 bg-light bg-opacity-75 py-3 px-4" 
                        required>
              </div>
              
              <div class="mb-4">
                  <input name="password" 
                        type="password" 
                        id="password" 
                        value=""
                        placeholder="Mot de passe" 
                        class="form-control form-control-lg rounded-pill border-0 bg-light bg-opacity-75 py-3 px-4" 
                        required>
              </div>
              
              <div class="d-grid mb-3">
                  <button type="submit" 
                          class="btn btn-primary btn-lg rounded-pill py-3 fw-bold">
                      Se connecter
                  </button>
              </div>
              
                <div class="text-center text-white mt-4">
                  <a href="#" class="text-decoration-none text-white">
                    Mot de passe oublié ?
                  </a>
                </div>
                <div class="text-center text-white mt-2">
                  <a href="index.php" class="text-decoration-none text-white">
                    Retour
                  </a>
                </div>
          </form>
      </div>
  </div>
   
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
