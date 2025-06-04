<?php
include '../../traitement/connect.php';
include '../../traitement/fonction_user.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    supprimerUtilisateur($id);
    header("Location: users.php");
    exit();
}
