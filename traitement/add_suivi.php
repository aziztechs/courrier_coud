<?php
// Connexion à la base de données
require_once('fonction.php');
$connexion = connexionBD();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instruction = $_POST['instruction'] ?? '';
    $statut = $_POST['statut'] ?? '';
    $date = $_POST['datetime'] ?? date('Y-m-d H:i:s'); // Valeur par défaut à la date actuelle

    // Obtenir les IDs de l'utilisateur et de l'imputation
    $id_user = $_SESSION['id_user'] ?? null;
    $id_imputation = $_POST['id_imputation'] ?? null;
    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] == UPLOAD_ERR_OK) {
        $fileType = mime_content_type($_FILES['pdf']['tmp_name']);
        if ($fileType != 'application/pdf') {
            echo "Type de fichier incorrect. Seuls les fichiers PDF sont autorisés.";
        }
        // Autres traitements pour le fichier
    } else {
        echo "Aucun fichier téléchargé ou erreur lors du téléchargement.";
    }
    
    if ($id_user && $id_imputation && $instruction && $statut) {
        // Traite le téléchargement du fichier PDF
        $targetDir = "c:\\xampp\\htdocs\\courrier_coud\\profils\\uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $uniqueId = uniqid();
        $pdfFileName = $uniqueId . "_" . basename($_FILES["pdf"]["name"]);
        $targetFilePath = $targetDir . $pdfFileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        if ($fileType != "pdf") {
            echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Type de fichier incorrect',
                        text: 'Seuls les fichiers PDF sont autorisés.',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.history.back();
                        }
                    });
                  </script>";
            return;
        }

        if (move_uploaded_file($_FILES["pdf"]["tmp_name"], $targetFilePath)) {
            // Insérer les données dans la base de données
            $query = "INSERT INTO suivi (Instruction, statut, id_user, id_imputation, date_suivi, pdf) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $connexion->prepare($query);
            $stmt->bind_param("ssisss", $instruction, $statut, $id_user, $id_imputation, $date, $pdfFileName);

            if ($stmt->execute()) {
                echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Suivi ajouté avec succès !',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var subrole = '" . $_SESSION['Fonction'] . "';
                                var redirectUrl = '/courrier_coud/profils/departement/pageDepCell/default.php'; // Valeur par défaut
            
                                switch (subrole) {
                                    case 'chef_courrier':
                                        redirectUrl = '/courrier_coud/profils/direction/accueil_direction.php';
                                        break;
                                    default:
                                        redirectUrl = 'defaultPage.php'; // Redirection par défaut
                                }
                                window.location.href = redirectUrl; // Redirection finale
                            }
                        });
                      </script>";
            }
             else {
                echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur d\'insertion',
                            text: 'Une erreur est survenue : " . $stmt->error . "',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.history.back();
                            }
                        });
                      </script>";
            }
            $stmt->close();
        } else {
            echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur de téléchargement',
                        text: 'Une erreur est survenue lors du téléchargement du fichier.',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.history.back();
                        }
                    });
                  </script>";
        }
    } else {
        echo "Tous les champs sont requis.";
    }
}

