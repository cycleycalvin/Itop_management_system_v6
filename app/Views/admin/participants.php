<?php use App\Core\Security; ?>

<div class="d-flex justify-content-between align-items-md-center flex-column flex-md-row mb-4 gap-2">
    <div>
        <span class="section-label">Management</span>
        <h1 class="section-title mb-0">Participants</h1>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <form class="d-flex gap-2" method="get" action="index.php">
            <input type="hidden" name="page" value="admin-participants">
            <input type="text" class="form-control form-control-sm" name="q" placeholder="Search by name or email..." value="<?= Security::e($q) ?>" style="min-width: 220px;">
            <button class="btn btn-primary btn-sm" type="submit">Search</button>
            <?php if ($q): ?><a href="index.php?page=admin-participants" class="btn btn-outline-secondary btn-sm">Clear</a><?php endif; ?>
        </form>
        <button class="btn btn-outline-primary btn-sm" onclick="exportParticipants()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export CSV
        </button>
    </div>
</div>

<!-- Summary Stats -->
<div class="overview-stats mb-4">
    <div class="overview-stat-card animate-in">
        <span class="overview-stat-label">Total Shown</span>
        <strong class="overview-stat-value"><?= count($users) ?></strong>
    </div>
    <div class="overview-stat-card accent-green animate-in">
        <span class="overview-stat-label">Page</span>
        <strong class="overview-stat-value"><?= $pageNo ?> / <?= $totalPages ?></strong>
    </div>
</div>

<?php if (empty($users)): ?>
<div class="empty-state">
    <div class="empty-state-icon">👥</div>
    <div class="empty-state-title">No participants found</div>
    <p class="text-muted">Try adjusting your search criteria.</p>
</div>
<?php else: ?>
<div class="table-responsive animate-in">
    <table class="table table-hover align-middle mb-0" id="participantsTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Registered</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $idx => $user): ?>
            <tr>
                <td class="text-muted"><?= ($pageNo - 1) * 20 + $idx + 1 ?></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,var(--ims-primary),var(--ims-accent));display:grid;place-items:center;color:#fff;font-weight:700;font-size:.75rem;flex-shrink:0"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
                        <strong><?= Security::e($user['name']) ?></strong>
                    </div>
                </td>
                <td class="text-muted small"><?= Security::e($user['email']) ?></td>
                <td class="text-muted small"><?= Security::e($user['phone'] ?? '—') ?></td>
                <td>
                    <span class="badge <?= $user['status'] === 'active' ? 'bg-success' : ($user['status'] === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                        <?= Security::e(ucfirst($user['status'])) ?>
                    </span>
                </td>
                <td class="text-muted small"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                <td class="text-end">
                    <a href="index.php?page=admin-users&edit=<?= (int) $user['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="mt-4 d-flex justify-content-center">
    <ul class="pagination pagination-sm">
        <?php if ($pageNo > 1): ?>
        <li class="page-item"><a class="page-link" href="index.php?page=admin-participants&q=<?= urlencode($q) ?>&p=<?= $pageNo - 1 ?>">← Prev</a></li>
        <?php endif; ?>
        <?php for ($i = max(1, $pageNo - 2); $i <= min($totalPages, $pageNo + 2); $i++): ?>
        <li class="page-item <?= $i === $pageNo ? 'active' : '' ?>"><a class="page-link" href="index.php?page=admin-participants&q=<?= urlencode($q) ?>&p=<?= $i ?>"><?= $i ?></a></li>
        <?php endfor; ?>
        <?php if ($pageNo < $totalPages): ?>
        <li class="page-item"><a class="page-link" href="index.php?page=admin-participants&q=<?= urlencode($q) ?>&p=<?= $pageNo + 1 ?>">Next →</a></li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
<?php endif; ?>

<script>
function exportParticipants() {
    const table = document.getElementById('participantsTable');
    if (!table) return;
    let csv = [];
    table.querySelectorAll('thead tr, tbody tr').forEach(row => {
        const cells = Array.from(row.querySelectorAll('th, td'))
            .filter((_, i) => i < row.children.length - 1) // skip actions column
            .map(c => '"' + c.textContent.trim().replace(/"/g, '""') + '"');
        csv.push(cells.join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'participants_export.csv';
    a.click();
}
</script>
