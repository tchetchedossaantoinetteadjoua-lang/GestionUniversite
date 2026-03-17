<?php
// views/admin/teachers.php
require_once __DIR__ . '/../../includes/header.php';
requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Handle Teacher creation and Course assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'])) {
        
        // Add new teacher
        if (isset($_POST['add_teacher'])) {
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $specialty = trim($_POST['specialty']);
            
            // Password logic for newly created teachers (simple random string for demo)
            $password = bin2hex(random_bytes(4)); // 8-char password
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $username = strtolower(substr($first_name, 0, 1) . $last_name . rand(10,99));
            
            try {
                $conn->beginTransaction();
                
                // Create user
                $stmt_u = $conn->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'teacher')");
                $stmt_u->execute([$username, $email, $hashed]);
                $user_id = $conn->lastInsertId();
                
                // Create teacher profile
                $stmt_t = $conn->prepare("INSERT INTO teachers (user_id, first_name, last_name, phone, specialty) VALUES (?, ?, ?, ?, ?)");
                $stmt_t->execute([$user_id, $first_name, $last_name, $phone, $specialty]);
                
                $conn->commit();
                
                logAction($conn, $_SESSION['user_id'], 'Création Enseignant', "L'enseignant $first_name $last_name ($username) a été créé.");
                // For a real app, send email with password. Here we display it in a flash message.
                $msg = "Enseignant $first_name $last_name ajouté avec succès. Identifiant : <strong>$username</strong> - Mot de passe : <strong>$password</strong>";
                setFlashMessage('success', $msg);
            } catch (PDOException $e) {
                $conn->rollBack();
                setFlashMessage('error', 'Erreur lors de la création de l\'enseignant. Email existant ?');
            }
        }
        
        // Assign course to teacher
        if (isset($_POST['assign_course'])) {
            $teacher_id = (int)$_POST['teacher_id'];
            $course_id = (int)$_POST['course_id'];
            
            try {
                $stmt = $conn->prepare("INSERT INTO course_teacher (course_id, teacher_id) VALUES (?, ?)");
                $stmt->execute([$course_id, $teacher_id]);
                logAction($conn, $_SESSION['user_id'], 'Attribution UE', "UE $course_id attribuée à enseignant $teacher_id.");
                setFlashMessage('success', 'Matière attribuée à l\'enseignant.');
            } catch (PDOException $e) {
                setFlashMessage('warning', 'Cette matière est déjà attribuée à cet enseignant.');
            }
        }
    }
    redirect('/GestionUniversite/views/admin/teachers.php');
}

// Fetch Teachers
$teachers = $conn->query("
    SELECT t.*, u.email, u.username, u.is_active 
    FROM teachers t 
    JOIN users u ON t.user_id = u.id 
    ORDER BY t.last_name ASC
")->fetchAll();

// Fetch all courses for assignment modal
$courses = $conn->query("SELECT id, code, name FROM courses ORDER BY code")->fetchAll();

?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Gestion du Corps Enseignant</h3>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('addTeacherModal').style.display='block'">
            <i class="fas fa-plus"></i> Nouvel Enseignant
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table" style="width:100%; text-align:left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border);">
                    <th style="padding: 12px;">Identifiant</th>
                    <th style="padding: 12px;">Nom & Prénom</th>
                    <th style="padding: 12px;">Spécialité</th>
                    <th style="padding: 12px;">Contact</th>
                    <th style="padding: 12px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($teachers)): ?>
                    <tr><td colspan="5" style="text-align:center; padding: 20px;">Aucun enseignant enregistré.</td></tr>
                <?php else: ?>
                    <?php foreach($teachers as $t): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px; font-weight:600;"><?php echo escape($t['username']); ?></td>
                        <td style="padding: 12px; font-weight:500;">
                            <?php echo escape($t['last_name'] . ' ' . $t['first_name']); ?>
                        </td>
                        <td style="padding: 12px;">
                            <span style="background:var(--background); padding: 4px 8px; border-radius:4px; font-size: 0.85rem;">
                                <?php echo escape($t['specialty']); ?>
                            </span>
                        </td>
                        <td style="padding: 12px; font-size: 0.9rem;">
                            <i class="fas fa-envelope"></i> <?php echo escape($t['email']); ?><br>
                            <i class="fas fa-phone"></i> <?php echo escape($t['phone'] ?? 'N/A'); ?>
                        </td>
                        <td style="padding: 12px;">
                            <button class="btn btn-sm" style="background:#8b5cf6; color:white;" title="Attribuer une matière" onclick="openAssignModal(<?php echo $t['id']; ?>, '<?php echo escape($t['last_name'] . ' ' . $t['first_name']); ?>')">
                                <i class="fas fa-book"></i> Attribuer UE
                            </button>
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

<!-- Add Teacher Modal -->
<div id="addTeacherModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nouveau Profil Enseignant</h3>
            <span class="close" onclick="document.getElementById('addTeacherModal').style.display='none'">&times;</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="add_teacher" value="1">
            
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
            
            <div class="form-group">
                <label>Email Professionnel (Servira de login si identifiant oublié)</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            
            <div style="display:flex; gap: 1rem;">
                <div class="form-group" style="flex:1;">
                    <label>Téléphone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Domaine de Spécialité</label>
                    <input type="text" name="specialty" class="form-control" required placeholder="Ex: Algorithmique, Bases de données...">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;">Créer le profil Enseignant</button>
            <p style="font-size: 0.8rem; color: var(--text-muted); text-align: center; margin-top: 1rem;">Le mot de passe sera généré automatiquement.</p>
        </form>
    </div>
</div>

<!-- Assign Course Modal -->
<div id="assignCourseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Attribuer une UE à <span id="assignTeacherName" style="color:var(--primary-color);"></span></h3>
            <span class="close" onclick="document.getElementById('assignCourseModal').style.display='none'">&times;</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="assign_course" value="1">
            <input type="hidden" name="teacher_id" id="assignTeacherId" value="">
            
            <div class="form-group">
                <label>Unité d'Enseignement</label>
                <select name="course_id" class="form-control" required>
                    <option value="">Sélectionner une UE...</option>
                    <?php foreach($courses as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo escape($c['code'] . ' - ' . $c['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary" style="background:#8b5cf6; border-color:#8b5cf6;">Assigner l'UE</button>
        </form>
    </div>
</div>

<script>
function openAssignModal(id, name) {
    document.getElementById('assignTeacherId').value = id;
    document.getElementById('assignTeacherName').textContent = name;
    document.getElementById('assignCourseModal').style.display = 'block';
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
