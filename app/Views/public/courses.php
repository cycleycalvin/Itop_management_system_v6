<?php use App\Core\Security; use App\Core\View; ?>
<section class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h2"><?= $selectedAcademy ? Security::e($selectedAcademy['code']) . ' Courses' : 'Courses by Academy' ?></h1>
            <p class="text-muted mb-0"><?= $selectedAcademy ? Security::e($selectedAcademy['name']) : 'Choose an academy to view available ITOP courses.' ?></p>
        </div>
        <?php if ($selectedAcademy): ?>
            <form class="d-flex" method="get">
                <input type="hidden" name="page" value="courses">
                <input type="hidden" name="academy" value="<?= Security::e($selectedAcademy['code']) ?>">
                <input class="form-control" name="q" value="<?= Security::e($q) ?>" placeholder="Search <?= Security::e($selectedAcademy['code']) ?> courses">
                <button class="btn btn-primary ms-2">Search</button>
            </form>
        <?php endif; ?>
    </div>
    <?php if (!$selectedAcademy): ?>
        <div class="row g-4">
            <?php foreach ($academies as $academy): ?>
                <div class="col-md-6">
                    <a class="academy-card" href="index.php?page=courses&academy=<?= Security::e($academy['code']) ?>">
                        <span class="academy-chip"><?= Security::e($academy['code']) ?></span>
                        <h2><?= Security::e($academy['name']) ?></h2>
                        <p><?= Security::e($academy['description'] ?? 'Browse academy courses and request enrolment.') ?></p>
                        <div class="academy-meta">
                            <strong><?= (int) $academy['course_count'] ?></strong><span>Courses</span>
                            <strong><?= (int) $academy['participant_count'] ?></strong><span>Participants</span>
                        </div>
                        <span class="btn btn-primary mt-3">View Courses</span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="mb-3"><a class="btn btn-sm btn-outline-primary" href="index.php?page=courses">Back to Academies</a></div>
        <div class="row g-3">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-6 col-xl-4"><?php View::partial('partials/course-card', ['course' => $course, 'actions' => 'public']); ?></div>
            <?php endforeach; ?>
            <?php if (!$courses): ?><div class="col-12"><div class="panel text-muted">No courses found for this academy.</div></div><?php endif; ?>
        </div>
    <?php endif; ?>
</section>
