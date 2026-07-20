<?php use App\Core\Security; use App\Core\View; ?>
<section class="container py-4">
    <?php View::partial('partials/role-nav'); ?>

    <span class="section-label">Training Evaluation</span>
    <h1 class="section-title">Post-course feedback</h1>

    <div class="lms-layout">
        <!-- ── Feedback Form ─────────────────────────── -->
        <div>
            <?php if (!$courses): ?>
                <div class="lms-card">
                    <p class="text-muted mb-0">No completed courses are waiting for evaluation. You're all caught up!</p>
                </div>
            <?php endif; ?>

            <?php foreach ($courses as $courseIndex => $course): ?>
                <div class="lms-card <?= $courseIndex > 0 ? 'mt-3' : '' ?>">
                    <h2 class="lms-card-title">Feedback form — <?= Security::e($course['title']) ?></h2>

                    <form method="post" action="index.php?page=save-evaluation">
                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                        <input type="hidden" name="course_id" value="<?= (int) $course['course_id'] ?>">

                        <!-- Rating Dropdown -->
                        <div class="range-group">
                            <label class="range-label">Rating</label>
                            <select class="form-select star-select" name="course_rating">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?= $i ?>"><?= $i ?> star<?= $i > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Trainer Evaluation Slider -->
                        <div class="range-group">
                            <label class="range-label">Trainer evaluation</label>
                            <input type="range" class="range-slider" name="instructor_rating" min="1" max="5" step="0.1" value="4.5" data-range-display>
                            <input type="hidden" name="instructor_rating_value" value="4.5">
                        </div>

                        <!-- Content Quality Slider -->
                        <div class="range-group">
                            <label class="range-label">Content quality</label>
                            <input type="range" class="range-slider" data-extra="content_quality" min="1" max="5" step="0.1" value="3.5" data-range-display>
                        </div>

                        <!-- Facilities Slider -->
                        <div class="range-group">
                            <label class="range-label">Facilities</label>
                            <input type="range" class="range-slider" data-extra="facilities" min="1" max="5" step="0.1" value="4.0" data-range-display>
                        </div>

                        <!-- Learning Experience -->
                        <div class="range-group">
                            <label class="range-label">Learning experience</label>
                            <textarea class="feedback-textarea" name="feedback" placeholder="Share suggestions"></textarea>
                        </div>

                        <!-- Additional Comments -->
                        <textarea class="feedback-textarea mb-3" name="comments" rows="2" placeholder="Additional comments about materials, pacing, facilities, or support"></textarea>

                        <button class="lms-btn lms-btn-primary" type="submit">Submit Feedback</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Evaluation Summary Sidebar ────────────── -->
        <div>
            <div class="lms-sidebar">
                <span class="lms-sidebar-label">Administrator Analytics</span>
                <h2 class="lms-sidebar-title">Evaluation summary</h2>

                <div class="progress-metric">
                    <span class="progress-metric-label">Trainer</span>
                    <div class="progress-metric-bar"><div class="progress-metric-fill" style="width: 92%"></div></div>
                    <span class="progress-metric-value">4.6</span>
                </div>
                <div class="progress-metric">
                    <span class="progress-metric-label">Content</span>
                    <div class="progress-metric-bar"><div class="progress-metric-fill" style="width: 88%"></div></div>
                    <span class="progress-metric-value">4.4</span>
                </div>
                <div class="progress-metric">
                    <span class="progress-metric-label">Facilities</span>
                    <div class="progress-metric-bar"><div class="progress-metric-fill" style="width: 80%"></div></div>
                    <span class="progress-metric-value">4.0</span>
                </div>
            </div>

            <div class="lms-sidebar mt-3">
                <span class="lms-sidebar-label">How It Works</span>
                <h2 class="lms-sidebar-title">Feedback guidelines</h2>
                <div class="completion-item">
                    <div class="completion-item-title">Be honest</div>
                    <div class="completion-item-meta">Your feedback is anonymous and helps improve future training sessions.</div>
                </div>
                <div class="completion-item">
                    <div class="completion-item-title">Rate each category</div>
                    <div class="completion-item-meta">Drag the sliders to rate trainer, content quality, and facilities from 1 to 5.</div>
                </div>
                <div class="completion-item">
                    <div class="completion-item-title">Share suggestions</div>
                    <div class="completion-item-meta">Use the text area to provide detailed feedback on your learning experience.</div>
                </div>
            </div>
        </div>
    </div>
</section>
