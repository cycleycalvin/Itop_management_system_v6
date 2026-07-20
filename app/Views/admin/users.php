<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
        <div>
            <h1 class="h3 mb-1">User Management</h1>
            <p class="text-muted mb-0">Manage administrators, instructors, and trainees.</p>
        </div>
        <form class="row g-2" method="get">
            <input type="hidden" name="page" value="admin-users">
            <div class="col-sm"><input class="form-control" name="q" value="<?= Security::e((string) $q) ?>" placeholder="Search users"></div>
            <div class="col-sm">
                <select class="form-select" name="status">
                    <option value="">All status</option>
                    <?php foreach (['pending', 'active', 'inactive', 'suspended'] as $state): ?>
                        <option value="<?= $state ?>" <?= $status === $state ? 'selected' : '' ?>><?= ucfirst($state) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-auto"><button class="btn btn-primary w-100">Filter</button></div>
        </form>
    </div>

    <ul class="nav nav-tabs mb-3">
        <?php foreach (['' => 'All Users', 'admin' => 'Administrators', 'instructor' => 'Instructors', 'trainee' => 'Trainees'] as $slug => $label): ?>
            <li class="nav-item"><a class="nav-link <?= $role === $slug ? 'active' : '' ?>" href="index.php?page=admin-users&role=<?= Security::e($slug) ?>&q=<?= urlencode((string) $q) ?>"><?= Security::e($label) ?></a></li>
        <?php endforeach; ?>
    </ul>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="panel">
                <h2 class="h5"><?= $editing ? 'Edit User' : 'Create User' ?></h2>
                <form method="post" action="index.php?page=save-user">
                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                    <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
                    <input class="form-control mb-2" name="name" value="<?= Security::e($editing['name'] ?? '') ?>" placeholder="Full name" required>
                    <input class="form-control mb-2" type="email" name="email" value="<?= Security::e($editing['email'] ?? '') ?>" placeholder="Email" required>
                    <input class="form-control mb-2" name="phone" value="<?= Security::e($editing['phone'] ?? '') ?>" placeholder="Phone">
                    <textarea class="form-control mb-2" name="address" rows="2" placeholder="Address"><?= Security::e($editing['address'] ?? '') ?></textarea>
                    <select class="form-select mb-2" name="role_slug">
                        <?php foreach ($roles as $item): ?>
                            <option value="<?= Security::e($item['slug']) ?>" <?= ($editing['role_slug'] ?? $role) === $item['slug'] ? 'selected' : '' ?>><?= Security::e($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="form-select mb-2" name="status">
                        <?php foreach (['pending', 'active', 'inactive', 'suspended'] as $state): ?>
                            <option value="<?= $state ?>" <?= ($editing['status'] ?? 'active') === $state ? 'selected' : '' ?>><?= ucfirst($state) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input class="form-control mb-3" type="password" name="password" placeholder="<?= $editing ? 'New password (optional)' : 'Password' ?>">
                    <button class="btn btn-primary w-100"><?= $editing ? 'Save Changes' : 'Create Account' ?></button>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="panel table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Name</th><th>Role</th><th>Status</th><th>Last Login</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><strong><?= Security::e($user['name']) ?></strong><br><span class="small text-muted"><?= Security::e($user['email']) ?></span></td>
                                <td><?= Security::e($user['role_name']) ?></td>
                                <td><span class="badge text-bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>"><?= Security::e($user['status']) ?></span></td>
                                <td class="small text-muted"><?= Security::e($user['last_login'] ?? '-') ?></td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a class="btn btn-sm btn-outline-secondary" href="index.php?page=admin-users&edit=<?= (int) $user['id'] ?>">View/Edit</a>
                                        <form method="post" action="index.php?page=delete-user" onsubmit="return confirm('Delete this user?')">
                                            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 small text-muted">
                <span>Page <?= (int) $pageNo ?> of <?= (int) $totalPages ?></span>
                <div class="btn-group">
                    <a class="btn btn-sm btn-outline-primary <?= $pageNo <= 1 ? 'disabled' : '' ?>" href="index.php?page=admin-users&p=<?= max(1, $pageNo - 1) ?>&role=<?= Security::e($role) ?>&q=<?= urlencode((string) $q) ?>">Previous</a>
                    <a class="btn btn-sm btn-outline-primary <?= $pageNo >= $totalPages ? 'disabled' : '' ?>" href="index.php?page=admin-users&p=<?= min($totalPages, $pageNo + 1) ?>&role=<?= Security::e($role) ?>&q=<?= urlencode((string) $q) ?>">Next</a>
                </div>
            </div>
        </div>
    </div>


