<?php
/**
 * WeGo — dashboard.php
 * User dashboard — protected page.
 */

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$user         = currentUser();
$bookingModel = new Booking();
$userModel    = new User();
$tripModel    = new Trip();

// Refresh user data from DB
$freshUser = $userModel->findById((int)$user['id']);
if ($freshUser) {
    $_SESSION['wego_user'] = array_merge($user, $freshUser);
    $user = $_SESSION['wego_user'];
}

// Fetch user's bookings and hosted trips
$bookings    = $bookingModel->getByUser((int)$user['id']);
$hostedTrips = $tripModel->getHostedByUser((int)$user['id']);

// Separate into upcoming / past
$upcoming = array_filter($bookings, fn($b) => strtotime($b['trip_start'] ?? 'now') >= strtotime('today'));
$past     = array_filter($bookings, fn($b) => strtotime($b['trip_start'] ?? 'now') < strtotime('today'));

$avgRating   = $userModel->getAverageRating((int)$user['id']);
$trustScore  = (int)($user['trust_score'] ?? 70);

// Detect active tab
$activeTab = $_GET['tab'] ?? 'home';

renderHeader('Dashboard');
?>

<div class="dash-wrap">
    <aside class="dash-side">
        <div class="dash-side-logo"><span class="ds-dot"></span>WeGo</div>

        <a class="dash-nav-item <?= $activeTab == 'home' ? 'on' : '' ?>" href="<?= APP_URL ?>/dashboard.php?tab=home">
            <i class="fa-solid fa-house"></i> Home
        </a>
        <a class="dash-nav-item <?= $activeTab == 'host' ? 'on' : '' ?>" href="<?= APP_URL ?>/dashboard.php?tab=host">
            <i class="fa-solid fa-plus-circle"></i> Host a Trip
        </a>
        <a class="dash-nav-item" href="<?= APP_URL ?>/explore.php">
            <i class="fa-solid fa-compass"></i> Explore Trips
        </a>
        <a class="dash-nav-item" href="<?= APP_URL ?>/profile.php?id=<?= (int)$user['id'] ?>">
            <i class="fa-regular fa-user"></i> My Profile
        </a>
        <div class="dash-nav-sep"></div>
        <a class="dash-nav-item" href="<?= APP_URL ?>/logout.php">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </aside>

    <main class="dash-main">

        <?php
        $ok  = getFlash('success');
        $err = getFlash('error');
        if ($ok):  ?><div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= h($ok) ?></div><?php endif;
        if ($err): ?><div class="alert alert-error"><i class="fa-solid fa-exclamation-circle"></i> <?= h($err) ?></div><?php endif;
        ?>

        <div class="dash-ph">
            <div class="av av-lg <?= getAvatarColor((int)$user['id']) ?>">
                <?= h(getInitials($user['first_name'], $user['last_name'])) ?>
            </div>
            <div class="dash-ph-info">
                <div class="name"><?= h($user['first_name'] . ' ' . $user['last_name']) ?></div>
                <div class="handle">
                    @<?= h(strtolower($user['first_name']) . '.' . strtolower($user['last_name'])) ?>
                    · <?= h($user['email']) ?>
                </div>
                <div class="trust">
                    <i class="fa-solid fa-star" style="color:#F59E0B"></i>
                    Trust Score <?= $trustScore ?> · <?= $user['is_verified'] ? '✅ Verified' : 'Unverified' ?>
                </div>
            </div>
            <div class="dash-ph-actions">
                <a href="<?= APP_URL ?>/profile.php?id=<?= (int)$user['id'] ?>" class="btn btn-outline btn-sm">
                    <i class="fa-regular fa-user"></i> Profile
                </a>
                <a href="<?= APP_URL ?>/dashboard.php?tab=host" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> New Trip
                </a>
            </div>
        </div>

        <?php if ($activeTab == 'host'): ?>
            <div class="dash-panel" style="max-width: 800px; margin: 0 auto;">
                <h2 style="font-family:var(--F-disp); margin-bottom:1.5rem">🚀 Host a New Adventure</h2>
                
                <form action="host_process.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px">
                        <div class="form-group">
                            <label class="small text-secondary" style="display:block; margin-bottom:5px">Trip Title</label>
                            <input type="text" name="name" class="form-input" placeholder="e.g. Blue Lagoon Kayaking" required>
                        </div>
                        <div class="form-group">
                            <label class="small text-secondary" style="display:block; margin-bottom:5px">Location</label>
                            <input type="text" name="location" class="form-input" placeholder="e.g. Bizerte, Tunisia" required>
                        </div>
                        <div class="form-group">
                            <label class="small text-secondary" style="display:block; margin-bottom:5px">Start Date</label>
                            <input type="date" name="start_date" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="small text-secondary" style="display:block; margin-bottom:5px">End Date</label>
                            <input type="date" name="end_date" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="small text-secondary" style="display:block; margin-bottom:5px">Price (TND)</label>
                            <input type="number" name="price" class="form-input" placeholder="0" required>
                        </div>
                        <div class="form-group">
                            <label class="small text-secondary" style="display:block; margin-bottom:5px">Max Seats</label>
                            <input type="number" name="seats_max" class="form-input" placeholder="e.g. 10" required>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:20px">
                        <div class="form-group">
                            <label class="small text-secondary" style="display:block; margin-bottom:5px">✨ Trip Vibe</label>
                            <select name="vibe" class="form-input" required>
                                <option value="adventure">🧗 Adventure</option>
                                <option value="camping">⛺ Camping</option>
                                <option value="beach">🏖️ Beach</option>
                                <option value="mountain">🏔️ Mountain</option>
                                <option value="luxury">✨ Luxury</option>
                                <option value="city">🏙️ City Break</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="small text-secondary" style="display:block; margin-bottom:5px">🚐 Transport</label>
                            <select name="transport" class="form-input" required>
                                <option value="Covoiturage">🚗 Covoiturage</option>
                                <option value="Van">🚐 Private Van</option>
                                <option value="Bus">🚌 Bus</option>
                                <option value="Train">🚆 Train</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:20px">
                        <label class="small text-secondary" style="display:block; margin-bottom:5px">Trip Cover Photo</label>
                        <input type="file" name="trip_image" class="form-input" accept="image/*" required>
                    </div>

                    <div class="form-group" style="margin-top:20px">
                        <label class="small text-secondary" style="display:block; margin-bottom:5px">Description</label>
                        <textarea name="description" class="form-input" rows="4" placeholder="Tell travelers about the plan..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full" style="margin-top:25px; height:50px; font-weight:700">
                        <i class="fa-solid fa-paper-plane"></i> Publish Trip
                    </button>
                </form>
            </div>

        <?php else: ?>
            <div class="dash-stats">
                <div class="ds-stat">
                    <div class="ds-stat-icon">✈️</div>
                    <div class="ds-stat-val"><?= (int)($user['trips_taken'] ?? 0) ?></div>
                    <div class="ds-stat-lbl">Trips taken</div>
                </div>
                <div class="ds-stat">
                    <div class="ds-stat-icon">👥</div>
                    <div class="ds-stat-val"><?= (int)($user['connections'] ?? 0) ?></div>
                    <div class="ds-stat-lbl">Connections</div>
                </div>
                <div class="ds-stat">
                    <div class="ds-stat-icon">🏕</div>
                    <div class="ds-stat-val"><?= (int)($user['trips_hosted'] ?? 0) ?></div>
                    <div class="ds-stat-lbl">Trips hosted</div>
                </div>
                <div class="ds-stat">
                    <div class="ds-stat-icon">💰</div>
                    <div class="ds-stat-val">
                        <?= number_format((float)($user['wallet'] ?? 0), 0) ?>
                        <span style="font-size:.7rem;font-weight:400;color:var(--tx3)"> TND</span>
                    </div>
                    <div class="ds-stat-lbl">Wallet</div>
                </div>
            </div>

            <div class="dash-panels">
                <div class="dash-panel">
                    <div class="dp-title">Upcoming Adventures</div>
                    <?php if (empty($upcoming)): ?>
                        <div style="font-size:.83rem;color:var(--tx3);padding:.5rem 0">
                            No upcoming trips yet.
                            <a href="<?= APP_URL ?>/explore.php" style="color:var(--p);font-weight:600">Explore trips →</a>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_slice($upcoming, 0, 3) as $b): ?>
                        <div class="trip-list-item">
                            <div class="tli-dot" style="background:var(--p)"></div>
                            <div>
                                <div class="tli-dest"><?= h($b['trip_name']) ?></div>
                                <div class="tli-date">
                                    <?= h($b['trip_dates'] ?? '') ?> · <?= h($b['trip_location'] ?? '') ?>
                                </div>
                            </div>
                            <div class="tli-badge">
                                <span class="badge <?= $b['status'] === 'confirmed' ? 'badge-green' : 'badge-yellow' ?>">
                                    <?= ucfirst(h($b['status'])) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div>
                    <div class="dash-panel" style="margin-bottom:12px">
                        <div class="dp-title">Wallet</div>
                        <div class="wallet-amt"><?= number_format((float)($user['wallet'] ?? 0), 0) ?> TND</div>
                        <div class="wallet-sub">Available balance</div>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>

    </main>
</div>

<?php renderFooter(); ?>