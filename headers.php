<?php
    if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
        header('Location: /courrier_coud/');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>COURRIER_COUD - Administration</title> <!-- Titre plus descriptif -->
    <meta name="description" content="Interface d'administration du courrier - COURRIER_COUD" />
    <meta name="author" content="COURRIER_COUD" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="/courrier_coud/assets/images/log.gif" type="image/x-icon" />
    <link rel="icon" href="/courrier_coud/assets/images/log.gif" type="image/x-icon" />

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="/courrier_coud/assets/css/style.css">

    <style>
        /* Style pour le header principal */
        .s-header {
            display: flex;
            align-items: center;
            justify-content: space-between; /* Cela pousse les éléments vers les extrémités */
            padding: 15px 20px;
            background-color: #0056b3; /* Couleur de fond */
            color: white; /* Couleur du texte */
            border-bottom: 1px solid #e0e0e0;
        }

        .header-content {
            display: flex;
            align-items: center;
            width: 100%;
            justify-content: space-between; /* Espace entre logo et nav */
        }

        .header-logo {
            display: flex;
            align-items: center;
        }

        .header-logo img {
            height: 40px;
            margin-right: 15px;
        }

        /* Navigation à droite */
        .header-nav-right {
            margin-left: auto; /* Cela pousse la nav vers la droite */
        }

        .header-nav {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            margin-left: 20px;
        }

        /* Couleurs */
        .institution-name, 
        .welcome-text,
        .user-details,
        .btn--primary {
            color: #0056b3;
        }


        .btn--primary {
            text-decoration: none;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .btn--primary:hover {
            color: #003366;
            background-color: #f0f7ff;
        }

        /* Bannière d'information */
        .info-banner {
            background-color: #f8f9fa;
            padding: 10px 20px;
            display: flex;
            justify-content: flex-end;
            border-bottom: 1px solid #ddd;
        }

        .institution-name {
            font-weight: bold;
            font-size: 1.2em;
            margin-left: 10px;
            color:rgb(227, 229, 232);
        }

        .user-info-section {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .welcome-text {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 1.1em;
        }

        .user-details {
            font-style: italic;
            font-size: 0.9em;
            color: #555;
        }

        /* Menu burger pour mobile */
        .header-menu-toggle {
            display: none;
            cursor: pointer;
        }

        /* Style responsive */
        @media (max-width: 768px) {
            .header-nav {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 70px;
                right: 20px;
                background: white;
                padding: 15px;
                border-radius: 5px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }

            .header-nav.active {
                display: flex;
            }

            .nav-item {
                margin: 5px 0;
            }

            .header-menu-toggle {
                display: block;
            }
        }
    </style>
</head>

<body id="top">
    <!-- Header principal avec logo et navigation -->
    <header class="s-header bg-primary fixed-top">
        <div class="header-content">
            <div class="header-logo">
                <a class="site-logo" href="/courrier_coud/login.php">
                    <img src="/courrier_coud/assets/images/logo.png" alt="Logo COURRIER_COUD" />
                </a>
                <div class="institution-name">COURRIER_COUD</div>
            </div>
        </div>          
        <a class="header-menu-toggle" href="#0" id="menu-toggle"><span>Menu</span></a>
    </header>



    <script>
        // Script pour le menu burger en mobile
        document.getElementById('menu-toggle').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('main-nav').classList.toggle('active');
        });
    </script>
</body>
</html>
