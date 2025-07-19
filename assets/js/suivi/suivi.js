/**
 * Gestion du formulaire d'ajout de suivi - COUD
 * Valide les champs, vérifie les numéros en AJAX, et gère les statuts "AUTRE".
 */

document.addEventListener('DOMContentLoaded', () => {
    // Configuration de flatpickr (dates)
    flatpickr("#date_reception", {
        dateFormat: "d-m-Y",
        allowInput: true,
        locale: "fr",
        defaultDate: "today"
    });

    // Vérification asynchrone du numéro de suivi
    const numeroInput = document.getElementById('numero');
    if (numeroInput) {
        numeroInput.addEventListener('blur', async () => {
            const numero = numeroInput.value.trim();
            const feedback = document.getElementById('numero-feedback');

            if (!/^\d{4}-\d{3}$/.test(numero)) {
                showError(numeroInput, feedback, 'Format invalide (doit être YYYY-NNN)');
                return;
            }

            try {
                const response = await fetch(`verifier_numero.php?numero=${encodeURIComponent(numero)}`);
                const data = await response.json();
                
                if (data.exists) {
                    showError(numeroInput, feedback, 'Ce numéro existe déjà');
                } else {
                    clearError(numeroInput, feedback);
                }
            } catch (error) {
                console.error('Erreur réseau:', error);
                showError(numeroInput, feedback, 'Erreur de vérification');
            }
        });
    }

    // Gestion des champs "AUTRE" pour les statuts
    setupStatutField('statut_1', 'statut_1_autre_container', 'statut_1_autre');
    setupStatutField('statut_2', 'statut_2_autre_container', 'statut_2_autre');
    setupStatutField('statut_3', 'statut_3_autre_container', 'statut_3_autre');

    // Validation du formulaire
    const form = document.getElementById('suiviForm');
    if (form) {
        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                scrollToFirstInvalidField();
            }
            form.classList.add('was-validated');
        });
    }

    // Gestion des messages d'erreur pour les champs requis
    const requiredFields = document.querySelectorAll('.form-control[required]');
});

// --- Fonctions utilitaires ---
function showError(input, feedback, message) {
    input.classList.add('is-invalid');
    feedback.textContent = message;
    feedback.classList.remove('d-none');
}

function clearError(input, feedback) {
    input.classList.remove('is-invalid');
    feedback.classList.add('d-none');
}

function setupStatutField(selectId, containerId, inputId) {
    const select = document.getElementById(selectId);
    const container = document.getElementById(containerId);
    const input = document.getElementById(inputId);

    if (select && container && input) {
        select.addEventListener('change', () => {
            const isAutre = select.value === 'AUTRE';
            container.classList.toggle('d-none', !isAutre);
            input.required = isAutre;
            if (!isAutre) input.value = '';
        });
    }
}

function scrollToFirstInvalidField() {
    const invalidField = document.querySelector('.is-invalid');
    if (invalidField) {
        invalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}