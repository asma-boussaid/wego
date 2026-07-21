<?php
/**
 * WeGo — profile.php
 * Public profile page — no login required to view.
 * Tabs implemented with pure CSS :target selector trick.
 */

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

// Get user ID from query string
$profileId = (int)($_GET['id'] ?? 0);
if ($profileId < 1) {
    flash('error', 'User not found.');
    redirect(APP_URL . '/explore.php');
}

$userModel    = new User();
$tripModel    = new Trip();

$profileUser  = $userModel->findById($profileId);
if (!$profileUser) {
    flash('error', 'This user does not exist.');
    redirect(APP_URL . '/explore.php');
}

$reviews     = $userModel->getReviews($profileId);
$avgRating   = $userModel->getAverageRating($profileId);
$hostedTrips = $tripModel->getHostedByUser($profileId);

// Which tab is active? Pure CSS :target — URL hash controls it
// Default tab is "about". No JS needed.
$activeTab = clean($_GET['tab'] ?? 'about');
$tabs = ['about' => 'About', 'trips' => 'Trip History', 'reviews' => 'Reviews (' . count($reviews) . ')', 'hosted' => 'Hosted Trips'];

renderHeader(h($profileUser['first_name'] . ' ' . $profileUser['last_name']) . ' — Profile');
?>

<main style="padding:2rem 0;flex:1">
<div class="container">

    <div style="margin-bottom:1.25rem">
        <a href="javascript:history.back()" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="profile-layout">

        <!-- ── LEFT: Profile card ── -->
        <div class="profile-card">
            <div class="profile-av-wrap">
                <?= h(getInitials($profileUser['first_name'], $profileUser['last_name'])) ?>
            </div>
            <div class="profile-name">
                <?= h($profileUser['first_name'] . ' ' . $profileUser['last_name']) ?>
            </div>
            <div class="profile-handle">
                @<?= h(strtolower($profileUser['first_name']) . '.' . strtolower($profileUser['last_name'])) ?>
            </div>

            <!-- Trust Score widget -->
            <div class="trust-score">
                <div class="ts-num"><?= (int)$profileUser['trust_score'] ?></div>
                <div class="stars">
                    <?php
                    $rounded = round($avgRating);
                    echo str_repeat('★', (int)$rounded) . str_repeat('☆', 5 - (int)$rounded);
                    ?>
                </div>
                <div class="ts-lbl">Trust Score · <?= $avgRating > 0 ? $avgRating . ' avg' : 'No reviews yet' ?></div>
            </div>

            <!-- Stats -->
            <div class="profile-stat-row">
                <div class="ps-stat">
                    <div class="ps-val"><?= (int)$profileUser['trips_taken'] ?></div>
                    <div class="ps-lbl">Trips</div>
                </div>
                <div class="ps-stat">
                    <div class="ps-val"><?= count($reviews) ?></div>
                    <div class="ps-lbl">Reviews</div>
                </div>
                <div class="ps-stat">
                    <div class="ps-val"><?= count($hostedTrips) ?></div>
                    <div class="ps-lbl">Hosted</div>
                </div>
            </div>

            <!-- Badges -->
            <div class="profile-badges">
                <?php if ($profileUser['is_verified']): ?>
                    <span class="profile-badge">✅ Verified</span>
                <?php endif; ?>
                <?php if ((int)$profileUser['trips_taken'] >= 10): ?>
                    <span class="profile-badge">🏅 Frequent traveler</span>
                <?php endif; ?>
                <?php if (!empty($hostedTrips)): ?>
                    <span class="profile-badge">🧭 Organizer</span>
                <?php endif; ?>
                <?php if ((int)$profileUser['trust_score'] >= 90): ?>
                    <span class="profile-badge">⭐ Top traveler</span>
                <?php endif; ?>
            </div>

            <!-- Member since -->
            <div style="font-size:.75rem;color:var(--tx3);margin-bottom:1rem">
                <i class="fa-regular fa-calendar" style="color:var(--p)"></i>
                Member since <?= date('M Y', strtotime($profileUser['created_at'])) ?>
            </div>

            <!-- Own profile: edit button -->
            <?php if (isLoggedIn() && (int)currentUser()['id'] === $profileId): ?>
            <a href="<?= APP_URL ?>/dashboard.php" class="btn btn-primary btn-full btn-sm">
                <i class="fa-solid fa-gauge"></i> Go to dashboard
            </a>
            <?php endif; ?>
        </div>

        <!-- ── RIGHT: Tabbed content ── -->
        <!--
            PURE CSS TABS using <a> links + :target pseudo-class.
            Each tab panel has an id. The tab link sets the URL hash (#tab-id).
            CSS rule:  :target { display: block; }
            All other panels:  display: none  (by default)
            Because :target matches the element whose id = URL hash.

            Fallback: first tab shown by default (CSS .tab-panel:first-of-type).
        -->
        <div class="profile-content">
            <div class="tab-bar">
                <?php foreach ($tabs as $key => $label): ?>
                <a href="?id=<?= $profileId ?>&tab=<?= $key ?>"
                   class="tab-item <?= $activeTab === $key ? 'on' : '' ?>">
                    <?= h($label) ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- About tab -->
            <div class="tab-panel <?= $activeTab === 'about' ? 'on' : '' ?>">
                <div class="card" style="padding:1.5rem;margin-bottom:1rem">
                    <h3 style="font-family:var(--F-disp);font-size:1rem;font-weight:700;margin-bottom:.75rem">
                        About <?= h($profileUser['first_name']) ?>
                    </h3>
                    <p style="font-size:.85rem;color:var(--tx2);line-height:1.75">
                        <?= $profileUser['bio']
                            ? h($profileUser['bio'])
                            : 'This traveler hasn\'t written a bio yet.' ?>
                    </p>
                </div>
                <div class="card" style="padding:1.5rem">
                    <h3 style="font-family:var(--F-disp);font-size:1rem;font-weight:700;margin-bottom:.75rem">Stats</h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <div style="background:var(--bg2);border-radius:var(--r-sm);padding:.9rem">
                            <div style="font-size:1.2rem;font-weight:800;font-family:var(--F-disp);color:var(--p)"><?= (int)$profileUser['trips_taken'] ?></div>
                            <div style="font-size:.75rem;color:var(--tx3)">Trips completed</div>
                        </div>
                        <div style="background:var(--bg2);border-radius:var(--r-sm);padding:.9rem">
                            <div style="font-size:1.2rem;font-weight:800;font-family:var(--F-disp);color:var(--p)"><?= $avgRating ?: '—' ?> ★</div>
                            <div style="font-size:.75rem;color:var(--tx3)">Average rating</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trips history tab -->
            <div class="tab-panel <?= $activeTab === 'trips' ? 'on' : '' ?>">
                <?php if (empty($hostedTrips)): ?>
                    <div style="font-size:.85rem;color:var(--tx2);padding:1rem">No trip history yet.</div>
                <?php else: ?>
                    <div class="trip-grid" style="grid-template-columns:repeat(2,minmax(0,1fr))">
                        <?php foreach ($hostedTrips as $t):
                            $pct = Trip::getSeatFillPercent((int)$t['seats_left'], (int)$t['seats_max']);
                            $cls = Trip::getSeatFillClass($pct);
                        ?>
                        <a class="tc" href="<?= APP_URL ?>/trip.php?id=<?= (int)$t['id'] ?>">
                            <div class="tc-img <?= h($t['img_class'] ?? 'tc-img-1') ?>">
                                <div class="tc-scene"><?= h($t['icon']) ?></div>
                                <div class="tc-vbadge"><?= h($t['icon']) ?> <?= ucfirst(h($t['vibe'])) ?></div>
                            </div>
                            <div class="tc-body">
                                <div class="tc-name"><?= h($t['name']) ?></div>
                                <div class="tc-meta"><?= h($t['dates']) ?></div>
                                <div style="font-size:.78rem;color:var(--p)">
                                    <?= (int)$t['booking_count'] ?> booked
                                    <?php if ($t['avg_rating']): ?>
                                    · <?= round((float)$t['avg_rating'], 1) ?> ★
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Reviews tab -->
            <div class="tab-panel <?= $activeTab === 'reviews' ? 'on' : '' ?>">
                <?php if (empty($reviews)): ?>
                    <div style="font-size:.85rem;color:var(--tx2);padding:1rem">No reviews yet.</div>
                <?php else: ?>
                    <?php foreach ($reviews as $rev): ?>
                    <div class="review-item">
                        <div class="review-hd">
                            <div class="av av-sm <?= getAvatarColor((int)($rev['reviewer_id'] ?? 0)) ?>">
                                <?= h(getInitials($rev['reviewer_first'] ?? 'W', $rev['reviewer_last'] ?? 'G')) ?>
                            </div>
                            <div>
                                <div style="font-size:.85rem;font-weight:600">
                                    <?= h($rev['reviewer_first'] . ' ' . $rev['reviewer_last']) ?>
                                </div>
                                <div class="review-meta">
                                    <span class="stars"><?= str_repeat('★', (int)$rev['rating']) ?></span>
                                    · <?= h($rev['trip_name'] ?? '') ?>
                                    · <?= timeAgo($rev['created_at']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="review-txt"><?= h($rev['comment'] ?? '') ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Hosted trips tab -->
            <div class="tab-panel <?= $activeTab === 'hosted' ? 'on' : '' ?>">
                <?php if (empty($hostedTrips)): ?>
                    <div style="font-size:.85rem;color:var(--tx2);padding:1rem">No hosted trips yet.</div>
                <?php else: ?>
                    <?php foreach ($hostedTrips as $t): ?>
                    <div class="card" style="padding:1.1rem;margin-bottom:10px;display:flex;align-items:center;gap:14px">
                        <div style="width:48px;height:48px;border-radius:var(--r-sm);background:linear-gradient(145deg,#062114,#2ECC71);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0">
                            <?= h($t['icon']) ?>
                        </div>
                        <div style="flex:1">
                            <div style="font-family:var(--F-disp);font-size:.9rem;font-weight:700;margin-bottom:2px">
                                <?= h($t['name']) ?>
                            </div>
                            <div style="font-size:.75rem;color:var(--tx2)">
                                <?= h($t['dates']) ?> · <?= (int)$t['booking_count'] ?> booked
                                <?php if ($t['avg_rating']): ?>
                                · ★ <?= round((float)$t['avg_rating'], 1) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="badge badge-green">Active</span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div><!-- /profile-content -->
    </div><!-- /profile-layout -->
</div>
</main>

<?php renderFooter(); ?>
