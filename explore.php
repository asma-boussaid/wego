<?php
/**
 * WeGo — explore.php
 * Updated with Dynamic Images and Star Ratings.
 */

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

// ── 1. GET FILTERS ───────────────────────────────────────────
$filterVibe  = clean($_GET['vibe']      ?? '');
$filterDest  = clean($_GET['dest']      ?? '');
$filterDate  = clean($_GET['date']      ?? '');
$filterSort  = clean($_GET['sort']      ?? '');
$currentPage = max(1, (int)($_GET['p'] ?? 1));

$filters = array_filter([
    'vibe' => $filterVibe,
    'dest' => $filterDest,
    'date' => $filterDate,
    'sort' => $filterSort,
]);

// ── 2. FETCH DATA ────────────────────────────────────────────
$tripModel  = new Trip();
$totalTrips = $tripModel->countAll($filters);
$pagination = paginate($totalTrips, Trip::TRIPS_PER_PAGE, $currentPage);

// Hna lezem el getAll() mte3ek t-supporti el Avg Rating (zidha fil SQL mta3 el Trip class)
$trips      = $tripModel->getAll($filters, Trip::TRIPS_PER_PAGE, $pagination['offset']);

$vibeLabels = [
    ''            => 'All',
    'camping'     => '🏕 Camping',
    'beach'       => '🏖 Beach',
    'mountain'    => '🏔 Mountain',
    'luxury'      => '💎 Luxury',
    'adventure'   => '🚵 Adventure',
    'city'        => '🌆 City',
    'backpacker'  => '🎒 Backpacker',
];

function filterUrl(array $overrides = []): string {
    global $filterVibe, $filterDest, $filterDate, $filterSort;
    $params = array_filter([
        'vibe' => $overrides['vibe'] ?? $filterVibe,
        'dest' => $overrides['dest'] ?? $filterDest,
        'date' => $overrides['date'] ?? $filterDate,
        'sort' => $overrides['sort'] ?? $filterSort,
        'p'    => $overrides['p']    ?? '',
    ]);
    return APP_URL . '/explore.php' . ($params ? '?' . http_build_query($params) : '');
}

renderHeader('Explore Trips');
?>

<main style="padding:2rem 0;flex:1">
<div class="container">

    <div class="sec-hd">
        <h1 class="sec-title">Explore Trips</h1>
        <span class="badge badge-green">
            <i class="fa-solid fa-circle" style="font-size:.45rem"></i>
            <?= $totalTrips ?> trips found
        </span>
    </div>

    <div class="filters-bar">
        <div class="chips-container" style="display:flex; gap:8px; overflow-x:auto; padding-bottom:10px;">
            <?php foreach ($vibeLabels as $vibe => $label):
                $isActive = ($filterVibe === $vibe);
                $url      = filterUrl(['vibe' => $vibe, 'p' => '']);
            ?>
                <a href="<?= h($url) ?>" class="filter-chip <?= $isActive ? 'on' : '' ?>" style="white-space:nowrap">
                    <?= h($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <form method="GET" action="<?= APP_URL ?>/explore.php" style="margin-left:auto;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <input class="form-input" style="padding:6px 12px;font-size:.78rem;width:170px" type="text" name="dest" placeholder="Search destination…" value="<?= h($filterDest) ?>"/>
            <select class="filter-select" name="sort" onchange="this.form.submit()">
                <option value="" <?= $filterSort==='' ? 'selected':'' ?>>Trending</option>
                <option value="price_asc" <?= $filterSort==='price_asc' ? 'selected':'' ?>>Price: Low → High</option>
                <option value="price_desc" <?= $filterSort==='price_desc' ? 'selected':'' ?>>Price: High → Low</option>
                <option value="newest" <?= $filterSort==='newest' ? 'selected':'' ?>>Newest first</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>
    </div>

    <?php if (empty($trips)): ?>
        <div class="alert alert-info" style="text-align:center;padding:2.5rem">
            No trips found. <a href="<?= APP_URL ?>/explore.php">Reset all</a>
        </div>
    <?php else: ?>
        <div class="trip-grid">
            <?php foreach ($trips as $trip):
                $pct = Trip::getSeatFillPercent((int)$trip['seats_left'], (int)$trip['seats_max']);
                $cls = Trip::getSeatFillClass($pct);
                
                // ── DYNAMIC IMAGE LOGIC ───────────────────────
                $imgField = $trip['img_class'];
                $isUpload = (strpos($imgField, '.') !== false);
                $imageUrl = APP_URL . "/uploads/" . h($imgField);
                $bgStyle  = $isUpload ? "background-image: url('$imageUrl'); background-size: cover; background-position: center;" : "";
                
                $orgIni   = getInitials($trip['org_first'], $trip['org_last']);
                $orgColor = getAvatarColor((int)$trip['organizer_id']);
                
                // ── RATING LOGIC ─────────────────────────────
                $avgRating = isset($trip['avg_rating']) ? round((float)$trip['avg_rating'], 1) : 0;
            ?>
            <a class="tc" href="<?= APP_URL ?>/trip.php?id=<?= (int)$trip['id'] ?>">
                <div class="tc-img <?= !$isUpload ? h($imgField) : '' ?>" style="<?= $bgStyle ?>">
                    <div class="tc-scene"><?= h($trip['icon']) ?></div>
                    <div class="av av-sm <?= $orgColor ?> tc-org"><?= h($orgIni) ?></div>
                    <div class="tc-vbadge"><?= h($trip['icon']) ?> <?= ucfirst(h($trip['vibe'])) ?></div>
                    
                    <?php if ($avgRating > 0): ?>
                        <div class="tc-rating" style="position:absolute; bottom:10px; right:10px; background:rgba(0,0,0,0.6); color:#FFD700; padding:2px 8px; border-radius:12px; font-size:0.75rem; font-weight:bold; display:flex; align-items:center; gap:4px;">
                            <i class="fa-solid fa-star"></i> <?= $avgRating ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tc-body">
                    <div class="tc-name"><?= h($trip['name']) ?></div>
                    <div class="tc-meta">
                        <i class="fa-solid fa-location-dot" style="color:var(--p);font-size:.68rem"></i>
                        <?= h($trip['location']) ?> · <?= h($trip['dates']) ?>
                    </div>
                    
                    <div class="tc-ft" style="margin-top:10px; border-top:1px solid var(--bg2); padding-top:10px;">
                        <div class="tc-price">
                            <span style="font-size:1.1rem; font-weight:800; color:var(--p);"><?= number_format((float)$trip['price'], 0) ?> TND</span><small>/person</small>
                        </div>
                        <div class="tc-seats">
                            <div class="tc-sl" style="font-size:0.7rem; margin-bottom:4px; display:flex; justify-content:space-between">
                                <span><?= (int)$trip['seats_left'] ?> seats left</span>
                                <span style="color:var(--tx3)"><?= 100-$pct ?>% full</span>
                            </div>
                            <div class="seats-bar" style="height:6px; background:var(--bg2); border-radius:10px; overflow:hidden">
                                <div class="seats-fill <?= $cls ?>" style="width:<?= $pct ?>%; height:100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($pagination['total_pages'] > 1): ?>
        <div style="display:flex;justify-content:center;gap:8px;margin-top:2rem;">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a href="<?= h(filterUrl(['p' => $i])) ?>"
                   class="btn btn-sm <?= $i === $pagination['current_page'] ? 'btn-primary' : 'btn-outline' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>

</div>
</main>

<?php renderFooter(); ?>