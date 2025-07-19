<?php
require_once '../../traitement/fonction_facture.php';

// Vérifier si l'ID est passé en paramètre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../facture/liste_factures.php?error=id_invalide');
    exit();
}

$id_facture = intval($_GET['id']);
$resultat = supprimerFacture($id_facture);
?>

<!-- Modal de Résultat -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php if ($resultat): ?>
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="resultModalLabel">Suppression réussie</h5>
            </div>
            <div class="modal-body">
                <p>La facture a été supprimée avec succès.</p>
            </div>
            <div class="modal-footer">
                <a href="../../profils/facture/liste_factures.php" class="btn btn-success">Retour à la liste</a>
            </div>
            <?php else: ?>
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="resultModalLabel">Erreur lors de la suppression</h5>
            </div>
            <div class="modal-body">
                <p>Une erreur est survenue lors de la suppression de la facture.</p>
            </div>
            <div class="modal-footer">
                <a href="../../facture/liste_factures.php" class="btn btn-danger">Retour à la liste</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Afficher automatiquement le modal au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    var resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
    resultModal.show();
    
    // Redirection automatique après 3 secondes
    setTimeout(function() {
        window.location.href = "../../facture/liste_factures.php";
    }, 3000);
});
</script>
