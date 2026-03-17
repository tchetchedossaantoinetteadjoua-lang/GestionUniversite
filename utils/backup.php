<?php
// utils/backup.php
// This script can be run via Cron Job or manually from Admin Settings

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

$sqlScript = "-- Sauvegarde Base de Données GestionUniversite\n";
$sqlScript .= "-- Généré le : " . date('Y-m-d H:i:s') . "\n\n";

foreach ($tables as $table) {
    // Schema
    $query = "SHOW CREATE TABLE $table";
    $stmt = $conn->query($query);
    $row = $stmt->fetch(PDO::FETCH_NUM);
    $sqlScript .= "\n\n" . $row[1] . ";\n\n";
    
    // Data
    $query = "SELECT * FROM $table";
    $stmt = $conn->query($query);
    $columnCount = $stmt->columnCount();
    
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $sqlScript .= "INSERT INTO $table VALUES(";
        for ($j = 0; $j < $columnCount; $j ++) {
            $row[$j] = $row[$j];
            if (isset($row[$j])) {
                $sqlScript .= '"' . addslashes($row[$j]) . '"';
            } else {
                $sqlScript .= '""';
            }
            if ($j < ($columnCount - 1)) {
                $sqlScript .= ',';
            }
        }
        $sqlScript .= ");\n";
    }
}

// Generate File
$backup_file_name = __DIR__ . '/../backups/backup_' . date('Y-m-d-H-i-s') . '.sql';

// Ensure backup folder exists
if (!is_dir(__DIR__ . '/../backups')) {
    mkdir(__DIR__ . '/../backups', 0777, true);
}

file_put_contents($backup_file_name, $sqlScript);

// If called via web (Settings page), prompt download
if (php_sapi_name() != "cli") {
    // Optionally log the backup
    session_start();
    require_once __DIR__ . '/../includes/functions.php';
    if(isset($_SESSION['user_id'])) {
        logAction($conn, $_SESSION['user_id'], 'Sauvegarde DB', "Une sauvegarde SQL a été générée.");
    }
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($backup_file_name));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($backup_file_name));
    readfile($backup_file_name);
    exit;
} else {
    echo "Backup completed: " . $backup_file_name . "\n";
}
?>
