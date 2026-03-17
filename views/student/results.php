<?php
// views/student/results.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('student');

$db = new Database();
$conn = $db->getConnection();

// Get student ID
$stmt_s = $conn->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt_s->execute([$_SESSION['user_id']]);
$student_id = $stmt_s->fetchColumn();

// Fetch grades with coefficients
$query = "
    SELECT g.score, g.evaluation_type, c.code, c.name, c.credits, c.coefficient, sem.name as sem_name
    FROM grades g
    JOIN enrollments e ON g.enrollment_id = e.id
    JOIN courses c ON e.course_id = c.id
    JOIN semesters sem ON c.semester_id = sem.id
    WHERE e.student_id = ?
    ORDER BY sem.academic_year DESC, sem.id ASC, c.code ASC
";
$stmt_g = $conn->prepare($query);
$stmt_g->execute([$student_id]);
$grades = $stmt_g->fetchAll();

// Calculate Average
$total_points = 0;
$total_coeffs = 0;
$results_by_semester = [];

foreach ($grades as $g) {
    if (!isset($results_by_semester[$g['sem_name']])) {
        $results_by_semester[$g['sem_name']] = [
            'grades' => [],
            'tot_points' => 0,
            'tot_coeffs' => 0
        ];
    }
    
    $results_by_semester[$g['sem_name']]['grades'][] = $g;
    $results_by_semester[$g['sem_name']]['tot_points'] += ($g['score'] * $g['coefficient']);
    $results_by_semester[$g['sem_name']]['tot_coeffs'] += $g['coefficient'];
    
    $total_points += ($g['score'] * $g['coefficient']);
    $total_coeffs += $g['coefficient'];
}

$general_average = ($total_coeffs > 0) ? ($total_points / $total_coeffs) : null;

function getResultString($average) {
    if ($average === null) return ['N/A', 'secondary-color'];
    if ($average >= 10) return ['Admis', 'success'];
    if ($average >= 8) return ['Rattrapage', 'warning'];
    return ['Ajourné', 'danger'];
}

?>

<div class="card" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h3 class="card-title" style="margin-bottom:0.5rem;">Mes Résultats Académiques</h3>
        <p style="color:var(--text-muted); font-size: 0.9rem;">Consultez vos notes et moyennes par semestre.</p>
    </div>
    
    <?php if ($general_average !== null): ?>
    <div style="text-align: right;">
        <p style="font-size: 0.9rem; margin-bottom: 0.2rem;">Moyenne Générale</p>
        <h2 style="font-size: 2rem; color: <?php echo $general_average >= 10 ? 'var(--success)' : 'var(--danger)'; ?>">
            <?php echo number_format($general_average, 2, ',', ' '); ?> / 20
        </h2>
        <?php $res = getResultString($general_average); ?>
        <span style="background: var(--<?php echo $res[1]; ?>); color: white; padding: 4px 12px; border-radius: 999px; font-weight: 600; font-size: 0.85rem;">
            <?php echo $res[0]; ?>
        </span>
    </div>
    <?php endif; ?>
</div>

<?php if(empty($results_by_semester)): ?>
    <div class="card" style="text-align:center; padding: 3rem;">
        <i class="fas fa-folder-open" style="font-size: 3rem; color: var(--border); margin-bottom: 1rem;"></i>
        <h3 style="color:var(--text-muted);">Aucun résultat disponible pour le moment.</h3>
    </div>
<?php else: ?>
    <?php foreach($results_by_semester as $sem_name => $data): ?>
        <?php 
            $sem_avg = ($data['tot_coeffs'] > 0) ? ($data['tot_points'] / $data['tot_coeffs']) : null;
            $sem_res = getResultString($sem_avg);
        ?>
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header" style="background:#f8fafc; margin:-1.5rem -1.5rem 1.5rem -1.5rem; padding: 1.5rem; border-radius: var(--radius-lg) var(--radius-lg) 0 0; display:flex; justify-content:space-between; align-items:center;">
                <h3 class="card-title" style="margin:0;"><?php echo escape($sem_name); ?></h3>
                <div style="text-align:right;">
                    <span style="font-weight:700; font-size:1.2rem; margin-right: 15px;">Moyenne: <?php echo number_format($sem_avg, 2, ',', ' '); ?></span>
                    <span style="background: var(--<?php echo $sem_res[1]; ?>); color: white; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">
                        <?php echo $sem_res[0]; ?>
                    </span>
                </div>
            </div>
            
            <table class="table" style="width:100%; text-align:left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="padding: 10px;">Code</th>
                        <th style="padding: 10px;">Unité d'Enseignement</th>
                        <th style="padding: 10px;">Coefficient</th>
                        <th style="padding: 10px;">Note / 20</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data['grades'] as $g): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 10px; font-weight:600; color:var(--text-muted);"><?php echo escape($g['code']); ?></td>
                            <td style="padding: 10px;"><?php echo escape($g['name']); ?> <small>(<?php echo escape($g['evaluation_type']); ?>)</small></td>
                            <td style="padding: 10px;"><?php echo escape($g['coefficient']); ?></td>
                            <td style="padding: 10px; font-weight: 600; color: <?php echo $g['score'] >= 10 ? 'var(--success)' : 'var(--danger)'; ?>;">
                                <?php echo number_format($g['score'], 2, ',', ' '); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 1rem; text-align: right;">
                <a href="/GestionUniversite/views/student/print_bulletin.php?semester=<?php echo urlencode($sem_name); ?>" target="_blank" class="btn btn-sm" style="background:#475569; color:white; text-decoration:none;">
                    <i class="fas fa-print"></i> Imprimer le Relevé (PDF)
                </a>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
