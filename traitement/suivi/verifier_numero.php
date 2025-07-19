<?php
require_once('../../traitement/fonction_suivi_courrier.php');

header('Content-Type: application/json');

if (isset($_GET['numero'])) {
    $numero = trim($_GET['numero']);
    
    try {
        $stmt = $connexion->prepare("SELECT id FROM suivi_courrier WHERE numero = ? LIMIT 1");
        $stmt->bind_param("s", $numero);
        $stmt->execute();
        $stmt->store_result();
        
        echo json_encode(['exists' => $stmt->num_rows > 0]);
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['exists' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['exists' => false]);
}