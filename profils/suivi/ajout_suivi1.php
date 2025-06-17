<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

require_once('../../traitement/fonction_suivi_courrier.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $connexion->prepare("INSERT INTO suivi_courrier
                          (numero, date_reception, expediteur, objet, destinataire, statut_1, statut_2, statut_3) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", 
        $_POST['numero'],
        $_POST['date_reception'],
        $_POST['expediteur'],
        $_POST['objet'],
        $_POST['destinataire'],
        $_POST['statut_1'],
        $_POST['statut_2'],
        $_POST['statut_3']
    );
    
    if ($stmt->execute()) {
        header("Location: liste_suivi_courrier.php");
        exit();
    } else {
        $erreur = "Erreur lors de l'ajout";
    }
}

// Récupérer les valeurs ENUM pour les statuts
function get_enum_values($connexion, $column) {
    $result = $connexion->query("SHOW COLUMNS FROM suivi_courrier LIKE '$column'");
    $row = $result->fetch_assoc();
    preg_match("/^enum\(\'(.*)\'\)$/", $row['Type'], $matches);
    return explode("','", $matches[1]);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter un suivi</title>
</head>
<body>
    <h2>Ajouter un nouveau suivi</h2>
    
    <?php if (isset($erreur)) echo "<p style='color:red'>$erreur</p>"; ?>
    
    <form method="post">
        <div>
            <label>N°:</label>
            <input type="text" name="numero" required>
        </div>
        <div>
            <label>Date réception:</label>
            <input type="date" name="date_reception" required>
        </div>
        <div>
            <label>Expéditeur:</label>
            <input type="text" name="expediteur" required>
        </div>
        <div>
            <label>Objet:</label>
            <textarea name="objet" required></textarea>
        </div>
        <div>
            <label>Destinataire:</label>
            <input type="text" name="destinataire" required>
        </div>
        <div>
            <label>Statut 1:</label>
            <select name="statut_1" required>
                <?php foreach (get_enum_values($connexion, 'statut_1') as $value): ?>
                <option value="<?= $value ?>"><?= $value ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Statut 2:</label>
            <select name="statut_2" required>
                <?php foreach (get_enum_values($connexion, 'statut_2') as $value): ?>
                <option value="<?= $value ?>"><?= $value ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Statut 3:</label>
            <select name="statut_3" required>
                <?php foreach (get_enum_values($connexion, 'statut_3') as $value): ?>
                <option value="<?= $value ?>"><?= $value ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit">Ajouter</button>
    </form>
    
    <a href="liste_suivi_courrier.php">Retour à la liste</a>
</body>
</html>