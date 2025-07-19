<?php

require_once('../../traitement/fonction_facture.php');

header('Content-Type: application/json');

if (!isset($_GET['type']) || !isset($_GET['numero'])) {
    echo json_encode(['exists' => false]);
    exit();
}

$type = $_GET['type'];
$numero = trim($_GET['numero']);
$exists = false;

try {
    $connexion = connexionBD();
    
    if ($type === 'courrier') {
        $exists = numeroCourrierExiste($connexion, $numero);
    } elseif ($type === 'facture') {
        $exists = numeroFactureExiste($connexion, $numero);
    }
    
    echo json_encode(['exists' => $exists]);
} catch (Exception $e) {
    echo json_encode(['exists' => false, 'error' => $e->getMessage()]);
}