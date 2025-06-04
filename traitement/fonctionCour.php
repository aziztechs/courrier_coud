<?php
// Inclure le fichier de fonction et établir la connexion
require_once('fonction.php');
$connexion = connexionBD();

function enregistrerCourrier($formData, $fileData) {
    global $connexion;

    // Vérifier la connexion
    if ($connexion->connect_error) {
        die("La connexion a échoué : " . $connexion->connect_error);
    }

    // Récupérer les données du formulaire avec vérification de chaque clé
    $numero = isset($formData['numero']) ? $formData['numero'] : null;
    $date = isset($formData['datetime']) ? $formData['datetime'] : null;
    $objet = isset($formData['objet']) ? $formData['objet'] : null;
    $nature = isset($formData['nature']) ? $formData['nature'] : null;
    $type = isset($formData['Type']) ? $formData['Type'] : '';  // Valeur par défaut vide si absent
    $expediteur = isset($formData['expediteur']) ? $formData['expediteur'] : null;

    // Vérifier si le numéro de courrier existe déjà
    $checkQuery = "SELECT COUNT(*) AS total FROM courrier WHERE Numero_Courrier = ?";
    $stmt = $connexion->prepare($checkQuery);
    $stmt->bind_param("s", $numero);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['total'] > 0) {
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Numéro déjà utilisé',
                    text: 'Ce numéro de courrier existe déjà. Veuillez en choisir un autre.',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.history.back();
                    }
                });
              </script>";
        return;
    }

    // Traite le téléchargement du fichier PDF
    $targetDir = "c:\\xampp\\htdocs\\courrier_coud\\profils\\uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $uniqueId = uniqid();
    $pdfFileName = $uniqueId . "_" . basename($fileData["pdf"]["name"]);
    $targetFilePath = $targetDir . $pdfFileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    if ($fileType != "pdf") {
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Type de fichier incorrect',
                    text: 'Désolé, seuls les fichiers PDF sont autorisés.',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.history.back();
                    }
                });
              </script>";
        return;
    }

    if (move_uploaded_file($fileData["pdf"]["tmp_name"], $targetFilePath)) {
        $sql = "INSERT INTO courrier (Numero_Courrier, Date, Objet, pdf, Nature, Type, Expediteur) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param("sssssss", $numero, $date, $objet, $pdfFileName, $nature, $type, $expediteur);

        if ($stmt->execute()) {
            echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Courrier Enregistré',
                        text: 'Le courrier a été enregistré avec succès.',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '../profils/courrier/liste_courrier.php';
                        }
                    });
                  </script>";
        } else {
            echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur d\'insertion',
                        text: 'Une erreur est survenue lors de l\'enregistrement du courrier : " . $stmt->error . "',
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
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    enregistrerCourrier($_POST, $_FILES);
}
?>
