<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/Database.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        flash('error', 'Security token expired.');
        redirect('dashboard.php?tab=host');
    }

    $user = currentUser();
    $db = Database::getInstance()->getConnection();

    // 1. Handle File Upload
    $upload_dir = __DIR__ . "/uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $file_ext  = strtolower(pathinfo($_FILES["trip_image"]["name"], PATHINFO_EXTENSION));
    $file_name = "trip_" . time() . "_" . uniqid() . "." . $file_ext;
    $target_file = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES["trip_image"]["tmp_name"], $target_file)) {
        try {
            // 2. Format Date Range (kima fil base: "May 09 – May 09, 2026")
            $start = new DateTime($_POST['start_date']);
            $end   = new DateTime($_POST['end_date']);
            if ($start == $end) {
                $dateRange = $start->format('M d') . ' – ' . $end->format('M d, Y');
            } else {
                $dateRange = $start->format('M d') . ' – ' . $end->format('M d, Y');
            }

            // 3. Vibe to Icon Mapping
            $vibe = $_POST['vibe'] ?? 'adventure';
            $vibeIcons = [
                'adventure' => '🧗', 'camping' => '⛺', 'beach' => '🏖️', 
                'mountain' => '🏔️', 'luxury' => '✨', 'city' => '🏙️', 'backpacker' => '🎒'
            ];
            $icon = $vibeIcons[$vibe] ?? '🌍';

            // 4. SQL Insert (Hasb el columns mte3ek bedhabt)
            $sql = "INSERT INTO trips (
                organizer_id, name, location, dates, start_date, end_date, 
                price, vibe, icon, img_class, seats_left, seats_max, 
                description, transport, is_active, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                (int)$user['id'],
                clean($_POST['name']),
                clean($_POST['location']),
                $dateRange,
                $_POST['start_date'],
                $_POST['end_date'],
                (float)$_POST['price'],
                $vibe,
                $icon,
                $file_name, // hna iwalli i-sajjel esm el taswira kima "trip_177...png"
                (int)$_POST['seats_max'],
                (int)$_POST['seats_max'],
                clean($_POST['description']),
                clean($_POST['transport'])
            ]);

            flash('success', 'Trip published successfully!');
            redirect('explore.php');

        } catch (Exception $e) {
            if (file_exists($target_file)) unlink($target_file);
            die("Error: " . $e->getMessage()); // Beih hna bech nchoufou ay error i-sir
        }
    } else {
        flash('error', 'Failed to upload image.');
        redirect('dashboard.php?tab=host');
    }
}