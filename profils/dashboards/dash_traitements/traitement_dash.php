<?php


// Récupération des statistiques
$stats = [
    'total_courriers' => 0,
    'courriers_arrivee' => 0,
    'courriers_depart' => 0,
    'courriers_internes' => 0,
    'courriers_externes' => 0,
    'courriers_non_traites' => 0,
    'courriers_archives' => 0 // Si vous avez une table d'archives, vous pouvez l'ajouter ici
];

// Requêtes pour les statistiques
$queries = [
    'total_courriers' => "SELECT COUNT(*) FROM courrier",
    'courriers_arrivee' => "SELECT COUNT(*) FROM courrier WHERE Nature = 'arrive'",
    'courriers_depart' => "SELECT COUNT(*) FROM courrier WHERE Nature = 'depart'",
    'courriers_internes' => "SELECT COUNT(*) FROM courrier WHERE Type = 'interne'",
    'courriers_externes' => "SELECT COUNT(*) FROM courrier WHERE Type = 'externe'",
    'courriers_non_traites' => "SELECT COUNT(DISTINCT c.id_courrier) FROM courrier c LEFT JOIN imputation i ON c.id_courrier = i.id_courrier WHERE i.id_imputation IS NULL"
];

// Si vous avez une table d'archives, vous pouvez ajouter une requête pour les courriers archivés
$queries['courriers_archives'] = "SELECT COUNT(*) FROM archive WHERE date_archivage IS NOT NULL";


foreach ($queries as $key => $sql) {
    $result = $connexion->query($sql);
    $stats[$key] = $result->fetch_row()[0];
}

// Récupération des derniers courriers
$query = "SELECT c.*, COUNT(i.id_imputation) as imputations 
          FROM courrier c 
          LEFT JOIN imputation i ON c.id_courrier = i.id_courrier 
          GROUP BY c.id_courrier 
          ORDER BY c.id_courrier  DESC 
          LIMIT 5";
$last_courriers = $connexion->query($query)->fetch_all(MYSQLI_ASSOC);

