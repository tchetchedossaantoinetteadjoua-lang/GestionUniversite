<?php
// views/admin/semesters.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Handle Semester CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'])) {
        
        // Add Semester
        if (isset($_POST['add_semester'])) {
            $name = trim($_POST['name']);
            $year = trim($_POST['academic_year']);
            $start = $_POST['start_date'] ?: null;
            $end = $_POST['end_date'] ?: null;
            
            try {
                $stmt = $conn->prepare("INSERT INTO semesters (name, academic_year, start_date, end_date) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $year, $start, $end]);
                logAction($conn, $_SESSION['user_id'], 'Création Semestre', "Semestre $name ($year) ajouté.");
                setFlashMessage('success', 'Semestre ajouté.');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Erreur lors de l\'ajout du semestre.');
            }
        }
        
        // Toggle Active Status
        if (isset($_POST['toggle_active'])) {
            $semester_id = (int)$_POST['semester_id'];
            $new_status = (int)$_POST['status'];
            
            // Optional: If you only want one active semester at a time, uncomment the next line
            // $conn->query("UPDATE semesters SET is_active = 0"); 
            
            $stmt = $conn->prepare("UPDATE semesters SET is_active = ? WHERE id = ?");
            $stmt->execute([$new_status, $semester_id]);
            logAction($conn, $_SESSION['user_id'], 'Statut Semestre', "Statut du semestre $semester_id modifié à $new_status.");
            setFlashMessage('success', 'Statut du semestre mis à jour.');
        }
    }
    redirect('/GestionUniversite/views/admin/semesters.php');
}

// Fetch all semesters
$semesters = $conn->query("SELECT * FROM semesters ORDER BY academic_year DESC, name ASC")->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Gestion des Semestres Académiques</h3>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('addSemModal').style.display='block'">
            <i class="fas fa-plus"></i> Nouveau Semestre
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table" style="width:100%; text-align:left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border);">
                    <th style="padding: 12px;">Année Academique</th>
                    <th style="padding: 12px;">Semestre</th>
                    <th style="padding: 12px;">Période</th>
                    <th style="padding: 12px;">Statut</th>
                    <th style="padding: 12px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($semesters)): ?>
                    <tr><td colspan="5" style="text-align:center; padding: 20px;">Aucun semestre enregistré.</td></tr>
                <?php else: ?>
                    <?php foreach($semesters as $s): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px; font-weight:600;"><?php echo escape($s['academic_year']); ?></td>
                        <td style="padding: 12px;"><?php echo escape($s['name']); ?></td>
                        <td style="padding: 12px; color:var(--text-muted);">
                            <?php echo $s['start_date'] ? formatDate($s['start_date']) : 'N/A'; ?> - 
                            <?php echo $s['end_date'] ? formatDate($s['end_date']) : 'N/A'; ?>
                        </td>
                        <td style="padding: 12px;">
                            <?php if($s['is_active']): ?>
                                <span style="background: var(--success); color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">En cours</span>
                            <?php else: ?>
                                <span style="background: var(--secondary-color); color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Clôturé</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px;">
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                <input type="hidden" name="toggle_active" value="1">
                                <input type="hidden" name="semester_id" value="<?php echo $s['id']; ?>">
                                <input type="hidden" name="status" value="<?php echo $s['is_active'] ? '0' : '1'; ?>">
                                
                                <?php if($s['is_active']): ?>
                                    <button type="submit" class="btn btn-sm" style="background:#eab308; color:white;" title="Clôturer le semestre">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-sm btn-primary" title="Activer le semestre">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
.modal-content { background-color: var(--surface); margin: 10% auto; padding: 2rem; border-radius: var(--radius-lg); width: 100%; max-width: 500px; box-shadow: var(--shadow-md); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.close { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
.close:hover { color: #000; }
</style>

<div id="addSemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ajouter un Semestre</h3>
            <span class="close" onclick="document.getElementById('addSemModal').style.display='none'">&times;</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="add_semester" value="1">
            
            <div class="form-group">
                <label>Nom du Semestre</label>
                <select name="name" class="form-control" required>
                    <option value="Semestre 1 (Automne)">Semestre 1 (Automne)</option>
                    <option value="Semestre 2 (Printemps)">Semestre 2 (Printemps)</option>
                    <option value="Semestre d'été">Semestre d'été</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Année Académique (ex: 2023-2024)</label>
                <input type="text" name="academic_year" class="form-control" required placeholder="YYYY-YYYY">
            </div>
            
            <div style="display:flex; gap: 1rem;">
                <div class="form-group" style="flex:1;">
                    <label>Date de début</label>
                    <input type="date" name="start_date" class="form-control">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Date de fin</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
