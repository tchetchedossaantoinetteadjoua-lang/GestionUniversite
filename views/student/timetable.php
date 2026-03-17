<?php
// views/student/timetable.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('student');

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT sector_id FROM students WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$sector_id = $stmt->fetchColumn();

$timetables = $conn->prepare("
    SELECT t.*, c.code, c.name as course_name, tc.last_name, tc.first_name
    FROM timetables t
    JOIN courses c ON t.course_id = c.id
    JOIN teachers tc ON t.teacher_id = tc.id
    WHERE t.sector_id = ?
    ORDER BY FIELD(t.day_of_week, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'), t.start_time
");
$timetables->execute([$sector_id]);
$courses = $timetables->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Mon Emploi du Temps</h3>
    </div>
    
    <div class="table-responsive">
        <table class="table" style="width:100%; text-align:left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border);">
                    <th style="padding: 12px;">Jour</th>
                    <th style="padding: 12px;">Horaire</th>
                    <th style="padding: 12px;">Matière</th>
                    <th style="padding: 12px;">Enseignant</th>
                    <th style="padding: 12px;">Salle</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($courses)): ?>
                    <tr><td colspan="5" style="text-align:center; padding: 20px;">Aucun cours programmé dans votre filière.</td></tr>
                <?php else: ?>
                    <?php foreach($courses as $t): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px; font-weight:600;"><?php echo escape($t['day_of_week']); ?></td>
                        <td style="padding: 12px; color:var(--text-muted);"><?php echo date('H:i', strtotime($t['start_time'])) . ' - ' . date('H:i', strtotime($t['end_time'])); ?></td>
                        <td style="padding: 12px;"><strong><?php echo escape($t['code']); ?></strong><br><small><?php echo escape($t['course_name']); ?></small></td>
                        <td style="padding: 12px;"><?php echo escape($t['last_name']) . ' ' . escape($t['first_name']); ?></td>
                        <td style="padding: 12px; font-weight: 600; color: var(--primary-color);"><?php echo escape($t['room']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
