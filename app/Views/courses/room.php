<?php use App\Core\Auth; use App\Core\Security; ?>
<section class="container py-4">
    <a href="index.php?page=dashboard">&larr; Dashboard</a>
    <h1 class="h3 mt-2"><?= Security::e($course['title'] ?? 'Course Room') ?></h1>
    <div class="row g-4 mt-1">
        <div class="col-lg-7">
            <div class="panel mb-4"><h2 class="h5">Learning Materials</h2><?php foreach ($materials as $m): ?><div class="d-flex justify-content-between border-bottom py-2"><span><?= Security::e($m['title']) ?> <small class="text-muted"><?= Security::e($m['type']) ?></small></span><?php if ($m['external_url']): ?><a href="<?= Security::e($m['external_url']) ?>" target="_blank">Open</a><?php else: ?><span class="text-muted small"><?= Security::e($m['file_path']) ?></span><?php endif; ?></div><?php endforeach; ?></div>
            <div class="panel"><h2 class="h5">Assignments</h2><?php foreach ($assignments as $a): ?><div class="border-bottom py-3"><strong><?= Security::e($a['title']) ?></strong><p class="mb-2"><?= Security::e($a['instructions']) ?></p><?php if (Auth::role() === 'trainee'): ?><form class="row g-2" method="post" enctype="multipart/form-data" action="index.php?page=submit-assignment"><input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>"><input type="hidden" name="assignment_id" value="<?= (int) $a['id'] ?>"><div class="col-md-6"><input class="form-control" type="file" name="submission" required></div><div class="col-md-4"><input class="form-control" name="notes" placeholder="Notes"></div><div class="col-md-2"><button class="btn btn-primary w-100">Submit</button></div></form><?php endif; ?></div><?php endforeach; ?></div>
        </div>
        <div class="col-lg-5">
            <?php if (in_array(Auth::role(), ['admin', 'instructor'], true)): ?>
                <div class="panel mb-4"><h2 class="h5">Upload Material</h2><form method="post" enctype="multipart/form-data" action="index.php?page=add-material"><input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>"><input type="hidden" name="course_id" value="<?= (int) ($course['id'] ?? 0) ?>"><input class="form-control mb-2" name="title" placeholder="Title" required><select class="form-select mb-2" name="type"><option>document</option><option>video</option><option>link</option></select><input class="form-control mb-2" type="file" name="material"><input class="form-control mb-3" name="external_url" placeholder="External URL"><button class="btn btn-primary w-100">Add Material</button></form></div>
                <div class="panel"><h2 class="h5">Create Assignment</h2><form method="post" action="index.php?page=add-assignment"><input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>"><input type="hidden" name="course_id" value="<?= (int) ($course['id'] ?? 0) ?>"><input class="form-control mb-2" name="title" placeholder="Title" required><textarea class="form-control mb-2" name="instructions" rows="4" placeholder="Instructions"></textarea><input class="form-control mb-2" type="datetime-local" name="due_date"><input class="form-control mb-3" type="number" step="0.01" name="max_score" value="100"><button class="btn btn-primary w-100">Create</button></form></div>
            <?php endif; ?>
            <div class="panel mt-4"><h2 class="h5">Quizzes</h2><?php foreach ($quizzes as $q): ?><p class="border-bottom pb-2"><?= Security::e($q['title']) ?> <span class="badge text-bg-secondary"><?= (int) $q['total_marks'] ?> marks</span></p><?php endforeach; ?></div>
        </div>
    </div>
</section>

