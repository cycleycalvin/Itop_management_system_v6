<?php use App\Core\Security; ?>
<section class="auth-wrap animate-in">
    <div class="auth-container">
        <!-- Sidebar visible on large screens -->
        <div class="auth-sidebar">
            <div class="auth-sidebar-overlay"></div>
            <div class="auth-sidebar-content">
                <div class="auth-sidebar-logo">
                    <img src="public/assets/img/centexs-logo-with-outline-1.png" alt="CENTEXS Logo">
                    <span>CENTEXS ITOP</span>
                </div>
                <div class="auth-sidebar-body">
                    <h2 class="auth-sidebar-title">Join Our Training Programmes</h2>
                    <p class="auth-sidebar-tagline">Register a trainee account to enroll in courses, access learning materials, and earn verified certifications.</p>
                    <div class="auth-sidebar-features">
                        <div class="auth-sidebar-feature">🎓 Advanced Skills Training</div>
                        <div class="auth-sidebar-feature">📑 Material & Assessment Access</div>
                        <div class="auth-sidebar-feature">🤝 Industry Placement Links</div>
                    </div>
                </div>
                <div class="auth-sidebar-footer">
                    &copy; <?= date('Y') ?> Centre for Technology Excellence Sarawak
                </div>
            </div>
        </div>

        <!-- Form side -->
        <div class="auth-form-side">
            <div class="auth-form-logo-mobile text-center">
                <img src="public/assets/img/centexs-logo-with-outline-1.png" alt="CENTEXS Logo" height="60">
            </div>
            <div class="auth-form-header">
                <h1 class="auth-form-title">Create Account</h1>
                <p class="auth-form-subtitle">Already have an account? <a href="index.php?page=login">Sign in here</a></p>
            </div>

            <?php if (!empty($error)): ?><div class="alert alert-danger p-2 small"><?= Security::e($error) ?></div><?php endif; ?>

            <form method="post" action="index.php?page=register">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                
                <div class="form-floating-custom">
                    <input class="form-control" name="name" id="name" placeholder=" " required autocomplete="name">
                    <label for="name">Full Name</label>
                </div>

                <div class="form-floating-custom">
                    <input class="form-control" type="email" name="email" id="email" placeholder=" " required autocomplete="email">
                    <label for="email">Email address</label>
                </div>

                <div class="form-floating-custom">
                    <input class="form-control" name="phone" id="phone" placeholder=" " autocomplete="tel">
                    <label for="phone">Phone Number</label>
                </div>

                <div class="form-floating-custom">
                    <input class="form-control" type="password" name="password" id="password" placeholder=" " minlength="8" required autocomplete="new-password">
                    <label for="password">Password (min. 8 chars)</label>
                </div>

                <button class="btn btn-primary btn-lg w-100 mt-2" style="border-radius: 12px; font-weight: 600; font-size: 0.95rem;">Create Account</button>
            </form>
        </div>
    </div>
</section>


