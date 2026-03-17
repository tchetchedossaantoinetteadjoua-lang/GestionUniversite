<?php
// views/student/print_bulletin.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    die("Accès refusé.");
}

$sem_name = isset($_GET['semester']) ? $_GET['semester'] : '';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("
    SELECT s.*, sec.name as section_name, sec.level, d.name as dept_name, f.name as fac_name 
    FROM students s
    JOIN sectors sec ON s.sector_id = sec.id
    JOIN departments d ON sec.department_id = d.id
    JOIN faculties f ON d.faculty_id = f.id
    WHERE s.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

$stmt_g = $conn->prepare("
    SELECT g.score, c.code, c.name, c.credits, c.coefficient
    FROM grades g
    JOIN enrollments e ON g.enrollment_id = e.id
    JOIN courses c ON e.course_id = c.id
    JOIN semesters sem ON c.semester_id = sem.id
    WHERE e.student_id = ? AND sem.name = ?
    ORDER BY c.code ASC
");
$stmt_g->execute([$student['id'], $sem_name]);
$grades = $stmt_g->fetchAll();

$tot_pts = 0; $tot_coef = 0;
foreach($grades as $g) {
    $tot_pts += ($g['score'] * $g['coefficient']);
    $tot_coef += $g['coefficient'];
}
$average = ($tot_coef > 0) ? ($tot_pts / $tot_coef) : 0;
$status = ($average >= 10) ? 'ADMIS' : (($average >= 8) ? 'RATTRAPAGE' : 'AJOURNÉ');

// Quick HTML for print view simulating PDF
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Relevé de Notes - <?php echo escape($student['matricule']); ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 40px; color: #333; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #2563eb; padding-bottom: 20px; }
        .header h1 { margin: 0; color: #1e293b; font-size: 24px; text-transform: uppercase; }
        .header h2 { margin: 5px 0 0; color: #64748b; font-size: 16px; font-weight: normal; }
        .info-box { border: 1px solid #cbd5e1; padding: 15px; border-radius: 8px; margin-bottom: 30px; display: flex; justify-content: space-between; }
        .info-col p { margin: 5px 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: left; font-size: 14px; }
        th { background-color: #f8fafc; color: #1e293b; }
        .footer-res { text-align: right; border: 2px solid #1e293b; padding: 15px; border-radius: 8px; width: 300px; float: right; background: #f8fafc;}
        .footer-res p { margin: 5px 0; font-size: 16px; }
        @media print {
            .btn-print { display: none; }
            body { padding: 0; }
        }
        .btn-print { padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; position: fixed; bottom: 20px; right: 20px; }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">Imprimer en PDF</button>

    <div class="header">
        <h1>RELEVÉ OFFICIEL DE NOTES</h1>
        <h2><?php echo escape($student['fac_name']); ?> - <?php echo escape($student['dept_name']); ?></h2>
    </div>

    <div class="info-box">
        <div class="info-col">
            <p><strong>Nom :</strong> <?php echo escape($student['last_name']); ?></p>
            <p><strong>Prénom :</strong> <?php echo escape($student['first_name']); ?></p>
            <p><strong>Matricule :</strong> <?php echo escape($student['matricule']); ?></p>
        </div>
        <div class="info-col">
            <p><strong>Filière :</strong> <?php echo escape($student['section_name']); ?></p>
            <p><strong>Niveau :</strong> <?php echo escape($student['level']); ?></p>
            <p><strong>Période :</strong> <?php echo escape($sem_name); ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Code UE</th>
                <th>Intitulé de l'Unité d'Enseignement</th>
                <th>Crédits</th>
                <th>Coef.</th>
                <th>Note / 20</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($grades as $g): ?>
            <tr>
                <td><strong><?php echo escape($g['code']); ?></strong></td>
                <td><?php echo escape($g['name']); ?></td>
                <td><?php echo escape($g['credits']); ?></td>
                <td><?php echo escape($g['coefficient']); ?></td>
                <td><strong><?php echo number_format($g['score'], 2, ',', ' '); ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer-res">
        <p><strong>Moyenne du semestre :</strong> <?php echo number_format($average, 2, ',', ' '); ?> / 20</p>
        <p><strong>Décision :</strong> <span style="font-weight:bold; color: <?php echo $status=='ADMIS'?'#10b981':($status=='RATTRAPAGE'?'#f59e0b':'#ef4444'); ?>"><?php echo $status; ?></span></p>
    </div>
    
    <div style="clear:both;"></div>
    
    <div style="margin-top: 50px; text-align: right;">
        <p><em>Fait le <?php echo date('d/m/Y'); ?><br>Le Service de la Scolarité</em></p>
    </div>
</body>
</html>
