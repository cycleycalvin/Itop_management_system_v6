<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<span class="section-label">Instructor Enrolments</span>
<h1 class="section-title">Course Enrolments</h1>

<!-- ── Status Tabs ───────────────────────────────── -->
<?php $statusFilter = Security::cleanString($_GET['status'] ?? ''); ?>
<div class="status-tabs">
    <a class="status-tab <?= $statusFilter === '' ? 'active' : '' ?>" href="index.php?page=instructor-enrolments">All</a>
    <a class="status-tab <?= $statusFilter === 'pending' ? 'active' : '' ?>" href="index.php?page=instructor-enrolments&status=pending">Pending</a>
    <a class="status-tab <?= $statusFilter === 'active' ? 'active' : '' ?>" href="index.php?page=instructor-enrolments&status=active">Active</a>
    <a class="status-tab <?= $statusFilter === 'completed' ? 'active' : '' ?>" href="index.php?page=instructor-enrolments&status=completed">Completed</a>
</div>

<div class="overview-panel animate-in">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Trainee</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $filtered = $enrolments;
                if ($statusFilter) {
                    $filtered = array_filter($enrolments, fn($r) => ($r['status'] ?? '') === $statusFilter);
                }
                ?>
                <?php foreach ($filtered as $row): ?>
                    <tr>
                        <td>
                            <strong><?= Security::e($row['trainee_name']) ?></strong><br>
                            <span class="small text-muted"><?= Security::e($row['email']) ?></span>
                        </td>
                        <td><?= Security::e($row['course_title']) ?></td>
                        <td>
                            <span class="completion-item-badge <?= ($row['status'] ?? 'pending') === 'active' ? '' : 'in-progress' ?>"><?= ucfirst(Security::e($row['status'] ?? 'pending')) ?></span>
                        </td>
                        <td class="small text-muted"><?= Security::e($row['created_at']) ?></td>
                        <td>
                            <form class="d-flex justify-content-end gap-2" method="post" action="index.php?page=instructor-enrolment-status">
                                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                <?php if (($row['status'] ?? '') === 'pending'): ?>
                                    <button name="status" value="active" class="btn btn-sm btn-success">Approve</button>
                                    <button name="status" value="rejected" class="btn btn-sm btn-outline-danger">Reject</button>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($filtered)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No enrolments found for your courses.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
