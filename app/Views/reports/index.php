<?php use App\Core\Security; use App\Core\View; ?>
<section class="container py-4">
    <?php View::partial('partials/role-nav'); ?>
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
        <div><h1 class="h3 mb-1">Training Reports</h1><p class="text-muted mb-0">Analytics for trainees, completion, attendance, assignments, assessments, certificates, and evaluations.</p></div>
        <div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-primary" href="index.php?page=export-report&format=csv">CSV</a><a class="btn btn-outline-primary" href="index.php?page=export-report&format=excel">Excel</a><a class="btn btn-primary" href="index.php?page=export-report&format=pdf">PDF</a></div>
    </div>
    <form class="panel row g-2 mb-4" method="get">
        <input type="hidden" name="page" value="reports">
        <div class="col-md-4"><select class="form-select" name="academy_id"><option value="">All academies</option><?php foreach ($academies as $academy): ?><option value="<?= (int) $academy['id'] ?>" <?= $academyId === (int) $academy['id'] ? 'selected' : '' ?>><?= Security::e($academy['code']) ?> - <?= Security::e($academy['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><select class="form-select" name="course_id"><option value="">All courses</option><?php foreach ($courses as $course): ?><option value="<?= (int) $course['id'] ?>" <?= $courseId === (int) $course['id'] ? 'selected' : '' ?>><?= Security::e($course['title']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><input class="form-control" type="date" name="date_from"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    </form>
    <div class="row g-3 mb-4">
        <?php foreach ($summary as $label => $value): ?>
            <div class="col-6 col-lg-3"><div class="stat-card"><strong><?= (int) $value ?></strong><span><?= Security::e(ucwords(str_replace('_', ' ', $label))) ?></span></div></div>
        <?php endforeach; ?>
    </div>
    <div class="row g-4">
        <div class="col-lg-6"><div class="panel"><h2 class="h5">Course Completion Rate</h2><canvas class="miniChart" data-values='<?= json_encode(array_column($completion, 'rate')) ?>' data-labels='<?= json_encode(array_column($completion, 'title')) ?>'></canvas></div></div>
        <div class="col-lg-6"><div class="panel"><h2 class="h5">Certificate Issuance</h2><canvas class="miniChart" data-values='<?= json_encode(array_column($certificates, 'approved')) ?>' data-labels='<?= json_encode(array_column($certificates, 'title')) ?>'></canvas></div></div>
        <div class="col-lg-6"><div class="panel table-responsive"><h2 class="h5">Attendance Statistics</h2><table class="table"><thead><tr><th>Course</th><th>Attendance</th><th>Records</th></tr></thead><tbody><?php foreach ($attendance as $row): ?><tr><td><?= Security::e($row['title']) ?></td><td><?= (int) $row['attendance_rate'] ?>%</td><td><?= (int) $row['records'] ?></td></tr><?php endforeach; ?></tbody></table></div></div>
        <div class="col-lg-6"><div class="panel table-responsive"><h2 class="h5">Assignment Statistics</h2><table class="table"><thead><tr><th>Course</th><th>Assignments</th><th>Submissions</th><th>Graded</th></tr></thead><tbody><?php foreach ($assignments as $row): ?><tr><td><?= Security::e($row['title']) ?></td><td><?= (int) $row['assignments'] ?></td><td><?= (int) $row['submissions'] ?></td><td><?= (int) $row['graded'] ?></td></tr><?php endforeach; ?></tbody></table></div></div>
        <div class="col-lg-7"><div class="panel table-responsive"><h2 class="h5">Completion Report</h2><table class="table"><thead><tr><th>Course</th><th>Total</th><th>Completed</th><th>Rate</th></tr></thead><tbody><?php foreach ($completion as $row): ?><tr><td><?= Security::e($row['title']) ?></td><td><?= (int) $row['total'] ?></td><td><?= (int) $row['completed'] ?></td><td><?= (int) $row['rate'] ?>%</td></tr><?php endforeach; ?></tbody></table></div></div>
        <div class="col-lg-5"><div class="panel table-responsive"><h2 class="h5">Evaluation Summary</h2><table class="table"><thead><tr><th>Course</th><th>Rating</th><th>Responses</th></tr></thead><tbody><?php foreach ($evaluations as $row): ?><tr><td><?= Security::e($row['title']) ?></td><td><?= Security::e((string) $row['avg_rating']) ?></td><td><?= (int) $row['responses'] ?></td></tr><?php endforeach; ?></tbody></table></div></div>
    </div>
</section>
