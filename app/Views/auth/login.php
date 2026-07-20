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
                    <h2 class="auth-sidebar-title">Empowering Sarawak's Digital Future</h2>
                    <p class="auth-sidebar-tagline">Access the unified Industrial Training Operations Platform for certificates, learning, and progress management.</p>
                    <div class="auth-sidebar-features">
                        <div class="auth-sidebar-feature">🛡️ Secure Authentication</div>
                        <div class="auth-sidebar-feature">📚 Specialist Academies</div>
                        <div class="auth-sidebar-feature">🏆 Industry-Ready Certification</div>
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
                <h1 class="auth-form-title">Welcome Back</h1>
                <p class="auth-form-subtitle">Don't have an account? <a href="index.php?page=register">Register here</a></p>
            </div>

            <?php if (!empty($error)): ?><div class="alert alert-danger p-2 small"><?= Security::e($error) ?></div><?php endif; ?>
            <?php if (!empty($success)): ?><div class="alert alert-success p-2 small"><?= Security::e($success) ?></div><?php endif; ?>

            <form method="post" action="index.php?page=login">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                
                <div class="form-floating-custom">
                    <input class="form-control" type="email" name="email" id="email" placeholder=" " required autocomplete="email">
                    <label for="email">Email address</label>
                </div>

                <div class="form-floating-custom">
                    <input class="form-control" type="password" name="password" id="password" placeholder=" " required autocomplete="current-password">
                    <label for="password">Password</label>
                </div>

                <button class="btn btn-primary btn-lg w-100 mt-2" style="border-radius: 12px; font-weight: 600; font-size: 0.95rem;">Sign In</button>
            </form>

            <div class="demo-badge-info mt-4">
                <p class="small text-muted mb-0">💡 <strong>Demo Accounts</strong><br>
                All accounts use: <code>password</code><br>
                Admin: <code>admin@centexs.local</code><br>
                Trainee: <code>trainee@centexs.local</code></p>
            </div>
        </div>
    </div>
</section>


