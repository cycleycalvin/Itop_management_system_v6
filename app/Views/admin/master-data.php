<?php use App\Core\Security; use App\Core\View; ?>
<section class="container py-4">
    <?php View::partial('partials/role-nav'); ?>
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
        <div>
            <h1 class="h3 mb-1">ITOP Master Data</h1>
            <p class="text-muted mb-0">Maintain programme academies, categories, organisations, locations, professions, and 2018-2025 statistics.</p>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3">
        <?php foreach ($tables as $key => $meta): ?>
            <li class="nav-item"><a class="nav-link <?= $table === $key ? 'active' : '' ?>" href="index.php?page=admin-master-data&table=<?= Security::e($key) ?>"><?= Security::e(ucwords(str_replace('_', ' ', $key))) ?></a></li>
        <?php endforeach; ?>
        <li class="nav-item"><a class="nav-link <?= $table === 'training_statistics' ? 'active' : '' ?>" href="index.php?page=admin-master-data&table=training_statistics">Training Statistics</a></li>
    </ul>

    <?php if ($table === 'training_statistics'): ?>
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="panel">
                    <h2 class="h5">Add Statistic</h2>
                    <form method="post" action="index.php?page=save-training-statistic">
                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                        <select class="form-select mb-2" name="academy_id" required><option value="">Academy</option><?php foreach ($academies as $academy): ?><option value="<?= (int) $academy['id'] ?>"><?= Security::e($academy['code']) ?></option><?php endforeach; ?></select>
                        <select class="form-select mb-2" name="course_id"><option value="">Linked course</option><?php foreach ($courses as $course): ?><option value="<?= (int) $course['id'] ?>"><?= Security::e($course['title']) ?></option><?php endforeach; ?></select>
                        <input class="form-control mb-2" name="course_name" placeholder="Course name" required>
                        <input class="form-control mb-3" type="number" name="participants" placeholder="Participants" required>
                        <button class="btn btn-primary w-100">Save Statistic</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="panel table-responsive">
                    <table class="table align-middle"><thead><tr><th>Academy</th><th>Course</th><th>Participants</th><th></th></tr></thead><tbody>
                        <?php foreach ($statistics['training'] as $row): ?>
                            <tr><td><?= Security::e($row['academy_code']) ?></td><td><?= Security::e($row['course_name']) ?></td><td><?= (int) $row['participants'] ?></td><td class="text-end"><form method="post" action="index.php?page=delete-training-statistic"><input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
                        <?php endforeach; ?>
                    </tbody></table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="panel">
                    <h2 class="h5"><?= $editing ? 'Edit Record' : 'Create Record' ?></h2>
                    <form method="post" action="index.php?page=save-master-data">
                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                        <input type="hidden" name="table" value="<?= Security::e($table) ?>">
                        <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
                        <?php if (in_array('code', $tables[$table]['fields'], true)): ?><input class="form-control mb-2" name="code" value="<?= Security::e($editing['code'] ?? '') ?>" placeholder="Code"><?php endif; ?>
                        <input class="form-control mb-2" name="name" value="<?= Security::e($editing['name'] ?? '') ?>" placeholder="Name" required>
                        <?php if (in_array('description', $tables[$table]['fields'], true)): ?><textarea class="form-control mb-2" name="description" rows="3" placeholder="Description"><?= Security::e($editing['description'] ?? '') ?></textarea><?php endif; ?>
                        <?php if (in_array('location_id', $tables[$table]['fields'], true)): ?><select class="form-select mb-2" name="location_id"><option value="">Location</option><?php foreach ($locations as $location): ?><option value="<?= (int) $location['id'] ?>" <?= (int) ($editing['location_id'] ?? 0) === (int) $location['id'] ? 'selected' : '' ?>><?= Security::e($location['name']) ?></option><?php endforeach; ?></select><?php endif; ?>
                        <select class="form-select mb-3" name="status"><option value="active" <?= ($editing['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($editing['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select>
                        <button class="btn btn-primary w-100">Save</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="panel table-responsive">
                    <table class="table align-middle"><thead><tr><th>Name</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr><td><strong><?= Security::e($row['name']) ?></strong><?php if (!empty($row['code'])): ?><br><span class="small text-muted"><?= Security::e($row['code']) ?></span><?php endif; ?></td><td><?= Security::e($row['status'] ?? 'active') ?></td><td><div class="d-flex justify-content-end gap-2"><a class="btn btn-sm btn-outline-secondary" href="index.php?page=admin-master-data&table=<?= Security::e($table) ?>&edit=<?= (int) $row['id'] ?>">Edit</a><form method="post" action="index.php?page=delete-master-data"><input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>"><input type="hidden" name="table" value="<?= Security::e($table) ?>"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></div></td></tr>
                        <?php endforeach; ?>
                    </tbody></table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>
