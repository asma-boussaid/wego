<?php
/**
 * WeGo — host.php
 * Final version with forced styles.
 */
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pageTitle = "Host a Trip";
include 'header.php'; 
?>

<style>
    .wego-input {
        background-color: #0c0c0c !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
        color: white !important;
        border-radius: 8px !important;
        padding: 12px !important;
    }
    .wego-label {
        color: #888 !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        letter-spacing: 1px !important;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
</style>

<div class="container py-5">
    <div class="form-card" style="max-width: 800px; margin: auto; background: #111; padding: 30px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.05);">
        
        <h2 class="mb-4" style="font-family: var(--F-disp); color: white;">🚀 Host a New Adventure</h2>
        
        <form action="host_process.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                
                <div class="col-md-12 mb-3">
                    <label class="wego-label">TRIP TITLE</label>
                    <input type="text" name="name" class="form-control wego-input" placeholder="e.g. Blue Lagoon Kayaking" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="wego-label">LOCATION</label>
                    <input type="text" name="location" class="form-control wego-input" placeholder="e.g. Bizerte, Tunisia" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="wego-label">PRICE (TND)</label>
                    <input type="number" name="price" class="form-control wego-input" placeholder="0.00" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="wego-label">START DATE</label>
                    <input type="date" name="start_date" class="form-control wego-input" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="wego-label">END DATE</label>
                    <input type="date" name="end_date" class="form-control wego-input" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="wego-label">✨ TRIP VIBE</label>
                    <select name="vibe" class="form-control wego-input" required>
                        <option value="adventure">🧗 Adventure</option>
                        <option value="camping">⛺ Camping</option>
                        <option value="beach">🏖️ Beach</option>
                        <option value="mountain">🏔️ Mountain</option>
                        <option value="luxury">✨ Luxury</option>
                        <option value="city">🏙️ City Break</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="wego-label">🚐 TRANSPORT</label>
                    <select name="transport" class="form-control wego-input" required>
                        <option value="Covoiturage">🚗 Covoiturage</option>
                        <option value="Van">🚐 Private Van</option>
                        <option value="Bus">🚌 Bus</option>
                        <option value="Train">🚆 Train</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="wego-label">🪑 MAX SEATS</label>
                    <input type="number" name="seats_max" class="form-control wego-input" placeholder="10" required>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="wego-label">TRIP COVER PHOTO</label>
                    <input type="file" name="trip_image" class="form-control wego-input" accept="image/*" required>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="wego-label">DESCRIPTION</label>
                    <textarea name="description" class="form-control wego-input" rows="5" placeholder="Tell travelers about the plan..."></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100 fw-bold py-3 mt-3" style="background: #28a745; border: none; border-radius: 8px;">
                Publish Trip
            </button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>