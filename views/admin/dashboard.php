<?php
// views/admin/dashboard.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Fetch statistics
$stats = [
    'students' => $conn->query("SELECT COUNT(*) FROM students")->fetchColumn(),
    'teachers' => $conn->query("SELECT COUNT(*) FROM teachers")->fetchColumn(),
    'courses' => $conn->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
    'sectors' => $conn->query("SELECT COUNT(*) FROM sectors")->fetchColumn()
];

// Fetch recent students
$recent_students = $conn->query("SELECT s.matricule, s.first_name, s.last_name, sec.name as sector_name 
                                 FROM students s 
                                 JOIN sectors sec ON s.sector_id = sec.id 
                                 ORDER BY s.id DESC LIMIT 5")->fetchAll();

// Fetch sector statistics for Chart
$sector_stats = $conn->query("SELECT sec.name, COUNT(s.id) as count 
                              FROM sectors sec 
                              LEFT JOIN students s ON sec.id = s.sector_id 
                              GROUP BY sec.id")->fetchAll();

$chart_labels = [];
$chart_data = [];
foreach ($sector_stats as $ss) {
    // Escape for JS
    $chart_labels[] = escape($ss['name']);
    $chart_data[] = (int)$ss['count'];
}
?>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stat-info">
            <p>Total Étudiants</p>
            <h3><?php echo number_format($stats['students']); ?></h3>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-info">
            <p>Total Enseignants</p>
            <h3><?php echo number_format($stats['teachers']); ?></h3>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-info">
            <p>Unités d'Enseignement</p>
            <h3><?php echo number_format($stats['courses']); ?></h3>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-info">
            <p>Filières</p>
            <h3><?php echo number_format($stats['sectors']); ?></h3>
        </div>
    </div>
</div>

<div class="row">
    <div class="col card">
        <div class="card-header">
            <h3 class="card-title">Répartition des Étudiants par Filière</h3>
        </div>
        <div style="height: 300px;">
            <canvas id="studentChart"></canvas>
        </div>
    </div>
    
    <div class="col card">
        <div class="card-header">
            <h3 class="card-title">Nouveaux Inscrits</h3>
            <a href="/GestionUniversite/views/admin/students.php" class="btn btn-sm" style="background:var(--background)"><i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="table-responsive">
            <table class="table" style="width:100%; text-align:left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="padding: 10px;">Matricule</th>
                        <th style="padding: 10px;">Nom Complet</th>
                        <th style="padding: 10px;">Filière</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_students)): ?>
                        <tr><td colspan="3" style="text-align:center; padding: 15px;">Aucun étudiant récent.</td></tr>
                    <?php else: ?>
                        <?php foreach($recent_students as $rs): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 10px; font-weight:500;">
                                <?php echo escape($rs['matricule']); ?>
                            </td>
                            <td style="padding: 10px;">
                                <?php echo escape($rs['first_name'] . ' ' . $rs['last_name']); ?>
                            </td>
                            <td style="padding: 10px; color:var(--text-muted);">
                                <?php echo escape($rs['sector_name']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('studentChart').getContext('2d');
    const labels = <?php echo json_encode($chart_labels); ?>;
    const data = <?php echo json_encode($chart_data); ?>;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6'
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        font: {
                            family: "'Inter', sans-serif"
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
