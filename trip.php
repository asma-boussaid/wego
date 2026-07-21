<?php
/**
 * WeGo — trip.php
 * Single trip detail page with Comments section.
 */

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

// ── Validate trip ID ──────────────────────────────────────────
$tripId = (int)($_GET['id'] ?? 0);
if ($tripId < 1) {
    flash('error', 'Invalid trip ID.');
    redirect(APP_URL . '/explore.php');
}

// ── Fetch data ────────────────────────────────────────────────
$tripModel    = new Trip();
$bookingModel = new Booking();
$trip         = $tripModel->findById($tripId);

if (!$trip) {
    flash('error', 'Trip not found or no longer available.');
    redirect(APP_URL . '/explore.php');
}

$reviews   = $tripModel->getReviews($tripId);
$avgRating = $tripModel->getAverageRating($tripId) ?: 4.9;

$alreadyBooked = false;
if (isLoggedIn()) {
    $alreadyBooked = $bookingModel->hasUserBooked((int)currentUser()['id'], $tripId);
}

// Seat fill logic
$seatPct = Trip::getSeatFillPercent((int)$trip['seats_left'], (int)$trip['seats_max']);
$seatCls = Trip::getSeatFillClass($seatPct);
$taken   = (int)$trip['seats_max'] - (int)$trip['seats_left'];

// ── Dynamic Background Logic ──
$imgField = $trip['img_class'] ?? 'dh-1';
$heroStyle = "";
$heroCls = "";

if (strpos($imgField, '.') !== false) {
    $coverUrl = APP_URL . "/uploads/" . h($imgField);
    $heroStyle = "background-image: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.7)), url('$coverUrl'); background-size: cover; background-position: center;";
} else {
    $heroCls = $imgField;
}

renderHeader(h($trip['name']));
?>

<main style="padding:2rem 0;flex:1">
<div class="container">
    
    <div style="margin-bottom:1.25rem">
        <a href="<?= APP_URL ?>/explore.php" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Back to trips
        </a>
    </div>

    <div class="detail-hero <?= $heroCls ?>" style="<?= $heroStyle ?>">
        <div class="dh-emoji"><?= h($trip['icon']) ?></div>
        <div style="position:relative;z-index:1;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <span class="badge badge-green">
                <?= h($trip['icon']) ?> <?= ucfirst(h($trip['vibe'])) ?>
            </span>
            <?php if ((int)$trip['seats_left'] <= 3): ?>
            <span class="badge badge-red">
                <i class="fa-solid fa-fire"></i> Only <?= (int)$trip['seats_left'] ?> seats left!
            </span>
            <?php endif; ?>
            <?php if ($avgRating > 0): ?>
            <span class="badge" style="background:rgba(237, 218, 7, 0.2);color:#7A6A00;border:1px solid rgba(245,230,66,.4)">
                ★ <?= $avgRating ?> / 5
                (<?= count($reviews) ?> review<?= count($reviews) !== 1 ? 's' : '' ?>)
            </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-grid">
        <div>
            <h1 class="detail-title"><?= h($trip['name']) ?></h1>

            <div class="detail-meta">
                <div class="dmeta-item"><i class="fa-solid fa-location-dot"></i> <span><?= h($trip['location']) ?></span></div>
                <div class="dmeta-item"><i class="fa-regular fa-calendar"></i> <span><?= h($trip['dates']) ?></span></div>
                <div class="dmeta-item"><i class="fa-solid fa-users"></i> <span><?= (int)$trip['seats_max'] ?> max travelers</span></div>
                <div class="dmeta-item"><i class="fa-solid fa-car-side"></i> <span><?= h($trip['transport'] ?? 'Covoiturage') ?></span></div>
            </div>

            <p class="detail-desc"><?= h($trip['description'] ?? 'An incredible group adventure awaits.') ?></p>

            <div class="detail-section">
                <div class="detail-sec-title">What's included</div>
                <div class="incl-grid">
                    <div class="incl-item"><i class="fa-solid fa-check"></i> Transport</div>
                    <div class="incl-item"><i class="fa-solid fa-check"></i> Group WhatsApp chat</div>
                    <div class="incl-item"><i class="fa-solid fa-check"></i> Local guide</div>
                    <div class="incl-item"><i class="fa-solid fa-xmark" style="color:var(--danger)"></i> Meals</div>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-sec-title">Organizer</div>
                <div class="organizer-card">
                    <div class="av av-md <?= getAvatarColor((int)$trip['organizer_id']) ?>">
                        <?= h(getInitials($trip['org_first'], $trip['org_last'])) ?>
                    </div>
                    <div>
                        <div class="org-name"><?= h($trip['org_first'] . ' ' . $trip['org_last']) ?></div>
                        <div class="org-meta">★ ★ ★ ★ ★ <?= (int)$trip['org_trust'] ?> Trust Score</div>
                    </div>
                    <a href="<?= APP_URL ?>/profile.php?id=<?= (int)$trip['organizer_id'] ?>" class="btn btn-outline btn-sm" style="margin-left:auto">View profile</a>
                </div>
            </div>

            <div class="detail-section" style="margin-top: 3rem;">
                <div class="detail-sec-title">💬 Travelers' Thoughts</div>
                
                <?php if (isLoggedIn()): ?>
                <div style="background: var(--bg2); padding: 20px; border-radius: 15px; margin-bottom: 25px; border: 1px solid rgba(0,255,136,0.1);">
                    <form action="review_process.php" method="POST">
                        <input type="hidden" name="trip_id" value="<?= (int)$trip['id'] ?>">
                        <input type="hidden" name="reviewed_id" value="<?= (int)$trip['organizer_id'] ?>">
                        
                        <div class="mb-3">
                            <label class="small text-secondary">Rating</label>
                            <select name="rating" class="form-input" style="width:100%; background:var(--bg); color:white;">
                                <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                <option value="4">⭐⭐⭐⭐ Very Good</option>
                                <option value="3">⭐⭐⭐ Good</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="small text-secondary">Your Comment</label>
                            <textarea name="comment" class="form-input" rows="3" placeholder="How was the organization?..." style="width:100%; background:var(--bg); color:white;" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-full">Post Comment</button>
                    </form>
                </div>
                <?php endif; ?>

                <div class="comments-list">
                    <?php if (empty($reviews)): ?>
                        <p class="text-secondary small">No comments yet. Be the first to share!</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $rev): ?>
                        <div style="background: var(--bg2); padding: 15px; border-radius: 12px; margin-bottom: 12px; border-left: 3px solid var(--p);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong style="font-size: 0.9rem;"><?= h($rev['first_name'] ?? 'Traveler') ?></strong>
                                <span style="color: #F59E0B; font-size: 0.8rem;"><?= str_repeat('★', $rev['rating']) ?></span>
                            </div>
                            <p class="small text-secondary" style="margin: 8px 0 0;">"<?= h($rev['comment']) ?>"</p>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div>
            <div class="book-sidebar">
                <div class="book-price-row">
                    <span class="book-price"><?= number_format((float)$trip['price'], 0) ?> TND</span>
                    <span class="book-per">per person</span>
                </div>

                <div class="seats-visual" style="margin-top:1rem">
                    <?php for ($i = 0; $i < (int)$trip['seats_max']; $i++): 
                        $dotCls = ($i < $taken) ? 'taken' : (($i === $taken) ? 'mine' : 'avail'); ?>
                        <div class="seat-dot <?= $dotCls ?>"></div>
                    <?php endfor; ?>
                </div>
                <div class="seats-bar" style="margin:8px 0 1.25rem">
                    <div class="seats-fill <?= $seatCls ?>" style="width:<?= $seatPct ?>%"></div>
                </div>

                <?php if ($alreadyBooked): ?>
                    <div class="alert alert-success">Already booked!</div>
                <?php elseif ((int)$trip['seats_left'] < 1): ?>
                    <button class="btn btn-full" disabled>Fully booked</button>
                <?php elseif (!isLoggedIn()): ?>
                    <a href="<?= APP_URL ?>/login.php" class="btn btn-primary btn-full">Login to book</a>
                <?php else: ?>
                    <a href="<?= APP_URL ?>/booking.php?id=<?= (int)$trip['id'] ?>" class="btn btn-primary btn-full">Book this trip</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</main>

<?php renderFooter(); ?>