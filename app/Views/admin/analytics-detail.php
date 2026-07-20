<?php use App\Core\Security; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="d-flex justify-content-between align-items-md-center flex-column flex-md-row mb-4 gap-2">
    <div>
        <?php
        $backQuery = http_build_query(array_merge(['page' => 'admin-analytics'], array_filter($filters ?? [])));
        ?>
        <a href="index.php?<?= $backQuery ?>" class="text-decoration-none small">← Back to Analytics</a>
        <h1 class="section-title mb-0 mt-1"><?= Security::e($chartTitle) ?></h1>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary btn-sm" onclick="exportTable()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export CSV
        </button>
    </div>
</div>

<?php if (empty($chartData)): ?>
<div class="empty-state">
    <div class="empty-state-icon">📊</div>
    <div class="empty-state-title">No data available</div>
    <p class="text-muted">There is no data matching this filter or chart key.</p>
</div>
<?php else: ?>

<div class="row g-4 mb-4">
    <!-- Chart -->
    <div class="col-lg-7">
        <div class="chart-panel animate-in h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="chart-panel-title mb-0"><?= Security::e($chartTitle) ?></span>
                <select class="form-select form-select-sm" style="width:auto" id="detailChartType">
                    <option value="bar">Bar</option><option value="line">Line</option><option value="pie">Pie</option><option value="doughnut">Doughnut</option>
                </select>
            </div>
            <div class="flex-grow-1" style="position:relative; min-height: 350px;">
                <canvas id="detailChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="col-lg-5">
        <div class="overview-panel animate-in mb-3">
            <h3 class="overview-panel-title">Summary Statistics</h3>
            <div class="overview-detail-list">
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Total Records</span>
                    <span class="overview-detail-value fw-bold"><?= number_format(array_sum(array_map(fn($r) => (int) $r['value'], $chartData))) ?></span>
                </div>
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Categories</span>
                    <span class="overview-detail-value fw-bold"><?= count($chartData) ?></span>
                </div>
                <?php
                $maxRow = null; $maxVal = 0;
                $minRow = null; $minVal = PHP_INT_MAX;
                foreach ($chartData as $row) {
                    $v = (int) $row['value'];
                    if ($v > $maxVal) { $maxVal = $v; $maxRow = $row; }
                    if ($v < $minVal) { $minVal = $v; $minRow = $row; }
                }
                $avgVal = count($chartData) > 0 ? array_sum(array_map(fn($r) => (int) $r['value'], $chartData)) / count($chartData) : 0;
                ?>
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Highest</span>
                    <span class="overview-detail-value"><strong><?= number_format($maxVal) ?></strong> <small class="text-muted">(<?= Security::e($maxRow['label'] ?? '—') ?>)</small></span>
                </div>
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Lowest</span>
                    <span class="overview-detail-value"><strong><?= number_format($minVal) ?></strong> <small class="text-muted">(<?= Security::e($minRow['label'] ?? '—') ?>)</small></span>
                </div>
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Average</span>
                    <span class="overview-detail-value fw-bold"><?= number_format($avgVal, 1) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Full Data Table -->
<div class="chart-panel animate-in">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="chart-panel-title mb-0">Detailed Data</span>
        <div style="max-width: 250px;">
            <input type="text" class="form-control form-control-sm" placeholder="Search..." id="detailSearch">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="detailTable">
            <thead>
                <tr>
                    <th style="cursor:pointer" onclick="sortTable(0)"># <small>▼</small></th>
                    <th style="cursor:pointer" onclick="sortTable(1)">Label <small>▼</small></th>
                    <th class="text-end" style="cursor:pointer" onclick="sortTable(2)">Value <small>▼</small></th>
                    <th class="text-end">% of Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = max(1, array_sum(array_map(fn($r) => (int) $r['value'], $chartData)));
                foreach ($chartData as $idx => $row):
                    $v = (int) $row['value'];
                    $pct = ($v / $total) * 100;
                ?>
                <tr>
                    <td><?= $idx + 1 ?></td>
                    <td class="fw-semibold"><?= Security::e($row['label']) ?></td>
                    <td class="text-end fw-bold"><?= number_format($v) ?></td>
                    <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <div class="progress" style="width: 80px; height: 6px;">
                                <div class="progress-bar bg-primary" style="width: <?= round($pct) ?>%"></div>
                            </div>
                            <small class="text-muted"><?= number_format($pct, 1) ?>%</small>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-light">
                    <td></td>
                    <td class="fw-bold">Total</td>
                    <td class="text-end fw-bold"><?= number_format($total) ?></td>
                    <td class="text-end"><small class="text-muted">100%</small></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const labels = <?= json_encode(array_column($chartData, 'label')) ?>;
    const values = <?= json_encode(array_map(fn($r) => (int) $r['value'], $chartData)) ?>;
    const palette = ['#054d9e','#18a999','#ea580c','#16a34a','#8b5cf6','#0891b2','#dc2626','#334155','#0d9488','#c2410c','#6366f1','#059669'];
    let chart;

    function render(type) {
        if (chart) chart.destroy();
        chart = new Chart(document.getElementById('detailChart').getContext('2d'), {
            type, data: { labels, datasets: [{ label: 'Records', data: values,
                backgroundColor: type==='line' ? 'rgba(5,77,158,.08)' : palette.slice(0, values.length),
                borderColor: type==='line' ? '#054d9e' : palette.slice(0, values.length),
                borderWidth: type==='line' ? 2.5 : 1, fill: type==='line', tension: .4,
                pointRadius: type==='line' ? 4 : 0, borderRadius: type==='bar' ? 6 : 0,
            }]},
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: type==='pie'||type==='doughnut', position: 'bottom' }, tooltip: { padding: 12, cornerRadius: 8, backgroundColor: 'rgba(24,34,48,.92)' }},
                scales: { y: { beginAtZero: true, display: !(type==='pie'||type==='doughnut'), grid: { color: 'rgba(0,0,0,.04)' }}, x: { display: !(type==='pie'||type==='doughnut'), grid: { display: false }, ticks: { maxRotation: 45 }}}
            }
        });
    }
    render('bar');
    document.getElementById('detailChartType').addEventListener('change', e => render(e.target.value));

    // Search
    document.getElementById('detailSearch').addEventListener('input', e => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('#detailTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
});

let sortDir = {};
function sortTable(col) {
    const tbody = document.querySelector('#detailTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    sortDir[col] = !sortDir[col];
    rows.sort((a, b) => {
        let va = a.children[col].textContent.trim().replace(/[^0-9.\-]/g, '');
        let vb = b.children[col].textContent.trim().replace(/[^0-9.\-]/g, '');
        if (col === 1) { va = a.children[col].textContent.trim(); vb = b.children[col].textContent.trim(); return sortDir[col] ? va.localeCompare(vb) : vb.localeCompare(va); }
        return sortDir[col] ? parseFloat(va||0) - parseFloat(vb||0) : parseFloat(vb||0) - parseFloat(va||0);
    });
    rows.forEach(r => tbody.appendChild(r));
}

function exportTable() {
    const table = document.getElementById('detailTable');
    let csv = [];
    table.querySelectorAll('thead tr, tbody tr').forEach(row => {
        const cells = Array.from(row.querySelectorAll('th, td')).map(c => '"' + c.textContent.trim().replace(/"/g, '""') + '"');
        csv.push(cells.join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = '<?= Security::e($chartKey) ?>_data.csv';
    a.click();
}
</script>
<?php endif; ?>
