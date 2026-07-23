<?php use App\Core\Security; use App\Core\View; ?>
<section class="container py-4">
    <?php View::partial('partials/role-nav'); ?>
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Training Reports</h1>
            <p class="text-muted mb-0">Analytics for trainees, completion, attendance, assignments, assessments, certificates, and evaluations.</p>
        </div>
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export Report
            </button>
        </div>
    </div>

    <!-- Filters -->
    <form class="panel row g-3 mb-4 align-items-end" method="get">
        <input type="hidden" name="page" value="reports">
        <div class="col-md-4">
            <label class="form-label text-muted small mb-1">Academy</label>
            <select class="form-select" name="academy_id">
                <option value="">All academies</option>
                <?php foreach ($academies as $academy): ?>
                    <option value="<?= (int) $academy['id'] ?>" <?= $academyId === (int) $academy['id'] ? 'selected' : '' ?>>
                        <?= Security::e($academy['code']) ?> - <?= Security::e($academy['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label text-muted small mb-1">Course</label>
            <select class="form-select" name="course_id">
                <option value="">All courses</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= (int) $course['id'] ?>" <?= $courseId === (int) $course['id'] ? 'selected' : '' ?>>
                        <?= Security::e($course['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small mb-1">Date From</label>
            <input class="form-control" type="date" name="date_from">
        </div>
        <div class="col-md-2">
            <button class="btn btn-secondary w-100">Filter</button>
        </div>
    </form>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <?php foreach ($summary as $label => $value): ?>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <strong><?= (int) $value ?></strong>
                    <span><?= Security::e(ucwords(str_replace('_', ' ', $label))) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4 border-bottom-0 gap-2" id="reportTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-top border" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">Overview</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-top border" id="course-metrics-tab" data-bs-toggle="tab" data-bs-target="#course-metrics" type="button" role="tab" aria-controls="course-metrics" aria-selected="false">Course Metrics</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-top border" id="master-data-tab" data-bs-toggle="tab" data-bs-target="#master-data" type="button" role="tab" aria-controls="master-data" aria-selected="false">Master Data Stats</button>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="reportTabsContent">
        <!-- Overview Tab -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="panel h-100 border-top border-primary border-3">
                        <h2 class="h5 mb-4">Course Completion Rate</h2>
                        <canvas class="miniChart" data-values='<?= json_encode(array_column($completion, 'rate')) ?>' data-labels='<?= json_encode(array_column($completion, 'title')) ?>'></canvas>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="panel h-100 border-top border-success border-3">
                        <h2 class="h5 mb-4">Certificate Issuance</h2>
                        <canvas class="miniChart" data-values='<?= json_encode(array_column($certificates, 'approved')) ?>' data-labels='<?= json_encode(array_column($certificates, 'title')) ?>'></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Metrics Tab -->
        <div class="tab-pane fade" id="course-metrics" role="tabpanel" aria-labelledby="course-metrics-tab">
            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="panel table-responsive border-top border-primary border-3">
                        <h2 class="h5 mb-3">Completion Report</h2>
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Course</th><th>Total</th><th>Completed</th><th>Rate</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($completion as $row): ?>
                                    <tr>
                                        <td><strong class="text-dark"><?= Security::e($row['title']) ?></strong></td>
                                        <td><?= (int) $row['total'] ?></td>
                                        <td><span class="text-success fw-medium"><?= (int) $row['completed'] ?></span></td>
                                        <td style="min-width: 150px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar bg-primary" style="width: <?= (int) $row['rate'] ?>%"></div>
                                                </div>
                                                <span class="small fw-bold"><?= (int) $row['rate'] ?>%</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="panel table-responsive h-100 border-top border-info border-3">
                        <h2 class="h5 mb-3">Attendance & Assignments</h2>
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Course</th><th>Att. Rate</th><th>Submissions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance as $idx => $row): ?>
                                    <?php $assign = $assignments[$idx] ?? ['submissions' => 0]; ?>
                                    <tr>
                                        <td><span class="text-truncate d-inline-block fw-medium text-dark" style="max-width: 200px;"><?= Security::e($row['title']) ?></span></td>
                                        <td><?= (int) $row['attendance_rate'] ?>%</td>
                                        <td><span class="badge bg-secondary"><?= (int) $assign['submissions'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="panel table-responsive h-100 border-top border-warning border-3">
                        <h2 class="h5 mb-3">Evaluation Summary</h2>
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Course</th><th>Avg Rating</th><th>Responses</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluations as $row): ?>
                                    <tr>
                                        <td><span class="text-truncate d-inline-block fw-medium text-dark" style="max-width: 200px;"><?= Security::e($row['title']) ?></span></td>
                                        <td>
                                            <span class="badge bg-warning text-dark px-2 py-1"><i class="me-1">★</i><?= Security::e((string) $row['avg_rating']) ?></span>
                                        </td>
                                        <td><?= (int) $row['responses'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Master Data Stats Tab -->
        <div class="tab-pane fade" id="master-data" role="tabpanel" aria-labelledby="master-data-tab">
            <div class="panel table-responsive border-top border-secondary border-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Master Data Training Statistics</h2>
                    <a href="index.php?page=admin-master-data&table=training_statistics" class="btn btn-sm btn-outline-secondary">Manage Stats</a>
                </div>
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Academy</th>
                            <th>Course Name</th>
                            <th>Participants</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($master_data_stats)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">No master data statistics found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($master_data_stats as $row): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border"><?= Security::e($row['academy_code']) ?></span></td>
                                    <td><strong class="text-dark"><?= Security::e($row['course_name']) ?></strong></td>
                                    <td><span class="badge bg-primary rounded-pill"><?= (int) $row['participants'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="index.php" method="get">
                <input type="hidden" name="page" value="export-report">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title h5 fw-bold" id="exportModalLabel">Export Report Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted small mb-4">Select the type of data and the format you want to export.</p>
                    
                    <div class="mb-4">
                        <label class="form-label fw-medium text-dark small">1. Select Data Type</label>
                        <select class="form-select shadow-none" name="report_type" required>
                            <option value="completion">Course Completions</option>
                            <option value="attendance">Attendance Statistics</option>
                            <option value="certificates">Certificate Issuance</option>
                            <option value="master_data">Master Data Statistics</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label fw-medium text-dark small">2. Select Export Format</label>
                        <div class="d-flex gap-2">
                            <div class="form-check flex-fill p-0 m-0">
                                <input type="radio" class="btn-check" name="format" id="formatCsv" value="csv" checked>
                                <label class="btn btn-outline-primary w-100 py-2" for="formatCsv">
                                    <div class="fw-bold mb-0">CSV</div>
                                    <small class="opacity-75">Spreadsheet</small>
                                </label>
                            </div>
                            <div class="form-check flex-fill p-0 m-0">
                                <input type="radio" class="btn-check" name="format" id="formatExcel" value="excel">
                                <label class="btn btn-outline-primary w-100 py-2" for="formatExcel">
                                    <div class="fw-bold mb-0">Excel</div>
                                    <small class="opacity-75">.xls Format</small>
                                </label>
                            </div>
                            <div class="form-check flex-fill p-0 m-0">
                                <input type="radio" class="btn-check" name="format" id="formatPdf" value="pdf">
                                <label class="btn btn-outline-primary w-100 py-2" for="formatPdf">
                                    <div class="fw-bold mb-0">PDF</div>
                                    <small class="opacity-75">Document</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 rounded-bottom">
                    <button type="button" class="btn btn-link text-decoration-none text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Download</button>
                </div>
            </form>
        </div>
    </div>
</div>
