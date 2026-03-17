<?php
// logout.php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user_id'])) {
    $db = new Database();
    $conn = $db->getConnection();
    logAction($conn, $_SESSION['user_id'], 'Déconnexion', 'Déconnexion du système');
}

session_unset();
session_destroy();
header("Location: /GestionUniversite/index.php");
exit();
?>
