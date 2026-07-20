<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<span class="section-label">Enrolment Management</span>
<h1 class="section-title">All Enrolments</h1>

<!-- ── Status Tabs ───────────────────────────────── -->
<?php $statusFilter = Security::cleanString($_GET['status'] ?? ''); ?>
<div class="status-tabs">
    <a class="status-tab <?= $statusFilter === '' ? 'active' : '' ?>" href="index.php?page=admin-enrolments">All</a>
    <a class="status-tab <?= $statusFilter === 'pending' ? 'active' : '' ?>" href="index.php?page=admin-enrolments&status=pending">Pending</a>
    <a class="status-tab <?= $statusFilter === 'active' ? 'active' : '' ?>" href="index.php?page=admin-enrolments&status=active">Active</a>
    <a class="status-tab <?= $statusFilter === 'completed' ? 'active' : '' ?>" href="index.php?page=admin-enrolments&status=completed">Completed</a>
    <a class="status-tab <?= $statusFilter === 'rejected' ? 'active' : '' ?>" href="index.php?page=admin-enrolments&status=rejected">Rejected</a>
</div>

<div class="overview-panel animate-in">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Trainee</th>
                    <th>Course</th>
                    <th>Instructor</th>
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
                        <td><?= Security::e($row['instructor_name'] ?? 'Unassigned') ?></td>
                        <td>
                            <span class="completion-item-badge <?= ($row['status'] ?? 'pending') === 'active' ? '' : (($row['status'] ?? '') === 'completed' ? '' : 'in-progress') ?>"><?= ucfirst(Security::e($row['status'] ?? 'pending')) ?></span>
                        </td>
                        <td class="small text-muted"><?= Security::e($row['created_at']) ?></td>
                        <td>
                            <form class="d-flex justify-content-end gap-2" method="post" action="index.php?page=set-enrolment-status">
                                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                <?php if (($row['status'] ?? '') === 'pending'): ?>
                                    <button name="status" value="active" class="btn btn-sm btn-success">Approve</button>
                                    <button name="status" value="rejected" class="btn btn-sm btn-outline-danger">Reject</button>
                                <?php elseif (($row['status'] ?? '') === 'active'): ?>
                                    <button name="status" value="completed" class="btn btn-sm btn-outline-primary">Complete</button>
                                    <button name="status" value="withdrawn" class="btn btn-sm btn-outline-danger">Withdraw</button>
                                <?php elseif (($row['status'] ?? '') === 'rejected'): ?>
                                    <button name="status" value="active" class="btn btn-sm btn-outline-success">Re-approve</button>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($filtered)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No enrolments found for this filter.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
