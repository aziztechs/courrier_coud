<?php
    session_start();
    if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
        header('Location: /courrier_coud/');
        session_destroy();
        exit();
    }

    include '../../traitement/fonction_user.php';
    include '../../traitement/connect.php';

    // Génération du token CSRF
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $limit = 5; // Nombre d'utilisateurs par page
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    if ($search) {
        $users = searchUsers($search, $limit, $offset);
        $totalUsers = getSearchUsersCount($search);
    } else {
        $users = getAllUsersPaginated($limit, $offset);
        $totalUsers = getAllUsersCount();
    }

    $totalPages = ceil($totalUsers / $limit);
    
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil Direction - Gestion Courrier</title>
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/usersliste.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body>
    <?php include('../../head.php'); ?>
    <div class="info-banner text-white d-flex justify-content-center align-items-center"
         style="height: 140px; background-color: #0056b3;">
        <div class="welcome-text"></div>
        <div class="user-info-section">
            <p class="lead">Espace Administration : Liste des Utilisateurs !<br>
                <span>
                    (<?php echo htmlspecialchars($_SESSION['Prenom'] . ' ' . $_SESSION['Nom'] . ' - ' . $_SESSION['Fonction']); ?>)
                </span>
            </p>
        </div>
    </div>  

    <div class="container-table mt-4">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                <h1 class="text-center"><i class="fa fa-users"></i>&nbsp; LISTE DES UTILISATEURS</h1>
                <div class="d-flex align-items-center">
                    <span class="badge bg-light text-dark me-3"><?= $totalUsers ?> résultat(s)</span>
                    <div class="d-flex gap-2">
                        <a href="addUser.php" class="btn btn-sm btn-light text-primary">
                            <i class="fas fa-plus-circle me-1"></i> Nouvel
                        </a>
                    </div>
                </div>
            </div>
            
            <form method="get" class="row mt-3">
                <div class="col-md-10 offset-md-1">
                    <div class="d-flex">
                        <input type="text" name="search" class="form-control rounded-end-0" 
                               placeholder="Rechercher un utilisateur..." 
                               value="<?= htmlspecialchars($search) ?>"
                               style="width: 100%; height: 40px;">
                        <button type="submit" class="btn btn-primary rounded-start-0" style="height: 40px;">
                            <i class="bi bi-search"></i> 
                        </button>
                    </div>
                </div>
            </form>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">Nom</th>
                                <th class="text-center">Prénom</th>
                                <th class="text-center">Username</th>
                                <th class="text-center">Email</th>
                                <th class="text-center">Téléphone</th>
                                <th class="text-center">Fonction</th>
                                <th class="text-center">Matricule</th>
                                <th class="text-center">Statut</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($users) && count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                <tr style="text-align: center; font-size: 1.3em;">
                                    <td class="text-center"><?= htmlspecialchars($user['Nom']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($user['Prenom']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($user['Username']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($user['email']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($user['Tel']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($user['Fonction']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($user['Matricule']) ?></td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input toggle-status" 
                                                type="checkbox" 
                                                data-userid="<?= $user['id_user'] ?>"
                                                <?= $user['Actif'] ? 'checked' : '' ?>
                                                style="width: 3em; height: 1.5em;">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="editUser.php?id=<?= $user['id_user'] ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>&nbsp;
                                        </a>
                                        <!-- Mettre à jour le bouton de suppression -->
                                        <button class="btn btn-sm btn-danger delete-user" 
                                                data-userid="<?= $user['id_user'] ?>"
                                                data-username="<?= htmlspecialchars($user['Nom'] . ' ' . $user['Prenom']) ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">Aucun utilisateur trouvé</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>          
            </div>                
        </div> 
    </div>

    <?php if ($totalPages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&search='.urlencode($search) : '' ?>">Précédent</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&search='.urlencode($search) : '' ?>">Suivant</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Configuration commune pour les requêtes AJAX
            $.ajaxSetup({
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Gestion du toggle actif/inactif
            $('.toggle-status').change(function() {
                const $toggle = $(this);
                const userId = $toggle.data('userid');
                const isActive = $toggle.is(':checked') ? 1 : 0;
                
                // Désactiver le toggle pendant la requête
                $toggle.prop('disabled', true);
                
                $.ajax({
                    url: '../../traitement/toggleUserStatus.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        user_id: userId,
                        status: isActive,
                        csrf_token: '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>'
                    },
                    success: function(response) {
                        if (response?.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Statut mis à jour',
                                text: response.message || 'Le statut a été modifié avec succès',
                                timer: 2000,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                        } else {
                            showError(response?.message || 'Erreur lors de la mise à jour du statut');
                            $toggle.prop('checked', !isActive);
                        }
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Erreur de communication avec le serveur';
                        showError(errorMsg);
                        $toggle.prop('checked', !isActive);
                    },
                    complete: function() {
                        $toggle.prop('disabled', false);
                    }
                });
            });

             // Gestion de la suppression avec améliorations
    $('.delete-user').click(function(e) {
        e.preventDefault();
        const userId = $(this).data('userid');
        const username = $(this).data('username') || 'cet utilisateur';
        const csrfToken = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>';

        Swal.fire({
            title: 'Confirmer la suppression',
            html: `Êtes-vous sûr de vouloir supprimer <b>${escapeHtml(username)}</b> ?<br>
                  <small class="text-danger">Cette action est irréversible.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash"></i> Oui, supprimer',
            cancelButtonText: '<i class="fas fa-times"></i> Annuler',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch(`../../traitement/deleteUser.php?id=${userId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `csrf_token=${encodeURIComponent(csrfToken)}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(response.statusText);
                    }
                    return response.json();
                })
                .catch(error => {
                    Swal.showValidationMessage(
                        `Erreur serveur : ${error.message}`
                    );
                    return false;
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value && result.value.success) {
                    Swal.fire({
                        title: 'Supprimé!',
                        html: `<i class="fas fa-check-circle text-success"></i> ${result.value.message || 'Utilisateur supprimé avec succès'}`,
                        icon: 'success',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        willClose: () => location.reload()
                    });
                } else {
                    Swal.fire(
                        'Erreur',
                        result.value?.message || 'La suppression a échoué',
                        'error'
                    );
                }
            }
        });
    });
    // Fonction utilitaire pour afficher les erreurs
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: message,
            timer: 3000,
            timerProgressBar: true
        });
    }

    // Fonction de protection XSS
    function escapeHtml(unsafe) {
        return unsafe?.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';
            }
        });

         
    </script>
</body>
</html>