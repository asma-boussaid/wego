<?php
/**
 * WeGo — logout.php
 *
 * Destroys the session completely and redirects to home.
 * This is a simple action page — no HTML output.
 *
 * SECURITY: No CSRF check needed here because:
 *   - Worst case: someone tricks user into logging out (not dangerous)
 *   - Real damage (booking, delete) DOES require CSRF protection
 */

require_once __DIR__ . '/includes/init.php';

$firstName = currentUser()['first_name'] ?? 'Traveler';

destroySession();

// Start a new session just to hold the flash message
session_name('wego_sess');
session_start();

flash('success', 'See you next adventure, ' . $firstName . '! 👋');
redirect(APP_URL . '/index.php');
