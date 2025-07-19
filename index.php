<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COURRIER_COUD | Plateforme de Gestion de Courrier</title>
    
    <!-- Favicon -->
    <link rel="icon" href="log.gif" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0056b3;
            --secondary-color: #f8f9fa;
            --accent-color: #ffc107;
            --text-color: #333;
            --light-text: #6c757d;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            overflow-x: hidden;
        }
        
        .navbar-brand img {
            height: 50px;
            transition: all 0.3s ease;
        }
        
        .hero-section {
            background: url('assets/images/hero-bg.jpg') no-repeat center center;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
            position: relative;
            padding: 100px 0;
        }
        
        .hero-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
        
        .hero-text {
            flex: 1;
            padding-right: 50px;
        }
        
        .hero-buttons {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 20px;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2.5rem;
            font-weight: 300;
        }
        
        .btn-primary-custom {
            background-color: var(--primary-color);
            border: none;
            color: var(--text-color);
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 30px;
            transition: all 0.3s ease;
            width: 300px;
            text-align: center;
        }
        
        .btn-primary-custom:hover {
            background-color:rgb(48, 45, 234);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(209, 195, 195, 0.1);
        }
        
        .btn-outline-custom {
            border: 2px solid white;
            color: white;
            background: transparent;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 30px;
            transition: all 0.3s ease;
            width: 300px;
            text-align: center;
        }
        
        .btn-outline-custom:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .contact-section {
            padding: 60px 0;
            background-color: var(--secondary-color);
        }
        
        .contact-info {
            text-align: center;
            padding: 30px;
        }
        
        .contact-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            text-align: center;
        }
        
        .footer-logo img {
            height: 60px;
            margin-bottom: 20px;
        }
        
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--primary-color);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
        }
        
        .back-to-top.active {
            opacity: 1;
            visibility: visible;
        }
        
        @media (max-width: 992px) {
            .hero-container {
                flex-direction: column;
                text-align: center;
            }
            
            .hero-text {
                padding-right: 0;
                margin-bottom: 50px;
            }
            
            .hero-buttons {
                align-items: center;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-primary bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="assets/images/logo.png" alt="COURRIER_COUD">
                <span class="ms-2 text-white">Centre des Œuvres Universitaires de Dakar</span>
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-container">
                <div class="hero-text">
                    <h3 class="hero-subtitle">
                        <i class="fas fa-envelope me-2" style="color: var(--accent-color);"></i> MON COURRIER EN UN CLIC
                    </h3>
                    <h1 class="hero-title">
                        Bienvenue sur votre<br>
                        plateforme numérique<br>
                        de gestion de courriers
                    </h1>
                </div>
                <div class="hero-buttons">
                    <a href="aide.php" class="btn btn-primary-custom">
                        <i class="fas fa-question-circle me-2"></i> AIDE
                    </a>
                    <a href="login.php" class="btn btn-outline-custom">
                        <i class="fas fa-sign-in-alt me-2"></i> SE CONNECTER
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="contact-info">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4>Adresse</h4>
                        <p class="mb-0">
                            <strong>Département Informatique:</strong><br>
                            Rez de chaussée, Pavillon B, Grand Campus COUD
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-logo">
                <img src="assets/images/logo.png" alt="COURRIER_COUD">
            </div>
            <div class="mt-3">
                <p>Plateforme numérique de gestion de courriers du COUD</p>
                <p class="mb-0">© Copyright COUD 2025 - Tous droits réservés</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Back to Top Button
        window.addEventListener('scroll', function() {
            var backToTop = document.getElementById('backToTop');
            if (window.pageYOffset > 300) {
                backToTop.classList.add('active');
            } else {
                backToTop.classList.remove('active');
            }
        });
        
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>

</html>