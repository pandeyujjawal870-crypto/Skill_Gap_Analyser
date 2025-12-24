<?php
session_start();
include 'includes/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get user's career goal
$stmt = $conn->prepare("SELECT career_goal FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();
$career_goal = $user['career_goal'] ?? '';

// Handle quick add from suggested skills
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_add'])) {
    $skill_name = trim($_POST['skill_name']);
    $default_level = 20; // Beginner level
    
    // Check if skill already exists
    $stmt = $conn->prepare("SELECT id FROM user_skills WHERE user_id = :uid AND LOWER(skill_name) = LOWER(:skill)");
    $stmt->execute(['uid' => $user_id, 'skill' => $skill_name]);
    
    if($stmt->rowCount() == 0) {
        $stmt = $conn->prepare("INSERT INTO user_skills (user_id, skill_name, current_level, assessed_via) VALUES (:uid, :skill, :level, 'quick_add')");
        $stmt->execute(['uid' => $user_id, 'skill' => $skill_name, 'level' => $default_level]);
        
        // Log history
        $stmt = $conn->prepare("INSERT INTO skill_history (user_id, skill_name, old_level, new_level) VALUES (:uid, :skill, 0, :level)");
        $stmt->execute(['uid' => $user_id, 'skill' => $skill_name, 'level' => $default_level]);
        
        $message = "Added '$skill_name' as beginner level. Update it manually for accurate assessment!";
    } else {
        $error = "You already have '$skill_name' in your skills.";
    }
}

// Handle adding new skill(s) - supports single or multiple
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $skill_names = isset($_POST['skill_name']) ? (array)$_POST['skill_name'] : [];
    $skill_levels = isset($_POST['skill_level']) ? (array)$_POST['skill_level'] : [];
    
    $added_count = 0;
    $updated_count = 0;
    $skipped = [];
    
    // Process each skill
    for($i = 0; $i < count($skill_names); $i++) {
        $skill_name = trim($skill_names[$i]);
        $skill_level = isset($skill_levels[$i]) ? intval($skill_levels[$i]) : 20;
        
        if(empty($skill_name)) continue; // Skip empty entries
        
        // Check if skill already exists
        $stmt = $conn->prepare("SELECT id, current_level FROM user_skills WHERE user_id = :uid AND LOWER(skill_name) = LOWER(:skill)");
        $stmt->execute(['uid' => $user_id, 'skill' => $skill_name]);
        $existing = $stmt->fetch();
        
        if($existing) {
            // Log history and update
            $stmt = $conn->prepare("INSERT INTO skill_history (user_id, skill_name, old_level, new_level) VALUES (:uid, :skill, :old, :new)");
            $stmt->execute(['uid' => $user_id, 'skill' => $skill_name, 'old' => $existing['current_level'], 'new' => $skill_level]);
            
            $stmt = $conn->prepare("UPDATE user_skills SET current_level = :level, assessed_via = 'self' WHERE id = :id");
            $stmt->execute(['level' => $skill_level, 'id' => $existing['id']]);
            $updated_count++;
        } else {
            $stmt = $conn->prepare("INSERT INTO user_skills (user_id, skill_name, current_level, assessed_via) VALUES (:uid, :skill, :level, 'self')");
            $stmt->execute(['uid' => $user_id, 'skill' => $skill_name, 'level' => $skill_level]);
            
            // Log history
            $stmt = $conn->prepare("INSERT INTO skill_history (user_id, skill_name, old_level, new_level) VALUES (:uid, :skill, 0, :level)");
            $stmt->execute(['uid' => $user_id, 'skill' => $skill_name, 'level' => $skill_level]);
            
            $added_count++;
        }
    }
    
    // Build success message
    if($added_count > 0 || $updated_count > 0) {
        $parts = [];
        if($added_count > 0) $parts[] = "Added $added_count skill(s)";
        if($updated_count > 0) $parts[] = "Updated $updated_count skill(s)";
        $message = implode(' and ', $parts);
    }
}

// Handle updating a skill
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $skill_id = intval($_POST['skill_id']);
    $skill_level = intval($_POST['skill_level']);
    
    // Get old level for history
    $stmt = $conn->prepare("SELECT skill_name, current_level FROM user_skills WHERE id = :id AND user_id = :uid");
    $stmt->execute(['id' => $skill_id, 'uid' => $user_id]);
    $old = $stmt->fetch();
    
    if($old) {
        // Log history
        $stmt = $conn->prepare("INSERT INTO skill_history (user_id, skill_name, old_level, new_level) VALUES (:uid, :skill, :old, :new)");
        $stmt->execute(['uid' => $user_id, 'skill' => $old['skill_name'], 'old' => $old['current_level'], 'new' => $skill_level]);
        
        $stmt = $conn->prepare("UPDATE user_skills SET current_level = :level, assessed_via = 'self' WHERE id = :id AND user_id = :uid");
        $stmt->execute(['level' => $skill_level, 'id' => $skill_id, 'uid' => $user_id]);
        $message = "Skill level updated to $skill_level%";
    }
}

// Handle deleting a skill
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $skill_id = intval($_POST['skill_id']);
    $stmt = $conn->prepare("DELETE FROM user_skills WHERE id = :id AND user_id = :uid");
    $stmt->execute(['id' => $skill_id, 'uid' => $user_id]);
    $message = "Skill removed from your profile";
}

// Get user's skills
$stmt = $conn->prepare("SELECT * FROM user_skills WHERE user_id = :uid ORDER BY current_level DESC");
$stmt->execute(['uid' => $user_id]);
$skills = $stmt->fetchAll();

// Get suggested skills for career goal
$suggested_skills = [];
$user_skill_names = array_map(fn($s) => strtolower($s['skill_name']), $skills);

if($career_goal) {
    $stmt = $conn->prepare("SELECT skill_name, required_level, weight, priority FROM required_skills WHERE career_path = :goal ORDER BY priority");
    $stmt->execute(['goal' => $career_goal]);
    $required = $stmt->fetchAll();
    
    foreach($required as $r) {
        if(!in_array(strtolower($r['skill_name']), $user_skill_names)) {
            $suggested_skills[] = $r;
        }
    }
}


// Level descriptions
$level_descriptions = [
    20 => ['level' => 'Beginner', 'desc' => 'Just starting out'],
    40 => ['level' => 'Elementary', 'desc' => 'Know the basics'],
    60 => ['level' => 'Intermediate', 'desc' => 'Can work independently'],
    80 => ['level' => 'Advanced', 'desc' => 'Highly proficient'],
    100 => ['level' => 'Expert', 'desc' => 'Master level']
];

$page_title = "My Skills - SkillGap";
include 'includes/header.php';
?>

<div class="content-container">
    <div class="page-header fade-in">
        <h1>🛠️ My Skills</h1>
        <p>Add your skills and take assessments to get accurate skill levels</p>
    </div>
    
    <?php if($message): ?>
        <div class="alert alert-success fade-in"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="alert alert-error fade-in"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Add Skill Form -->
    <div class="card fade-in" style="margin-bottom: 32px;">
        <h3 style="margin-bottom: 16px;">➕ Add New Skill</h3>
        <form method="POST" id="skillsForm">
            <input type="hidden" name="action" value="add">
            
            <div id="skillEntries">
                <!-- Single skill entry -->
                <div class="skill-entry" style="display: flex; gap: 12px; margin-bottom: 12px; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group" style="flex: 2; min-width: 200px; margin-bottom: 0;">
                        <label>Skill Name</label>
                        <input type="text" name="skill_name[]" class="input" placeholder="e.g., JavaScript, Python, SQL" required>
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                        <label>Self-Assessment Level</label>
                        <select name="skill_level[]" class="input" required>
                            <?php foreach($level_descriptions as $value => $info): ?>
                                <option value="<?= $value ?>"><?= $value ?>% - <?= $info['level'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 12px; margin-top: 16px;">
                <button type="submit" class="btn btn-primary">Add Skill</button>
            </div>
        </form>
    </div>

    
    <!-- Suggested Skills for Career -->
    <?php if(!empty($suggested_skills)): ?>
    <div class="card fade-in" style="margin-bottom: 32px; background: var(--accent-gradient-soft);">
        <h3 style="margin-bottom: 8px;">🎯 Suggested for <?= htmlspecialchars($career_goal) ?></h3>
        <p style="color: var(--text-muted); margin-bottom: 16px; font-size: 14px;">Click to quick-add these skills, then take a quiz for accurate assessment</p>
        
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            <?php foreach($suggested_skills as $s): ?>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="quick_add" value="1">
                <input type="hidden" name="skill_name" value="<?= htmlspecialchars($s['skill_name']) ?>">
                <button type="submit" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">+</span>
                    <?= htmlspecialchars($s['skill_name']) ?>
                    <?php if($s['weight'] >= 4): ?>
                        <span style="font-size: 10px; background: var(--warning-bg); color: var(--warning); padding: 2px 6px; border-radius: 4px;">Important</span>
                    <?php endif; ?>
                </button>
            </form>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Skill Level Guide -->
    <div class="insight-box fade-in" style="margin-bottom: 32px;">
        <div class="insight-title">
            <span>📊</span>
            <span>Skill Assessment Guide</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 12px;">
            <div style="padding: 12px; background: rgba(255,255,255,0.5); border-radius: var(--radius-sm);">
                <strong style="color: var(--accent-1);">20% - Beginner</strong>
                <p style="font-size: 13px; margin: 4px 0 0;">Just starting out, learning basics</p>
            </div>
            <div style="padding: 12px; background: rgba(255,255,255,0.5); border-radius: var(--radius-sm);">
                <strong style="color: var(--text-secondary);">40% - Elementary</strong>
                <p style="font-size: 13px; margin: 4px 0 0;">Know the basics, need guidance</p>
            </div>
            <div style="padding: 12px; background: rgba(255,255,255,0.5); border-radius: var(--radius-sm);">
                <strong style="color: var(--warning);">60% - Intermediate</strong>
                <p style="font-size: 13px; margin: 4px 0 0;">Can work independently</p>
            </div>
            <div style="padding: 12px; background: rgba(255,255,255,0.5); border-radius: var(--radius-sm);">
                <strong style="color: var(--success);">80%+ - Advanced/Expert</strong>
                <p style="font-size: 13px; margin: 4px 0 0;">Highly proficient, can teach others</p>
            </div>
        </div>
    </div>
    
    <!-- Skills List -->
    <h2 style="margin-bottom: 20px;">Your Skills (<?= count($skills) ?>)</h2>
    
    <?php if(empty($skills)): ?>
    <div class="card empty-state fade-in">
        <div class="empty-icon">🛠️</div>
        <h3>No skills added yet</h3>
        <p>Start by adding your first skill above. Be honest about your proficiency levels for accurate gap analysis.</p>
        <?php if($career_goal && !empty($suggested_skills)): ?>
            <p style="color: var(--accent-1);">💡 Tip: Add skills related to <?= htmlspecialchars($career_goal) ?> using the quick-add buttons above</p>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="skills-grid">
        <?php foreach($skills as $skill): ?>
        <div class="skill-card fade-in">
            <div class="skill-header">
                <span class="skill-name"><?= htmlspecialchars($skill['skill_name']) ?></span>
                <span class="skill-level"><?= $skill['current_level'] ?>%</span>
            </div>
            
            <!-- Assessment method badge -->
            <div style="margin-bottom: 12px;">
                <?php if($skill['assessed_via'] === 'quick_add'): ?>
                    <span class="badge badge-warning" style="font-size: 10px;">⚡ Quick Added</span>
                <?php else: ?>
                    <span class="badge badge-info" style="font-size: 10px;">✍️ Self Reported</span>
                <?php endif; ?>
            </div>
            
            <!-- Level Description -->
            <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">
                <?php 
                $closest_level = 20;
                foreach([20, 40, 60, 80, 100] as $l) {
                    if($skill['current_level'] >= $l) $closest_level = $l;
                }
                echo $level_descriptions[$closest_level]['level'] . ' - ' . $level_descriptions[$closest_level]['desc'];
                ?>
            </div>
            
            <div class="progress-bar" style="margin-bottom: 16px;">
                <div class="progress-fill" style="width: <?= $skill['current_level'] ?>%"></div>
            </div>
            
            <!-- Actions -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; gap: 8px;">
                    <form method="POST" style="flex: 1; display: flex; gap: 6px;">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="skill_id" value="<?= $skill['id'] ?>">
                        <select name="skill_level" class="input" style="padding: 8px; font-size: 13px;">
                            <?php foreach($level_descriptions as $value => $info): ?>
                                <option value="<?= $value ?>" <?= $skill['current_level'] == $value ? 'selected' : '' ?>><?= $value ?>%</option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-secondary btn-sm">Update</button>
                    </form>
                    
                        <button type="button" class="btn btn-danger btn-sm delete-skill-btn" 
                                data-skill-id="<?= $skill['id'] ?>"
                                title="Remove">×</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Navigation -->
    <div style="text-align: center; margin-top: 40px;">
        <a href="roadmap.php" class="btn btn-primary">View My Roadmap →</a>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
  <div class="card fade-in" style="background: white; max-width: 400px; width: 90%; padding: 24px; text-align: center; box-shadow: var(--shadow-xl);">
    <div style="font-size: 48px; margin-bottom: 16px;">🗑️</div>
    <h3 style="margin-bottom: 12px; color: var(--text-dark);">Remove Skill?</h3>
    <p style="color: var(--text-muted); margin-bottom: 24px;">Are you sure you want to remove this skill from your profile?</p>
    <div style="display: flex; gap: 12px; justify-content: center;">
      <button onclick="closeDeleteModal()" class="btn" style="background: var(--bg-light); color: var(--text-dark);">Cancel</button>
      <button id="confirmDeleteBtn" class="btn btn-danger">Yes, Remove</button>
    </div>
  </div>
</div>

<script>
// Delete Modal Logic
let skillIdToDelete = null;
const deleteModal = document.getElementById('deleteModal');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

function openDeleteModal(skillId) {
    skillIdToDelete = skillId;
    deleteModal.style.display = 'flex';
}

function closeDeleteModal() {
    skillIdToDelete = null;
    deleteModal.style.display = 'none';
}

// Handle delete buttons
document.querySelectorAll('.delete-skill-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        openDeleteModal(this.dataset.skillId);
    });
});

// Handle Confirm Delete
confirmDeleteBtn.addEventListener('click', function() {
    if(skillIdToDelete) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'skill_id';
        idInput.value = skillIdToDelete;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
});

// Close modal when clicking outside
deleteModal.addEventListener('click', function(e) {
    if(e.target === deleteModal) {
        closeDeleteModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
