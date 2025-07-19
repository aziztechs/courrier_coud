<?php
// Récupération des statistiques
$stats = [
    'total_courriers' => 0,
    'courriers_arrivee' => 0,
    'courriers_depart' => 0,
    'courriers_internes' => 0,
    'courriers_externes' => 0,
    'courriers_non_traites' => 0,
    'courriers_suivi' => 0 // Si vous avez une table d'archives, vous pouvez l'ajouter ici
];

// Requêtes pour les statistiques
$queries = [
    'total_courriers' => "SELECT COUNT(*) FROM courriercsa",
    'courriers_arrivee' => "SELECT COUNT(*) FROM courriercsa WHERE Nature = 'arrive'",
    'courriers_depart' => "SELECT COUNT(*) FROM courriercsa WHERE Nature = 'depart'",
    'courriers_internes' => "SELECT COUNT(*) FROM courriercsa WHERE Type = 'interne'",
    'courriers_externes' => "SELECT COUNT(*) FROM courriercsa WHERE Type = 'externe'",
    'courriers_non_traites' => "SELECT COUNT(DISTINCT c.id_courrier) FROM courriercsa c LEFT JOIN imputation i ON c.id_courrier = i.id_courrier WHERE i.id_imputation IS NULL"
];




foreach ($queries as $key => $sql) {
    $result = $connexion->query($sql);
    $stats[$key] = $result->fetch_row()[0];
}

// Récupération des derniers courriers
$query = "SELECT c.*, COUNT(i.id_imputation) as imputations 
          FROM courriercsa c 
          LEFT JOIN imputation i ON c.id_courrier = i.id_courrier 
          GROUP BY c.id_courrier 
          ORDER BY c.id_courrier  DESC 
          LIMIT 5";
$last_courriers = $connexion->query($query)->fetch_all(MYSQLI_ASSOC);

