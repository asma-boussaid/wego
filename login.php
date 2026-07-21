<?php
/**
 * WeGo — login.php
 * ─────────────────────────────────────────────────────────────
 * POST/REDIRECT/GET (PRG) Pattern:
 *   1. User submits form (POST)
 *   2. We process the form at the top of the page
 *   3. On SUCCESS → redirect() to another page
 *   4. On FAILURE → re-render the form with errors
 *
 * WHY PRG?
 *   Without it, refreshing the page after login would resubmit
 *   the form — browser would warn "Resend form data?".
 *   With PRG, the browser's last request is a GET → safe to refresh.
 */

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect(APP_URL . '/dashboard.php');
}

$errors = [];
$email  = ''; // preserve email input on error (UX improvement)

// ── Handle POST submission ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Verify CSRF token (security first!)
    if (!verifyCsrf()) {
        $errors[] = 'Security check failed. Please try again.';
    } else {
        // 2. Sanitize inputs
        $email    = clean($_POST['email']    ?? '');
        $password = $_POST['password'] ?? ''; // don't clean passwords (may contain special chars)

        // 3. Attempt login via User class
        $userModel = new User();
        $result    = $userModel->login($email, $password);

        if ($result['success']) {
            // 4a. SUCCESS: start session, flash message, redirect
            startUserSession($result['user']);
            flash('success', 'Welcome back, ' . $result['user']['first_name'] . '! 👋');
            redirect(APP_URL . '/dashboard.php');
        } else {
            // 4b. FAILURE: collect errors for display
            $errors = $result['errors'];
        }
    }
}

renderHeader('Login');
?>

<div class="auth-page">
    <div class="auth-grid">
        <!-- Left: Brand panel -->
        <div class="auth-left">
            <div class="auth-brand"><span class="auth-brand-dot"></span>WeGo</div>
            <div class="auth-tagline">Your next adventure<br>is <span class="hl">one login away.</span></div>
            <div class="auth-sub">Join thousands of travelers across Tunisia. Discover trips, meet people, build memories.</div>
            <div class="auth-features">
                <div class="auth-feat"><i class="fa-solid fa-shield-halved"></i> Secure & verified community</div>
                <div class="auth-feat"><i class="fa-solid fa-star"></i> Trust Score system</div>
                <div class="auth-feat"><i class="fa-solid fa-bolt"></i> Instant booking confirmation</div>
                <div class="auth-feat"><i class="fa-solid fa-users"></i> 12,000+ active travelers</div>
            </div>
        </div>

        <!-- Right: Login form -->
        <div class="auth-right">
            <div class="auth-title">Welcome back 👋</div>
            <div class="auth-subtitle">Sign in to your WeGo account</div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-exclamation-circle"></i>
                <?php foreach ($errors as $err): ?>
                    <?= h($err) ?><br>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!--
                method="POST" because we're sending credentials (sensitive data).
                GET would put the password in the URL → visible in server logs!
                action="" means submit to THIS same page (self-referencing form).
            -->
            <form method="POST" action="" novalidate>
                <?= csrfField() ?>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-icon">
                        <i class="fa-regular fa-envelope"></i>
                        <input class="form-input <?= !empty($errors) ? 'error' : '' ?>"
                               type="email" id="email" name="email"
                               value="<?= h($email) ?>"
                               placeholder="your@email.com"
                               required autocomplete="email"/>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-lock"></i>
                        <input class="form-input <?= !empty($errors) ? 'error' : '' ?>"
                               type="password" id="password" name="password"
                               placeholder="••••••••"
                               required autocomplete="current-password"/>
                    </div>
                </div>

                <div style="text-align:right;margin-bottom:1rem">
                    <a href="#" style="font-size:.78rem;color:var(--p);font-weight:600">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fa-solid fa-right-to-bracket"></i> Sign in
                </button>
            </form>

            <div class="auth-switch">
                Don't have an account?
                <a href="<?= APP_URL ?>/register.php">Sign up free →</a>
            </div>
            <div class="demo-note">
                Demo credentials: <strong>sarra@wego.tn</strong> / <strong>password</strong>
            </div>
        </div>
    </div>
</div>

<?php renderFooter(); ?>
