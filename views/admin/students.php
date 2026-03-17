<?php
// views/admin/students.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Function to generate unique student matricule
function generateMatricule($conn) {
    $year = date('y');
    do {
        $rand = rand(1000, 9999);
        $mat = 'ET' . $year . $rand;
        // Check if exists
        $stmt = $conn->prepare("SELECT id FROM students WHERE matricule = ?");
        $stmt->execute([$mat]);
    } while ($stmt->rowCount() > 0);
    return $mat;
}

// Handle Student creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    if (verifyCsrfToken($_POST['csrf_token'])) {
        
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $dob = $_POST['date_of_birth'];
        $sector_id = (int)$_POST['sector_id'];
        
        // Auto matricule & username
        $matricule = generateMatricule($conn);
        $username = strtolower($matricule); // Username is the matricule
        
        // Password is DOB for students (DDMMYYYY)
        $dob_parts = explode('-', $dob);
        $password = $dob_parts[2] . $dob_parts[1] . $dob_parts[0];
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $conn->beginTransaction();
            
            // Create user for student
            $stmt_u = $conn->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'student')");
            $stmt_u->execute([$username, $email, $hashed]);
            $user_id = $conn->lastInsertId();
            
            // Create student record
            $stmt_s = $conn->prepare("INSERT INTO students (user_id, sector_id, matricule, first_name, last_name, date_of_birth, enrollment_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt_s->execute([$user_id, $sector_id, $matricule, $first_name, $last_name, $dob]);
            
            $conn->commit();
            
            logAction($conn, $_SESSION['user_id'], 'Inscription Étudiant', "L'étudiant $first_name $last_name a été inscrit avec matricule $matricule.");
            setFlashMessage('success', "Étudiant inscrit avec succès. Matricule : <strong>$matricule</strong> (Sert d'identifiant, et la date de naissance DDMMYYYY comme mot de passe par défaut)");
        } catch (PDOException $e) {
            $conn->rollBack();
            setFlashMessage('error', 'Erreur lors de l\'enregistrement de l\'étudiant. Email déjà pris ?');
        }
    }
    redirect('/GestionUniversite/views/admin/students.php');
}

// Searching and filtering could be added here
// Fetch Students
$students = $conn->query("
    SELECT s.*, sec.name as section_name, sec.level, u.email, u.is_active
    FROM students s
    JOIN users u ON s.user_id = u.id
    JOIN sectors sec ON s.sector_id = sec.id
    ORDER BY s.id DESC
")->fetchAll();

// Fetch sectors for form
$sectors = $conn->query("SELECT s.id, s.name, s.level, d.code as dept_code FROM sectors s JOIN departments d ON s.department_id = d.id ORDER BY d.code, s.level")->fetchAll();

?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Dossiers Étudiants</h3>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('addStudentModal').style.display='block'">
            <i class="fas fa-user-plus"></i> Inscrire un Étudiant
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table" style="width:100%; text-align:left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border);">
                    <th style="padding: 12px;">Matricule</th>
                    <th style="padding: 12px;">Nom Complet</th>
                    <th style="padding: 12px;">Filière</th>
                    <th style="padding: 12px;">Contact</th>
                    <th style="padding: 12px;">Date Inscription</th>
                    <th style="padding: 12px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($students)): ?>
                    <tr><td colspan="6" style="text-align:center; padding: 20px;">Aucun étudiant enregistré.</td></tr>
                <?php else: ?>
                    <?php foreach($students as $st): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px; font-weight:600; color:var(--primary-color);">
                            <?php echo escape($st['matricule']); ?>
                        </td>
                        <td style="padding: 12px; font-weight:500;">
                            <?php echo escape($st['last_name'] . ' ' . $st['first_name']); ?>
                        </td>
                        <td style="padding: 12px;">
                            <?php echo escape($st['section_name'] . ' [' . $st['level'] . ']'); ?>
                        </td>
                        <td style="padding: 12px; font-size: 0.9rem;">
                            <?php echo escape($st['email']); ?>
                        </td>
                        <td style="padding: 12px; font-size: 0.9rem; color:var(--text-muted);">
                            <?php echo formatDate($st['enrollment_date']); ?>
                        </td>
                        <td style="padding: 12px;">
                            <a href="#" class="btn btn-sm" style="background:#e2e8f0; color:#0f172a;" title="Générer Carte d'Étudiant (PDF)">
                                <i class="fas fa-id-card"></i>
                            </a>
                            <!-- Advanced Profile View Button could be here -->
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
.modal-content { background-color: var(--surface); margin: 5% auto; padding: 2rem; border-radius: var(--radius-lg); width: 100%; max-width: 600px; box-shadow: var(--shadow-md); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.close { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
.close:hover { color: #000; }
</style>

<!-- Add Student Modal -->
<div id="addStudentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Inscription Nouvel Étudiant</h3>
            <span class="close" onclick="document.getElementById('addStudentModal').style.display='none'">&times;</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="add_student" value="1">
            
            <div style="display:flex; gap: 1rem;">
                <div class="form-group" style="flex:1;">
                    <label>Prénom</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Nom</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
            </div>
            
            <div style="display:flex; gap: 1rem;">
                <div class="form-group" style="flex:1;">
                    <label>Email Personnel</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Date de Naissance</label>
                    <input type="date" name="date_of_birth" class="form-control" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Filière Assignée</label>
                <select name="sector_id" class="form-control" required>
                    <option value="">Sélectionner une filière...</option>
                    <?php foreach($sectors as $sec): ?>
                        <option value="<?php echo $sec['id']; ?>">
                            <?php echo escape($sec['dept_code'] . ' - ' . $sec['name'] . ' [' . $sec['level'] . ']'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;">Inscrire l'étudiant</button>
            <p style="font-size: 0.8rem; color: var(--text-muted); text-align: center; margin-top: 1rem;">Le matricule sera généré automatiquement et servira d'identifiant de connexion.</p>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
