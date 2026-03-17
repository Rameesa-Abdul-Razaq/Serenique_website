<?php
/**
 * Logout Handler
 */
require_once __DIR__ . '/session.php';

logoutUser();
setFlash('success', 'You have been logged out successfully.');
redirect('login.php');
?>
