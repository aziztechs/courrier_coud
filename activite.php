<script type="text/javascript">
var theTime;

// Fonction qui met à jour le temps actuel à chaque mouvement de souris ou pression de touche
document.onmousemove = stockTime;
document.onkeydown = stockTime; // Pour prendre en compte les actions du clavier

function stockTime() {
    currentTime = new Date();
    theTime = currentTime.getTime();  // Stocke l'heure actuelle
}

function verifTime() {
    currentTime = new Date();
    var timeNow = currentTime.getTime();
    
    // Si le temps écoulé depuis la dernière action est supérieur à 3 minutes (180000 ms)
    if (timeNow - theTime > 60000) {
        // Vous pouvez personnaliser ce message ou l'action ici
        alert('Votre session a expiré. Veuillez vous reconnecter!');
        // Rediriger l'utilisateur vers la page de connexion ou autre action
        top.location.href = "https://localhost/courrier_coud/login.php";  // Par exemple
    }
}

// Exécution de la fonction `verifTime` toutes les 3 minutes
window.setInterval(verifTime, 100000);  // Vérifie toutes les 3 minutes (300000 ms)
</script>
