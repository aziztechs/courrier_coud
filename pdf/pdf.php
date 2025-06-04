<?php
/*session_start();
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
    header('Location: /COUD/codif/');
    exit();
}
require('../traitement/fonction.php');
require_once __DIR__ . '/vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf();
// Appel des fonction de la page fonction.php
$tab = getLitOneStudentByConvention($_SESSION['lit_choisi']);
$date_formatee = getDateLitByStudent($_SESSION['id_lit']);
$pdfcontent = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
</head>
<body>
    <div class="container">
        <div class="row">
            <header>
                <div class="row">
                    <div class="col-md-6">
                        <p>Ministére de l\'Enseignement<br>Supérieur et de la Recherche <br/>
                            <u>________________________</u><br/>
                            <b> CENTRE DES ŒUVRES UNIVERSITAIRES DE DAKAR</b>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <div class="data-room">
                            N° Chambre : <b>' . $tab[0]['chambre'] . '</b><br>
                            Pallion : <b>' . $tab[0]['pavillon'] . '</b><br>
                            Campus : <b>' . $tab[0]['campus'] . '</b><br>
                            Caution : <br> 
                            Taux / Mois : 
                        </div>
                    </div>
                    <h4>N°:________________________________</h4>
                </div>                
            </header>
            <div class="row">
                <div class="col-md-12">
                    <div class="text-center">
                        CONVENTION D\'HEBERGEMENT
                    </div>
                    <h6>ANNEE UNIVERSITAIRE ' . date("Y") - 1 . '/' . date("Y") . '</h6> 
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <label>Prénoms (s) : ..............<b>' . $_SESSION['prenom'] . '</b>.....................</label>
                    <label>Nom : ......................<b>' . $_SESSION['nom'] . '</b>.....................</label>
                </div><br/>
                <div class="col-md-12">
                    <label>Date de naissance : ....<b>' . $_SESSION['dateNaissance'] . '</b>.......................</label>
                    <label>Lieu : .....................<b>' . $_SESSION['lieuNaissance'] . '</b>....................</label>
                </div><br/>
                <div class="col-md-12">
                    <label>Nationalité : ................<b>' . $_SESSION['nationalite'] . '</b>.....................</label>
                    <label>Bourse : .....................................</label>
                </div><br/>
                <div class="col-md-12">
                    <label>Faculté : ......................<b>' . $_SESSION['etablissement'] . '</b>.........................</label>
                    <label>Niveau : ..................<b>' . $_SESSION['niveau'] . '</b>........................</label>
                </div><br/>
                <div class="col-md-12">
                    <label>N° carte COUD : .........<b>' . $_SESSION['num_etu '] . '</b>......................</label>
                    <label>N° CE : ...................<b>' . $_SESSION['num_etu '] . '</b>.....................</label>
                </div><br/>
                <div class="col-md-12">
                    <label>Bénéficie de l\'hebergement : .........................................................................................</label>
                </div>
            </div><br/>
            <div class="row">
                <div class="col-md-12">
                    <div class="extrait">
                        EXTRAIT DU REGLEMENT INTERIEUR
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="reglement">
                        <p>
                            <b>Article 16</b><br/>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Sunt sint, architecto, asperiores labore rem quibusdam praesentium porro aperiam ipsum quis optio consequatur temporibus! Doloremque ad nulla nam incidunt asperiores. Ab.
                        </p>    
                        <p>
                            <b>Article 17</b><br/>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Sunt sint, architecto, asperiores labore rem quibusdam praesentium porro aperiam ipsum quis optio consequatur temporibus! Doloremque ad nulla nam incidunt asperiores. Ab.
                        </p>
                        <p>   
                            <b>Article 18</b><br/>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Sunt sint, architecto, asperiores labore rem quibusdam praesentium porro aperiam ipsum quis optio consequatur temporibus! Doloremque ad nulla nam incidunt asperiores. Ab.
                        </p>
                        <p>    
                            <b>N.B</b><br/>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Sunt sint, architecto, asperiores labore rem quibusdam praesentium porro aperiam ipsum quis optio consequatur temporibus! Doloremque ad nulla nam incidunt asperiores. Ab.
                        </p>
                    </div>
                </div>
            </div>
            <div class="row mb-0">
                <div class="col-md-12">
                    <div class="faitDakar">Dakar, le ' . $date_formatee . '</div>
                </div>
            </div>
            <div class="row">  
                <div class="col-md-3">
                    <div class="table-cell">Pour le Directeur du COUD <br/> Le Chef du service de l\'hebergement</div>
                </div>
                <div class="col-md-3">
                    <div class="right-align">Lu et Appouvé<br/>Le Locataire</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
                ';
$mpdf->WriteHTML($pdfcontent);
if ($result > 10) {
    $i++;
}
$mpdf->SetDisplayMode('fullpage');
$mpdf->list_indent_first_level = 0;

//call watermark content and image
//$mpdf->SetWatermarkText('COUD');
$mpdf->showWatermarkText = true;
$mpdf->watermarkTextAlpha = 0.1;

//output in browser
$mpdf->Output();
*/