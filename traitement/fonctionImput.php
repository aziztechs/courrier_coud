<?php
include_once 'fonction.php';

$mysqli = new mysqli('localhost', 'root', '', 'db_courrier');
if ($mysqli->connect_error) {
    die("Échec de la connexion : " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_courrier = isset($_POST['id_courrier']) ? $_POST['id_courrier'] : '';
    $stmt = $mysqli->prepare("INSERT INTO imputation (id_courrier, Instruction, departement, date_impu, instruction_personnalisee) VALUES (?, ?, ?, NOW(), ?)");

    foreach ($_POST['instructions'] as $departement => $instruction) {
        // Par défaut, pas d'instruction personnalisée
        $instruction_personnalisee = null;

        if ($instruction === 'Autre' && !empty($_POST['instructions_autre'][$departement])) {
            $instruction_personnalisee = $_POST['instructions_autre'][$departement];
        }

        $checkStmt = $mysqli->prepare("SELECT COUNT(*) FROM imputation WHERE id_courrier = ? AND departement = ?");
        $checkStmt->bind_param("is", $id_courrier, $departement);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count == 0) {
            $stmt->bind_param("isss", $id_courrier, $instruction, $departement, $instruction_personnalisee);
            if (!$stmt->execute()) {
                echo "Erreur d'exécution : " . $stmt->error;
            } else {
                echo "Imputation ajoutée avec succès pour le département $departement.";
            }
        } else {
            echo "Ce courrier a déjà été imputé à ce département.";
        }
    }

    $stmt->close();
    $mysqli->close();

    header('Location: ../profils/direction/accueil_direction.php');
    exit();
}
