<div class="container mt-5 pt-5">
    <div class="row">
        <!-- Colonne gauche : profil admin -->
        <div class="col-md-6">
            <h2 class="fw-bold mb-4 text-center">Information</h2>
            <form method="POST" action="/profile/update">
                <div class="mb-3">
                    <label class="form-label">Prénom :</label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nom :</label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email :</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nouveau mot de passe :</label>
                    <input type="password" name="password" class="form-control" placeholder="Laisser vide pour ne pas changer">
                </div>
                <div class="text-end">
                    <button type="reset" class="btn btn-outline-secondary me-2">Réinitialiser</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>

        <!-- Colonne droite : gestion des droits -->
        <div class="col-md-6">
            <h2 class="fw-bold mb-4 text-center">Droits</h2>

            <table class="table table-striped table-hover text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['last_name']); ?></td>
                        <td><?= htmlspecialchars($u['first_name']); ?></td>
                        <td><?= htmlspecialchars($u['email']); ?></td>
                        <td>
                            <form method="POST" action="/profile/updateRole" class="d-flex justify-content-center">
                                <input type="hidden" name="id" value="<?= $u['id']; ?>">
                                <select name="role" class="form-select form-select-sm w-auto me-2">
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <option value="admin" selected>Administrateur</option>
                                    <?php else: ?>
                                        <option value="user" <?= $u['role'] === 'user' ? 'selected' : ''; ?>>Utilisateur</option>
                                    <option value="editor" <?= $u['role'] === 'editor' ? 'selected' : '' ?>>Autorisé (Import)</option>
                                    <?php endif; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Modifier</button>
                            </form>
                        </td>
                        <td><a href="/dashboard" class="btn btn-sm btn-outline-secondary">Dashboard</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
