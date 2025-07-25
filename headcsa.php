<?php
if (empty($_SESSION['username'])) {
    header('Location: /courrier_coud/');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>COURRIER_COUD - Administration</title>
    <meta name="description" content="Interface d'administration du courrier - COURRIER_COUD" />
    <meta name="author" content="COURRIER_COUD" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="stylesheet" href="../../assets/css/datatables.min.css">
    <link rel="stylesheet" href="../../assets/css/tableau.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">

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
            justify-content: space-between;
            padding: 15px 20px;
            background-color:rgb(17, 139, 253);
            color: white;
            border-bottom: 1px solid #e0e0e0;
        }

        .header-content {
            display: flex;
            align-items: center;
            width: 100%;
            justify-content: space-between;
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
            margin-left: auto;
        }

        .header-nav {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            margin-left: 5px;
            position: relative;
        }

        /* Style pour l'élément actif */
        .nav-item.active a {
            background-color: #003366;
            color: white !important;
            border-radius: 4px;
        }

        /* Couleurs */
        .institution-name, 
        .welcome-text,
        .user-details,
        .btn--primary {
            color:rgb(233, 234, 235);
        }

        .btn--primary {
            text-decoration: none;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .btn--primary:hover {
            color: #8cbff3ff;
            background-color:rgba(231, 231, 235, 1);
        }

        /* Style pour les liens désactivés */
        .disabled-link {
            pointer-events: none;
            opacity: 0.5;
            cursor: not-allowed;
            text-decoration: none;
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
                background: #0076FF;
                padding: 15px;
                border-radius: 5px;
                box-shadow: 0 2px 10px rgba(229, 234, 235, 0.1);
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
    <header class="s-header fixed-top" style="background-color: #0076FF">
        <div class="header-content">
            <div class="header-logo">
                <a class="site-logo" href="/courrier_coud/profils/courrier/dashboard.php">
                    <img src="/courrier_coud/assets/images/logo.png" alt="Logo COURRIER_COUD" />
                </a>
                <div class="institution-name" style="color: #fff; margin-top: 3px;">COURRIERS COUD</div>
            </div>

            <div class="header-nav-right ">
                <ul class="header-nav" id="main-nav">
                    <li class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']),  ['dashboard_csa.php','view_courriercsa.php']) ? 'active' : '' ?>">
                        <a class="btn--primary text-dark" href="/courrier_coud/profils/dashboards/dashboard_csa.php">
                            <i class="fa fa-dashboard" aria-hidden="true"></i> TABLEAU DE BORD 
                        </a>
                    </li>
                    <li class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']), ['liste_courrierscsa.php', 'ajouter_courriercsa.php', 'modifier_courriercsa.php']) ? 'active' : '' ?>">
                        <a class="btn--primary text-dark" href="/courrier_coud/profils/courrierscsa/liste_courrierscsa.php">
                            <i class="fas fa-envelope" aria-hidden="true"></i> COURRIERS 
                        </a>
                    </li>
                    
                    <?php if(!isset($_SESSION['Fonction']) || $_SESSION['Fonction'] !== 'assistant_courrier' && $_SESSION['Fonction'] !== 'directeur'): ?>
                    <li class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']),['liste_suivi_courrierscsa.php', 'ajouter_suivi_courriercsa.php', 'modifier_suivi_courriercsa.php']) ? 'active' : '' ?>">
                        <a class="btn--primary text-dark" href="/courrier_coud/profils/suiviscourcsa/liste_suivi_courrierscsa.php">
                            <i class="fas fa-eye" aria-hidden="true"></i> SUIVI COURRIERS 
                        </a>
                    </li>
                    <?php endif; ?>
                
                    <li class="nav-item">
                        <a class="btn--primary text-danger" href="/courrier_coud/logout.php" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                            <i class="fa fa-sign-out-alt" aria-hidden="true"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <a class="header-menu-toggle" href="#0" id="menu-toggle"><span>Menu</span></a>
    </header> <br><br>

    <script>
        // Script pour le menu burger en mobile
        document.getElementById('menu-toggle').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('main-nav').classList.toggle('active');
        });
    </script>
</body>
</html>