<?php
/**
 * WeGo — review_process.php
 * Final Corrected Version
 */

require_once __DIR__ . '/includes/init.php';

// 1. Thabbet el user connecte
if (!isLoggedIn()) {
    flash('error', 'You must be logged in to leave a review.');
    redirect(APP_URL . '/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // FIX: Nadhou lel Database instance w ba3d el PDO connection
    $dbInstance = Database::getInstance();
    $db = $dbInstance->getConnection(); 

    $user = currentUser();

    // 2. Te5ou el data mel Form
    $trip_id     = (int)($_POST['trip_id'] ?? 0);
    $reviewed_id = (int)($_POST['reviewed_id'] ?? 0); 
    $rating      = (int)($_POST['rating'] ?? 5);
    $comment     = trim($_POST['comment'] ?? '');

    // Validation sghira
    if (empty($comment)) {
        flash('error', 'Please write a comment before posting.');
        redirect(APP_URL . "/trip.php?id=$trip_id");
    }

    try {
        // 3. L-insertion fil table reviews
        $sql = "INSERT INTO reviews (reviewer_id, reviewed_id, trip_id, rating, comment, created_at) 
                VALUES (:rev_id, :target_id, :t_id, :rat, :msg, NOW())";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':rev_id'    => $user['id'],
            ':target_id' => $reviewed_id,
            ':t_id'      => $trip_id,
            ':rat'       => $rating,
            ':msg'       => $comment
        ]);

        // 4. Update Trust Score mta3 el organizer kima fil UI mte3ek
        $updateSql = "UPDATE users SET trust_score = trust_score + 2 WHERE id = :oid";
        $db->prepare($updateSql)->execute([':oid' => $reviewed_id]);

        flash('success', 'Thank you! Your review has been posted. ✨');

    } catch (PDOException $e) {
        // Itha thamma mochkla fil permissions (Error #1142)
        flash('error', 'Database Error: ' . $e->getMessage());
    }

    // 5. Yarja3 lel page trip.php
    redirect(APP_URL . "/trip.php?id=$trip_id");
} else {
    redirect(APP_URL . '/explore.php');
}