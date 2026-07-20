<?php use App\Core\Security; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="d-flex justify-content-between align-items-md-center flex-column flex-md-row mb-4 gap-2">
    <div>
        <span class="section-label">Analytics</span>
        <h1 class="section-title mb-0">Full Analytics Dashboard</h1>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php?page=dashboard" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
        <a href="index.php?page=reports" class="btn btn-outline-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Export Reports
        </a>
    </div>
</div>

<!-- Global Filter Card -->
<div class="card p-3 mb-4 animate-in">
    <form method="get" action="index.php" class="row g-3 align-items-end">
        <input type="hidden" name="page" value="admin-analytics">
        
        <div class="col-md-3">
            <label for="academy_filter" class="form-label small fw-bold text-muted mb-1">Academy</label>
            <select name="academy_id" id="academy_filter" class="form-select form-select-sm">
                <option value="">All Academies</option>
                <?php foreach ($academies as $ac): ?>
                    <option value="<?= (int) $ac['id'] ?>" <?= $filters['academy_id'] == $ac['id'] ? 'selected' : '' ?>><?= Security::e($ac['code'] . ' - ' . $ac['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label for="course_filter" class="form-label small fw-bold text-muted mb-1">Course</label>
            <select name="course_id" id="course_filter" class="form-select form-select-sm">
                <option value="">All Courses</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= $filters['course_id'] == $c['id'] ? 'selected' : '' ?>><?= Security::e($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label for="instructor_filter" class="form-label small fw-bold text-muted mb-1">Instructor</label>
            <select name="instructor_id" id="instructor_filter" class="form-select form-select-sm">
                <option value="">All Instructors</option>
                <?php foreach ($instructors as $inst): ?>
                    <option value="<?= (int) $inst['id'] ?>" <?= $filters['instructor_id'] == $inst['id'] ? 'selected' : '' ?>><?= Security::e($inst['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2 col-6">
            <label for="start_date" class="form-label small fw-bold text-muted mb-1">Start Date</label>
            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="<?= Security::e($filters['start_date']) ?>">
        </div>

        <div class="col-md-2 col-6">
            <label for="end_date" class="form-label small fw-bold text-muted mb-1">End Date</label>
            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="<?= Security::e($filters['end_date']) ?>">
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
            <a href="index.php?page=admin-analytics" class="btn btn-outline-secondary btn-sm">Reset</a>
            <button type="submit" class="btn btn-primary btn-sm px-4">Apply Filters</button>
        </div>
    </form>
</div>

<!-- Stat Summary -->
<div class="overview-stats mb-4">
    <?php foreach ($stats as $label => $value): ?>
    <div class="overview-stat-card animate-in">
        <span class="overview-stat-label"><?= Security::e(ucwords(str_replace('_', ' ', $label))) ?></span>
        <strong class="overview-stat-value"><?= $label === 'total_revenue' ? 'RM ' . number_format((float)$value) : number_format((int)$value) ?></strong>
    </div>
    <?php endforeach; ?>
</div>

<!-- All Charts Grid -->
<div class="row g-4 mb-4">
    <?php
    $chartConfigs = [
        'programme'          => ['title' => 'Participants by Academy',  'type' => 'bar',      'col' => '6'],
        'monthly'            => ['title' => 'Monthly Registration',     'type' => 'line',     'col' => '6'],
        'years'              => ['title' => 'Participants by Year',     'type' => 'bar',      'col' => '4'],
        'completion'         => ['title' => 'Completion Rate',          'type' => 'doughnut', 'col' => '4'],
        'certificates'       => ['title' => 'Certificate Issuance',    'type' => 'line',     'col' => '4'],
        'course_participants'=> ['title' => 'Participants by Course',   'type' => 'bar',      'col' => '6'],
        'categories'         => ['title' => 'Participants by Category', 'type' => 'bar',      'col' => '6'],
    ];
    foreach ($chartConfigs as $key => $cfg):
        $data = $analytics[$key] ?? [];
        if (empty($data)) continue;
        $labels = array_column($data, 'label');
        $values = array_column($data, 'value');
        if ($key === 'monthly' || $key === 'certificates') {
            $labels = array_reverse($labels);
            $values = array_reverse($values);
        }
    ?>
    <div class="col-lg-<?= $cfg['col'] ?>">
        <div class="chart-panel animate-in h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="chart-panel-title mb-0"><?= $cfg['title'] ?></span>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select form-select-sm chart-type-select" style="width:auto" data-chart-id="analytics-<?= $key ?>">
                        <?php foreach (['bar','line','pie','doughnut'] as $t): ?>
                        <option value="<?= $t ?>" <?= $t === $cfg['type'] ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php
                    $drillQuery = http_build_query(array_merge(['page' => 'admin-analytics-detail', 'chart' => $key], array_filter($filters)));
                    ?>
                    <a href="index.php?<?= $drillQuery ?>" class="btn btn-sm btn-outline-primary px-2" title="Drill Down">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                </div>
            </div>
            <div class="flex-grow-1" style="position:relative; min-height: 280px;">
                <canvas class="analytics-chart" id="analytics-chart-<?= $key ?>"
                        data-default-type="<?= $cfg['type'] ?>"
                        data-labels='<?= json_encode($labels) ?>'
                        data-values='<?= json_encode(array_map('intval', $values)) ?>'></canvas>
            </div>
            <!-- Data Table Expandable -->
            <details class="mt-3" style="font-size:.88rem">
                <summary class="fw-semibold text-primary mb-2" style="cursor:pointer">View Data Table</summary>
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Label</th><th class="text-end">Value</th></tr></thead>
                        <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr><td><?= Security::e($row['label']) ?></td><td class="text-end fw-bold"><?= number_format((int)$row['value']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot><tr class="table-light"><td class="fw-bold">Total</td><td class="text-end fw-bold"><?= number_format(array_sum(array_map('intval', $values))) ?></td></tr></tfoot>
                    </table>
                </div>
            </details>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const palette = ['#054d9e','#18a999','#ea580c','#16a34a','#8b5cf6','#0891b2','#dc2626','#334155','#0d9488','#c2410c','#6366f1','#059669'];
    const chartsMap = {};

    function makeChart(canvasId, type, labels, values) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        return new Chart(canvas.getContext('2d'), {
            type, data: {
                labels, datasets: [{
                    label: 'Records', data: values,
                    backgroundColor: type === 'line' ? 'rgba(5,77,158,.08)' : palette.slice(0, values.length),
                    borderColor: type === 'line' ? '#054d9e' : palette.slice(0, values.length),
                    borderWidth: type === 'line' ? 2.5 : 1, fill: type === 'line', tension: .4,
                    pointBackgroundColor: '#054d9e', pointBorderColor: '#fff', pointBorderWidth: 2,
                    pointRadius: type === 'line' ? 4 : 0, borderRadius: type === 'bar' ? 6 : 0,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: type === 'pie' || type === 'doughnut', position: 'bottom', labels: { padding: 14, usePointStyle: true, pointStyle: 'circle', font: { family: "'Inter', sans-serif", size: 12 }}},
                    tooltip: { padding: 12, cornerRadius: 8, backgroundColor: 'rgba(24,34,48,.92)' }
                },
                scales: {
                    y: { beginAtZero: true, display: !(type==='pie'||type==='doughnut'), grid: { color: 'rgba(0,0,0,.04)', drawBorder: false }},
                    x: { display: !(type==='pie'||type==='doughnut'), grid: { display: false }, ticks: { maxRotation: 45, autoSkip: true, maxTicksLimit: 15 }}
                }
            }
        });
    }

    document.querySelectorAll('.analytics-chart').forEach(canvas => {
        const id = canvas.id;
        const type = canvas.dataset.defaultType;
        const labels = JSON.parse(canvas.dataset.labels);
        const values = JSON.parse(canvas.dataset.values);
        chartsMap[id] = makeChart(id, type, labels, values);
    });

    document.querySelectorAll('.chart-type-select').forEach(sel => {
        sel.addEventListener('change', e => {
            const chartId = 'analytics-chart-' + e.target.dataset.chartId.replace('analytics-','');
            if (chartsMap[chartId]) {
                const {labels} = chartsMap[chartId].data;
                const data = chartsMap[chartId].data.datasets[0].data;
                chartsMap[chartId].destroy();
                chartsMap[chartId] = makeChart(chartId, e.target.value, labels, data);
            }
        });
    });
});
</script>
