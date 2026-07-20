<?php use App\Core\Auth; use App\Core\Security; ?>
<section class="container py-5">
    <a href="index.php?page=courses" class="text-decoration-none">&larr; Courses</a>
    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <span class="badge text-bg-info"><?= Security::e($course['category']) ?></span>
            <h1 class="h2 mt-2"><?= Security::e($course['title']) ?></h1>
            <p class="lead"><?= Security::e($course['description']) ?></p>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="mb-2"><strong>Instructor:</strong> <?= Security::e($course['instructor_name'] ?? 'To be assigned') ?></div>
                    <div class="mb-2"><strong>Schedule:</strong> <?= Security::e($course['start_date']) ?> to <?= Security::e($course['end_date']) ?></div>
                    <div class="mb-3"><strong>Fee:</strong> RM <?= number_format((float) $course['fee'], 2) ?></div>
                    <?php if (Auth::role() === 'trainee'): ?>
                        <form method="post" action="index.php?page=enroll">
                            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                            <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
                            <button class="btn btn-primary w-100">Request Enrolment</button>
                        </form>
                    <?php else: ?>
                        <a class="btn btn-primary w-100" href="index.php?page=login">Login to Enrol</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

