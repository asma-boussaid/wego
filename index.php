<?php
/**
 * WeGo — index.php (Home Page)
 */

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

// ── Fetch data ────────────────────────────────────────────────
$tripModel  = new Trip();
// Get the 3 most recent trips for the homepage grid
$homeTrips  = $tripModel->getAll([], 3, 0);

renderHeader('WeGo — Travel Together');
?>

<main>
<section class="hero">
    <div class="container">
        <div class="hero-grid">
            <div>
                <div class="h-badge">
                    <span class="h-pulse"></span> 400+ trips this month
                </div>
                <h1 class="h-title">
                    Travel with people<br>who share your
                    <span class="h-acc">vibe.</span>
                </h1>
                <p class="h-sub">
                    Discover group adventures organized by real travelers.
                    Book your seat, meet your crew, and create memories that last.
                </p>
                <div class="h-btns">
                    <a href="<?= APP_URL ?>/explore.php" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-compass"></i> Explore Trips
                    </a>
                    <a href="<?= APP_URL ?>/register.php" class="btn btn-outline btn-lg">
                        <i class="fa-solid fa-rocket"></i> Join free
                    </a>
                </div>
            </div>
            
            <div class="h-visual">
    <div class="h-stat-bubble2">
        <i class="fa-solid fa-shield-halved"></i>
        <div><div class="n">96%</div><div class="l">Satisfaction</div></div>
    </div>
    <div class="h-main-card">
        <div class="h-img" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.6)), url('<?= APP_URL ?>/uploads/Screenshot 2026-04-19 022058.png'); background-size: cover; background-position: center; display: flex; align-items: flex-end;">
            
            <div class="h-badge-float">🟢 Trending · Atlas Mountains</div>
            
            </div>
        <div class="h-card-body">
            <div class="h-card-row">
                <div>
                    <div class="h-dest">Atlas Mountains Escape</div>
                    <div class="h-meta">📍 Morocco · Jun 14–18</div>
                </div>
                <div class="h-price">180 TND</div>
            </div>
        </div>
    </div>
</div>
        </div>

        <div class="search-wrap">
            <form class="search-card" method="GET" action="<?= APP_URL ?>/explore.php">
                <div class="sg">
                    <div class="sf">
                        <label for="s-dest">Destination</label>
                        <div class="sf-row">
                            <i class="fa-solid fa-location-dot"></i>
                            <input type="text" id="s-dest" name="dest" placeholder="Where to?"/>
                        </div>
                    </div>
                    <div class="sdiv"></div>
                    <div class="sf">
                        <label for="s-vibe">Travel Vibe</label>
                        <div class="sf-row">
                            <i class="fa-solid fa-fire"></i>
                            <select id="s-vibe" name="vibe">
                                <option value="">Any vibe</option>
                                <?php foreach (Trip::VIBES as $v): ?>
                                <option value="<?= h($v) ?>"><?= ucfirst(h($v)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="s-btn">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="sec-hd">
            <h2 class="sec-title">Trending Adventures</h2>
            <a href="<?= APP_URL ?>/explore.php" class="sec-link">
                View all <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        <div class="trip-grid">
            <?php foreach ($homeTrips as $trip):
                $pct = Trip::getSeatFillPercent((int)$trip['seats_left'], (int)$trip['seats_max']);
                $cls = Trip::getSeatFillClass($pct);
                $orgInitials = getInitials($trip['org_first'], $trip['org_last']);
                $orgColor    = getAvatarColor((int)$trip['organizer_id']);

                // ── LOGIC JDIED: Check if img_class is a filename (e.g., photo.jpg)
                $imgField = $trip['img_class'] ?? 'tc-img-1';
                $isUpload = (strpos($imgField, '.') !== false);
                
                if ($isUpload) {
                    $imageUrl = APP_URL . "/uploads/" . h($imgField);
                    $bgStyle  = "background-image: url('$imageUrl'); background-size: cover; background-position: center;";
                    $extraClass = ""; 
                } else {
                    $bgStyle  = ""; 
                    $extraClass = h($imgField);
                }
            ?>
            <a class="tc" href="<?= APP_URL ?>/trip.php?id=<?= (int)$trip['id'] ?>">
                <div class="tc-img <?= $extraClass ?>" style="<?= $bgStyle ?>">
                    <div class="tc-scene"><?= h($trip['icon']) ?></div>
                    <div class="av av-sm <?= $orgColor ?> tc-org"><?= h($orgInitials) ?></div>
                    <div class="tc-vbadge"><?= h($trip['icon']) ?> <?= ucfirst(h($trip['vibe'])) ?></div>
                </div>
                <div class="tc-body">
                    <div class="tc-name"><?= h($trip['name']) ?></div>
                    <div class="tc-meta">
                        <i class="fa-solid fa-location-dot" style="color:var(--p);font-size:.68rem"></i>
                        <?= h($trip['location']) ?> · <?= h($trip['dates']) ?>
                    </div>
                    <div class="tc-ft">
                        <div class="tc-price"><?= number_format((float)$trip['price'], 0) ?> TND<small>/person</small></div>
                        <div class="tc-seats">
                            <div class="tc-sl">
                                <span><?= (int)$trip['seats_left'] ?> seats left</span>
                            </div>
                            <div class="seats-bar">
                                <div class="seats-fill <?= $cls ?>" style="width:<?= $pct ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" style="background:var(--bg2)">
    <div class="container">
        <div class="sec-hd"><h2 class="sec-title">How WeGo Works</h2></div>
        <div class="steps">
            <div class="step-card">
                <div class="step-icon">🔍</div>
                <div class="step-num">01</div>
                <div class="step-title">Discover trips</div>
                <div class="step-desc">Browse curated group adventures.</div>
            </div>
            <div class="step-card">
                <div class="step-icon">🎟</div>
                <div class="step-num">02</div>
                <div class="step-title">Book your seat</div>
                <div class="step-desc">Reserve instantly with secure payment.</div>
            </div>
            <div class="step-card">
                <div class="step-icon">🌍</div>
                <div class="step-num">03</div>
                <div class="step-title">Travel & connect</div>
                <div class="step-desc">Meet your crew and build your Trust Score.</div>
            </div>
        
        </div>
    </div>
   <div style="margin-top: 80px; padding: 40px 0; border-top: 1px solid rgba(255,255,255,0.05); background: linear-gradient(to bottom, transparent, #080b0e);">
    <div class="container" style="text-align: center;">
        
        <p style="color: #9ca3af; font-size: 0.85rem; letter-spacing: 0.5px; line-height: 1.6;">
            &copy; <?= date('Y') ?> <span style="color: #fff; font-weight: 600;">WeGo Platform</span>. 
            All rights reserved.
        </p>

        <p style="color: #6b7280; font-size: 0.8rem; margin-top: 10px;">
            <i class="fa-solid fa-terminal" style="color: var(--p); font-size: 0.7rem;"></i> 
            Designed & Engineered with ❤️ by 
            <a href="profile.php?id=<?= (int)$trip['organizer_id'] ?? 1 ?>" 
               style="color: var(--p); text-decoration: none; font-weight: 600; border-bottom: 1px solid transparent; transition: 0.3s;"
               onmouseover="this.style.borderBottom='1px solid var(--p)'" 
               onmouseout="this.style.borderBottom='1px solid transparent'">
               Asma Boussaid
            </a>
        </p>

        <div style="margin-top: 15px;">
            <span style="background: rgba(0, 255, 136, 0.1); color: var(--p); padding: 4px 12px; border-radius: 50px; font-size: 0.7rem; border: 1px solid rgba(0, 255, 136, 0.2); font-weight: 500;">
                ICT Student & Full-Stack Developer
            </span>
        </div>

    </div>
</div>
</div>
</section>
</main>

<?php renderFooter(); ?>