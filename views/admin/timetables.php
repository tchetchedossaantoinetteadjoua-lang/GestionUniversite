<?php
// views/admin/timetables.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_timetable'])) {
    if (verifyCsrfToken($_POST['csrf_token'])) {
        $course_id = (int)$_POST['course_id'];
        $teacher_id = (int)$_POST['teacher_id'];
        $sector_id = (int)$_POST['sector_id'];
        $day = $_POST['day_of_week'];
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];
        $room = trim($_POST['room']);
        
        try {
            $stmt = $conn->prepare("INSERT INTO timetables (course_id, teacher_id, sector_id, day_of_week, start_time, end_time, room) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$course_id, $teacher_id, $sector_id, $day, $start, $end, $room]);
            logAction($conn, $_SESSION['user_id'], 'Création Emploi du Temps', "Créneau ajouté le $day pour l'UE $course_id.");
            setFlashMessage('success', 'Créneau ajouté avec succès.');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Erreur lors de l\'ajout du créneau.');
        }
    }
    redirect('/GestionUniversite/views/admin/timetables.php');
}

// Fetch all timetables
$timetables = $conn->query("
    SELECT t.*, c.code, c.name as course_name, tc.first_name, tc.last_name, sec.name as sector_name, sec.level
    FROM timetables t
    JOIN courses c ON t.course_id = c.id
    JOIN teachers tc ON t.teacher_id = tc.id
    JOIN sectors sec ON t.sector_id = sec.id
    ORDER BY FIELD(t.day_of_week, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'), t.start_time
")->fetchAll();

// Lookups for form
$courses = $conn->query("SELECT id, code, name FROM courses ORDER BY code")->fetchAll();
$teachers = $conn->query("SELECT id, first_name, last_name FROM teachers ORDER BY last_name")->fetchAll();
$sectors = $conn->query("SELECT id, name, level FROM sectors ORDER BY name")->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Gestion des Emplois du Temps</h3>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('addModal').style.display='block'">
            <i class="fas fa-plus"></i> Nouveau Créneau
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table" style="width:100%; text-align:left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border);">
                    <th style="padding: 12px;">Jour</th>
                    <th style="padding: 12px;">Horaire</th>
                    <th style="padding: 12px;">UE & Filière</th>
                    <th style="padding: 12px;">Enseignant</th>
                    <th style="padding: 12px;">Salle</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($timetables)): ?>
                    <tr><td colspan="5" style="text-align:center; padding: 20px;">Aucun créneau enregistré.</td></tr>
                <?php else: ?>
                    <?php foreach($timetables as $t): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px; font-weight:600;"><?php echo escape($t['day_of_week']); ?></td>
                        <td style="padding: 12px;"><?php echo date('H:i', strtotime($t['start_time'])) . ' - ' . date('H:i', strtotime($t['end_time'])); ?></td>
                        <td style="padding: 12px;">
                            <strong><?php echo escape($t['code']); ?></strong><br>
                            <small class="text-muted"><?php echo escape($t['sector_name']); ?> <?php echo escape($t['level']); ?></small>
                        </td>
                        <td style="padding: 12px;"><?php echo escape($t['last_name'] . ' ' . $t['first_name']); ?></td>
                        <td style="padding: 12px; font-weight: 600; color: var(--primary-color);"><?php echo escape($t['room']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
.modal-content { background-color: var(--surface); margin: 5% auto; padding: 2rem; border-radius: var(--radius-lg); width: 100%; max-width: 600px; box-shadow: var(--shadow-md); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.close { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
.close:hover { color: #000; }
</style>

<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ajouter un Créneau</h3>
            <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="add_timetable" value="1">
            
            <div class="form-group">
                <label>Unité d'Enseignement</label>
                <select name="course_id" class="form-control" required>
                    <option value="">Sélectionner une UE...</option>
                    <?php foreach($courses as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo escape($c['code'] . ' - ' . $c['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display:flex; gap: 1rem;">
                <div class="form-group" style="flex:1;">
                    <label>Enseignant</label>
                    <select name="teacher_id" class="form-control" required>
                        <option value="">Sélectionner...</option>
                        <?php foreach($teachers as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo escape($t['last_name'] . ' ' . $t['first_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Filière Assignée</label>
                    <select name="sector_id" class="form-control" required>
                        <option value="">Sélectionner...</option>
                        <?php foreach($sectors as $s): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo escape($s['name'] . ' [' . $s['level'] . ']'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div style="display:flex; gap: 1rem;">
                <div class="form-group" style="flex:1;">
                    <label>Jour de la Semaine</label>
                    <select name="day_of_week" class="form-control" required>
                        <option value="Lundi">Lundi</option>
                        <option value="Mardi">Mardi</option>
                        <option value="Mercredi">Mercredi</option>
                        <option value="Jeudi">Jeudi</option>
                        <option value="Vendredi">Vendredi</option>
                        <option value="Samedi">Samedi</option>
                    </select>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Salle</label>
                    <input type="text" name="room" class="form-control" placeholder="ex: Amphi 200" required>
                </div>
            </div>
            
            <div style="display:flex; gap: 1rem;">
                <div class="form-group" style="flex:1;">
                    <label>Heure de Début</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Heure de Fin</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width:100%;">Enregistrer le Créneau</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
