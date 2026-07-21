<?php
/**
 * WeGo — booking.php
 * Booking checkout — protected page (login required).
 * Demonstrates the full PRG pattern with DB transaction.
 */

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/auth_guard.php'; // redirects if not logged in
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

// ── Validate trip ID ──────────────────────────────────────────
$tripId = (int)($_GET['id'] ?? 0);
if ($tripId < 1) {
    flash('error', 'Invalid trip.');
    redirect(APP_URL . '/explore.php');
}

$tripModel = new Trip();
$trip      = $tripModel->findById($tripId);

if (!$trip) {
    flash('error', 'Trip not found.');
    redirect(APP_URL . '/explore.php');
}

if ((int)$trip['seats_left'] < 1) {
    flash('error', 'Sorry, this trip is fully booked.');
    redirect(APP_URL . '/trip.php?id=' . $tripId);
}

$user    = currentUser();
$fee     = 10;
$total   = (float)$trip['price'] + $fee;
$errors  = [];

// ── Handle POST (booking submission) ─────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Security check failed. Please refresh and try again.';
    } else {
        $payment = in_array($_POST['payment'] ?? '', ['online', 'cash'])
                   ? $_POST['payment'] : 'online';

        $bookingModel = new Booking();
        $result       = $bookingModel->create((int)$user['id'], $tripId, $payment);

        if ($result['success']) {
            // PRG: success → flash → redirect (prevents form resubmission)
            flash('success', '🎉 Seat reserved! Your adventure awaits. Booking #' . $result['booking_id']);
            redirect(APP_URL . '/dashboard.php');
        } else {
            $errors[] = $result['error'];
        }
    }
}

renderHeader('Book — ' . h($trip['name']));
?>

<main style="padding:2rem 0;flex:1">
<div class="container">

    <div style="margin-bottom:1.25rem">
        <a href="<?= APP_URL ?>/trip.php?id=<?= $tripId ?>"
           class="btn btn-outline btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Back to trip
        </a>
    </div>

    <h1 class="sec-title" style="margin-bottom:1.5rem">Complete your booking</h1>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <i class="fa-solid fa-exclamation-circle"></i>
        <?php foreach ($errors as $e): echo h($e) . '<br>'; endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrfField() ?>

        <div class="booking-layout">
            <!-- ── LEFT: Form steps ── -->
            <div>
                <!-- Step 1: Personal Details -->
                <div class="bk-step">
                    <div class="bk-step-hd">
                        <div class="bk-step-num">1</div>
                        <div class="bk-step-title">Your details</div>
                    </div>
                    <div class="bk-step-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                            <div class="form-group">
                                <label>First name</label>
                                <input class="form-input" type="text" name="first_name"
                                       value="<?= h($user['first_name']) ?>" readonly/>
                            </div>
                            <div class="form-group">
                                <label>Last name</label>
                                <input class="form-input" type="text" name="last_name"
                                       value="<?= h($user['last_name']) ?>" readonly/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <div class="input-icon">
                                <i class="fa-regular fa-envelope"></i>
                                <input class="form-input" type="email"
                                       value="<?= h($user['email']) ?>" readonly/>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label>Phone (WhatsApp)</label>
                            <div class="input-icon">
                                <i class="fa-brands fa-whatsapp"></i>
                                <input class="form-input" type="tel" name="phone"
                                       value="<?= h($user['phone'] ?? '') ?>"
                                       placeholder="+216 XX XXX XXX"/>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Payment method — PURE CSS TOGGLE using radio buttons -->
                <!--
                    PURE CSS PAYMENT TOGGLE:
                    Radio buttons act as exclusive state switches.
                    CSS sibling selectors show/hide the relevant panel:
                      #pay-online:checked ~ #panel-online { display: block; }
                      #pay-cash:checked   ~ #panel-cash   { display: block; }
                    No JavaScript needed!
                -->
                <div class="bk-step">
                    <div class="bk-step-hd">
                        <div class="bk-step-num">2</div>
                        <div class="bk-step-title">Payment method</div>
                    </div>
                    <div class="bk-step-body">
                        <div class="pay-opts">
                            <label class="pay-opt" for="pay-online" style="cursor:pointer">
                                <input type="radio" id="pay-online" name="payment"
                                       value="online" checked hidden/>
                                <div class="pay-opt-icon">💳</div>
                                <div class="pay-opt-title">Online Secure</div>
                                <div class="pay-opt-sub">Visa · Mastercard</div>
                            </label>
                            <label class="pay-opt" for="pay-cash" style="cursor:pointer">
                                <input type="radio" id="pay-cash" name="payment"
                                       value="cash" hidden/>
                                <div class="pay-opt-icon">💵</div>
                                <div class="pay-opt-title">Hand-to-Hand</div>
                                <div class="pay-opt-sub">Cash at meetup</div>
                            </label>
                        </div>

                        <!-- Online payment fields -->
                        <div class="pay-panel" id="panel-online">
                            <div class="form-group">
                                <label>Card number</label>
                                <div class="input-icon">
                                    <i class="fa-regular fa-credit-card"></i>
                                    <input class="form-input" type="text"
                                           placeholder="1234 5678 9012 3456"
                                           maxlength="19" autocomplete="cc-number"/>
                                </div>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                                <div class="form-group">
                                    <label>Expiry</label>
                                    <input class="form-input" type="text"
                                           placeholder="MM / YY" maxlength="7"/>
                                </div>
                                <div class="form-group">
                                    <label>CVV</label>
                                    <input class="form-input" type="text"
                                           placeholder="•••" maxlength="3"/>
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label>Cardholder name</label>
                                <input class="form-input" type="text"
                                       placeholder="Name as on card"/>
                            </div>
                        </div>

                        <!-- Cash payment info -->
                        <div class="pay-panel" id="panel-cash">
                            <div class="cash-note">
                                <i class="fa-solid fa-circle-info"></i>
                                <div>
                                    You'll pay <strong><?= number_format((float)$trip['price'], 0) ?> TND cash</strong>
                                    directly to <strong><?= h($trip['org_first']) ?></strong>
                                    at the meetup point on the trip start date.
                                    Your seat is reserved as soon as you confirm.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Confirm & Terms -->
                <div class="bk-step">
                    <div class="bk-step-hd">
                        <div class="bk-step-num">3</div>
                        <div class="bk-step-title">Confirm & agree</div>
                    </div>
                    <div class="bk-step-body">
                        <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:.83rem;color:var(--tx2);line-height:1.6">
                            <input type="checkbox" name="agree" required
                                   style="margin-top:3px;accent-color:var(--p)"/>
                            I agree to WeGo's
                            <span style="color:var(--p);font-weight:600">&nbsp;Terms of Service</span>&nbsp;and&nbsp;<span style="color:var(--p);font-weight:600">Cancellation Policy</span>.
                            I confirm I meet the trip's requirements.
                        </label>
                        <button type="submit" class="btn btn-primary btn-full btn-lg"
                                style="margin-top:1rem">
                            <i class="fa-solid fa-lock"></i> Confirm &amp; Reserve My Seat
                        </button>
                        <div class="ssl-note" style="margin-top:10px">
                            <span class="ssl-dot"></span>
                            256-bit SSL encryption · Your data is protected
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── RIGHT: Order summary ── -->
            <div class="order-card">
                <div class="order-title">Order Summary</div>
                <div class="order-trip">
                    <div class="order-img"><?= h($trip['icon']) ?></div>
                    <div>
                        <div class="order-dest"><?= h($trip['name']) ?></div>
                        <div class="order-dates">
                            <?= h($trip['dates']) ?><br>
                            <?= h($trip['location']) ?>
                        </div>
                    </div>
                </div>
                <div class="order-row">
                    <span class="order-row-lbl">1 seat × <?= number_format((float)$trip['price'], 0) ?> TND</span>
                    <span><?= number_format((float)$trip['price'], 0) ?> TND</span>
                </div>
                <div class="order-row">
                    <span class="order-row-lbl">Platform fee</span>
                    <span><?= $fee ?> TND</span>
                </div>
                <div class="order-row">
                    <span class="order-row-lbl">Insurance (optional)</span>
                    <span>0 TND</span>
                </div>
                <div class="order-row" style="font-weight:700;font-size:.95rem;border-top:1px solid var(--bd);padding-top:10px">
                    <span>Total</span>
                    <span style="color:var(--p);font-size:1.1rem">
                        <?= number_format($total, 0) ?> TND
                    </span>
                </div>
                <div class="divider"></div>
                <div style="font-size:.78rem;color:var(--tx3);line-height:1.85">
                    <div><i class="fa-regular fa-calendar" style="width:14px;color:var(--p)"></i> Free cancellation 48h before trip</div>
                    <div><i class="fa-solid fa-car-side" style="width:14px;color:var(--p)"></i> Transport: <?= h($trip['transport'] ?? 'Covoiturage') ?></div>
                    <div><i class="fa-solid fa-user" style="width:14px;color:var(--p)"></i> Organizer: <?= h($trip['org_first']) ?> — <?= (int)$trip['org_trust'] ?> Trust</div>
                    <div><i class="fa-solid fa-lock" style="width:14px;color:var(--p)"></i> Booking protected by WeGo</div>
                </div>
            </div>
        </div><!-- /booking-layout -->
    </form>
</div>
</main>

<!-- Pure CSS radio → panel toggle -->
<style>
/* Show online panel when online radio is checked */
#pay-online:checked ~ #panel-online,
.pay-opts:has(#pay-online:checked) ~ #panel-online { display: block; }
/* Show cash panel when cash radio is checked */
#pay-online:checked ~ #panel-cash,
.pay-opts:has(#pay-online:checked) ~ #panel-cash   { display: none; }
.pay-opts:has(#pay-cash:checked) ~ #panel-online   { display: none; }
.pay-opts:has(#pay-cash:checked) ~ #panel-cash     { display: block; }
/* Highlight selected pay-opt label */
.pay-opts:has(#pay-online:checked) label[for="pay-online"] { border-color:var(--p);background:var(--p-subtle); }
.pay-opts:has(#pay-cash:checked)   label[for="pay-cash"]   { border-color:var(--p);background:var(--p-subtle); }
</style>

<?php renderFooter(); ?>
