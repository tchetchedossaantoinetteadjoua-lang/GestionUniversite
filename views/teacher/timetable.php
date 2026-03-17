<?php
// views/teacher/timetable.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('teacher');

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT id FROM teachers WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$teacher_id = $stmt->fetchColumn();

$timetables = $conn->prepare("
    SELECT t.*, c.code, c.name as course_name, sec.name as sector_name, sec.level
    FROM timetables t
    JOIN courses c ON t.course_id = c.id
    JOIN sectors sec ON t.sector_id = sec.id
    WHERE t.teacher_id = ?
    ORDER BY FIELD(t.day_of_week, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'), t.start_time
");
$timetables->execute([$teacher_id]);
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
                    <th style="padding: 12px;">Filière</th>
                    <th style="padding: 12px;">Salle</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($courses)): ?>
                    <tr><td colspan="5" style="text-align:center; padding: 20px;">Aucun cours programmé.</td></tr>
                <?php else: ?>
                    <?php foreach($courses as $t): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px; font-weight:600;"><?php echo escape($t['day_of_week']); ?></td>
                        <td style="padding: 12px; color:var(--text-muted);"><?php echo date('H:i', strtotime($t['start_time'])) . ' - ' . date('H:i', strtotime($t['end_time'])); ?></td>
                        <td style="padding: 12px;"><strong><?php echo escape($t['code']); ?></strong><br><small><?php echo escape($t['course_name']); ?></small></td>
                        <td style="padding: 12px;"><?php echo escape($t['sector_name']) . ' [' . escape($t['level']) . ']'; ?></td>
                        <td style="padding: 12px; font-weight: 600; color: var(--primary-color);"><?php echo escape($t['room']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
