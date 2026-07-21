<?php
/**
 * WeGo — register.php
 * Uses the same PRG pattern as login.php
 */

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

if (isLoggedIn()) { redirect(APP_URL . '/dashboard.php'); }

$errors = [];
// Preserve inputs on validation failure (don't make user retype everything)
$inputs = ['first_name'=>'','last_name'=>'','email'=>'','phone'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Security check failed. Please try again.';
    } else {
        // Collect and sanitize inputs
        $inputs = [
            'first_name' => clean($_POST['first_name'] ?? ''),
            'last_name'  => clean($_POST['last_name']  ?? ''),
            'email'      => clean($_POST['email']      ?? ''),
            'phone'      => clean($_POST['phone']      ?? ''),
        ];
        $password  = $_POST['password']  ?? '';
        $password2 = $_POST['password2'] ?? '';

        // Client-side + server-side: confirm passwords match
        if ($password !== $password2) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            $userModel = new User();
            $result    = $userModel->register(
                $inputs['first_name'], $inputs['last_name'],
                $inputs['email'], $inputs['phone'], $password
            );

            if ($result['success']) {
                // Auto-login after registration (better UX)
                $newUser = $userModel->findById($result['user_id']);
                unset($newUser['password']); // never store hash in session
                startUserSession($newUser);
                flash('success', 'Welcome to WeGo, ' . $inputs['first_name'] . '! 🎉');
                redirect(APP_URL . '/dashboard.php');
            } else {
                $errors = $result['errors'];
            }
        }
    }
}

renderHeader('Create Account');
?>

<div class="auth-page">
    <div class="auth-grid">
        <div class="auth-left">
            <div class="auth-brand"><span class="auth-brand-dot"></span>WeGo</div>
            <div class="auth-tagline">Start your travel<br>journey <span class="hl">today.</span></div>
            <div class="auth-sub">Create your free account and join the fastest-growing travel community in North Africa.</div>
            <div class="auth-features">
                <div class="auth-feat"><i class="fa-solid fa-gift"></i> Free to join, always</div>
                <div class="auth-feat"><i class="fa-solid fa-shield-halved"></i> Verified community</div>
                <div class="auth-feat"><i class="fa-solid fa-star"></i> Build your Trust Score</div>
                <div class="auth-feat"><i class="fa-solid fa-plane"></i> 840+ trips to explore</div>
            </div>
        </div>
        <div class="auth-right">
            <div class="auth-title">Create account ✨</div>
            <div class="auth-subtitle">Join WeGo free — no credit card needed</div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-exclamation-circle"></i>
                <?php foreach ($errors as $err): echo h($err) . '<br>'; endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <?= csrfField() ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div class="form-group">
                        <label for="first_name">First name</label>
                        <input class="form-input" type="text" id="first_name" name="first_name"
                               value="<?= h($inputs['first_name']) ?>" placeholder="Sarra" required/>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last name</label>
                        <input class="form-input" type="text" id="last_name" name="last_name"
                               value="<?= h($inputs['last_name']) ?>" placeholder="Ayari" required/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-icon"><i class="fa-regular fa-envelope"></i>
                        <input class="form-input" type="email" id="email" name="email"
                               value="<?= h($inputs['email']) ?>" placeholder="your@email.com" required/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="phone">Phone (WhatsApp)</label>
                    <div class="input-icon"><i class="fa-brands fa-whatsapp"></i>
                        <input class="form-input" type="tel" id="phone" name="phone"
                               value="<?= h($inputs['phone']) ?>" placeholder="+216 XX XXX XXX"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon"><i class="fa-solid fa-lock"></i>
                        <input class="form-input" type="password" id="password" name="password"
                               placeholder="Min. 6 characters" required minlength="6"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password2">Confirm password</label>
                    <div class="input-icon"><i class="fa-solid fa-lock"></i>
                        <input class="form-input" type="password" id="password2" name="password2"
                               placeholder="Repeat your password" required/>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fa-solid fa-rocket"></i> Create my free account
                </button>
                <div style="font-size:.72rem;color:var(--tx3);text-align:center;margin-top:.6rem">
                    By signing up you agree to our <a href="#" style="color:var(--p)">Terms</a>
                    and <a href="#" style="color:var(--p)">Privacy Policy</a>
                </div>
            </form>
            <div class="auth-switch">Already have an account? <a href="<?= APP_URL ?>/login.php">Sign in →</a></div>
        </div>
    </div>
</div>

<?php renderFooter(); ?>
