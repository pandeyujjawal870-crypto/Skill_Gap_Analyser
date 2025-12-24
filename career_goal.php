<?php
session_start();
include 'includes/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// Handle adding new career goals
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $selected_goals = isset($_POST['career_goals']) ? $_POST['career_goals'] : [];
    
    $added = 0;
    foreach($selected_goals as $goal) {
        $goal = trim($goal);
        if(empty($goal)) continue;
        
        try {
            $stmt = $conn->prepare("INSERT IGNORE INTO user_career_goals (user_id, career_goal) VALUES (:uid, :goal)");
            $stmt->execute(['uid' => $user_id, 'goal' => $goal]);
            if($stmt->rowCount() > 0) $added++;
        } catch(PDOException $e) {
            // Skip duplicates
        }
    }
    
    if($added > 0) {
        $message = "Added $added career goal(s) successfully!";
        
        // Update primary goal in users table (first selected goal)
        if(!empty($selected_goals)) {
            $stmt = $conn->prepare("UPDATE users SET career_goal = :goal WHERE id = :id");
            $stmt->execute(['goal' => $selected_goals[0], 'id' => $user_id]);
        }
    }
}

// Handle deleting a career goal
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $goal_id = intval($_POST['goal_id']);
    
    // Check if user has more than one goal (prevent deleting all)
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM user_career_goals WHERE user_id = :uid");
    $count_stmt->execute(['uid' => $user_id]);
    $count = $count_stmt->fetchColumn();
    
    if($count > 1) {
        // Get the goal name before deleting
        $stmt = $conn->prepare("SELECT career_goal FROM user_career_goals WHERE id = :id");
        $stmt->execute(['id' => $goal_id]);
        $deleted_goal_name = $stmt->fetchColumn();

        $stmt = $conn->prepare("DELETE FROM user_career_goals WHERE id = :id AND user_id = :uid");
        $stmt->execute(['id' => $goal_id, 'uid' => $user_id]);
        $message = "Career goal removed successfully!";
        
        // Sync with users table (if we deleted the primary goal)
        $stmt = $conn->prepare("SELECT career_goal FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $current_primary = $stmt->fetchColumn();
        
        if($deleted_goal_name === $current_primary) {
            // Pick another goal to be primary
            $stmt = $conn->prepare("SELECT career_goal FROM user_career_goals WHERE user_id = :uid LIMIT 1");
            $stmt->execute(['uid' => $user_id]);
            $next_goal = $stmt->fetchColumn();
            
            if($next_goal) {
                $stmt = $conn->prepare("UPDATE users SET career_goal = :goal WHERE id = :uid");
                $stmt->execute(['goal' => $next_goal, 'uid' => $user_id]);
            }
        }
    } else {
        $message = "You must have at least one career goal!";
    }
}

// Get user's current career goals
$stmt = $conn->prepare("SELECT * FROM user_career_goals WHERE user_id = :id ORDER BY added_at DESC");
$stmt->execute(['id' => $user_id]);
$current_goals = $stmt->fetchAll();

// Get all career paths with skill counts
$paths = $conn->query("SELECT * FROM career_paths ORDER BY name")->fetchAll();

// Get skill counts for each path
function getSkillCount($conn, $career_name) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM required_skills WHERE career_path = :path");
    $stmt->execute(['path' => $career_name]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

// Check if a goal is already selected
function isSelected($current_goals, $goal_name) {
    foreach($current_goals as $goal) {
        if($goal['career_goal'] === $goal_name) return true;
    }
    return false;
}

$page_title = "Choose Career Goals - SkillGap";
include 'includes/header.php';
?>

<div class="content-container">
  <div class="page-header">
    <h1>Choose Your Career Goals</h1>
    <p style="font-weight: var(--font-light);">Select multiple career paths you want to pursue. This helps us identify all the skills you need.</p>
  </div>
  
  <?php if($message): ?>
  <div class="alert alert-success fade-in" style="margin-bottom: 24px;">
    <?= htmlspecialchars($message) ?>
  </div>
  <?php endif; ?>
  
  <!-- Current Selected Goals -->
  <?php if(!empty($current_goals)): ?>
  <div class="card fade-in" style="margin-bottom: 32px; background: var(--accent-gradient-soft);">
    <h3 style="margin-bottom: 16px;">✅ Your Selected Goals (<?= count($current_goals) ?>)</h3>
    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
      <?php foreach($current_goals as $goal): ?>
      <div style="display: flex; align-items: center; gap: 8px; background: white; padding: 10px 16px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
        <span style="font-weight: var(--font-semibold);"><?= htmlspecialchars($goal['career_goal']) ?></span>
        <button type="button" class="btn btn-danger btn-sm delete-goal-btn" 
                data-goal-id="<?= $goal['id'] ?>"
                style="padding: 4px 10px; font-size: 12px;" 
                title="Remove">×</button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
  
  <!-- Add More Goals -->
  <div class="card fade-in" style="margin-bottom: 32px;">
    <h3 style="margin-bottom: 16px;">➕ Add More Career Goals</h3>
    <p style="color: var(--text-muted); margin-bottom: 20px; font-size: 14px;">
      Select one or more career paths to add to your goals. You can track skills for multiple careers simultaneously.
    </p>
    
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="career-grid">
        <?php foreach($paths as $path): 
          $skill_count = getSkillCount($conn, $path['name']);
          $is_selected = isSelected($current_goals, $path['name']);
        ?>
          <label class="career-option <?= $is_selected ? 'disabled' : '' ?>" style="<?= $is_selected ? 'opacity: 0.5; cursor: not-allowed;' : '' ?>">
            <input type="checkbox" name="career_goals[]" value="<?= htmlspecialchars($path['name']) ?>" 
                   <?= $is_selected ? 'disabled' : '' ?> style="display: none;">
            <div class="career-icon"><?= $path['icon'] ?></div>
            <div class="career-name"><?= htmlspecialchars($path['name']) ?></div>
            <?php if($is_selected): ?>
              <div style="margin-top: 8px;">
                <span style="font-size: 11px; background: var(--success-bg); color: var(--success); padding: 4px 8px; border-radius: 4px; font-weight: var(--font-semibold);">✓ Already Selected</span>
              </div>
            <?php endif; ?>
            <p style="font-size: 13px; color: var(--text-muted); margin-top: 8px; font-weight: var(--font-light); line-height: 1.6;">
              <?= htmlspecialchars($path['description']) ?>
            </p>
            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.05);">
              <span style="font-size: 12px; color: var(--accent-1);"><?= $skill_count ?> key skills</span>
            </div>
          </label>
        <?php endforeach; ?>
      </div>
      
      <div style="text-align: center; margin-top: 40px;">
        <button type="submit" class="btn btn-primary btn-lg">
          Add Selected Goals →
        </button>
        <?php if(!empty($current_goals)): ?>
        <p style="margin-top: 16px; font-size: 14px; color: var(--text-muted); font-weight: var(--font-light);">
          Or <a href="skills.php" style="color: var(--accent-1); text-decoration: none;">continue to Skills →</a>
        </p>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
  <div class="card fade-in" style="background: white; max-width: 400px; width: 90%; padding: 24px; text-align: center; box-shadow: var(--shadow-xl);">
    <div style="font-size: 48px; margin-bottom: 16px;">🗑️</div>
    <h3 style="margin-bottom: 12px; color: var(--text-dark);">Remove Career Goal?</h3>
    <p style="color: var(--text-muted); margin-bottom: 24px;">Are you sure you want to remove this career goal? You can always add it back later.</p>
    <div style="display: flex; gap: 12px; justify-content: center;">
      <button onclick="closeDeleteModal()" class="btn" style="background: var(--bg-light); color: var(--text-dark);">Cancel</button>
      <button id="confirmDeleteBtn" class="btn btn-danger">Yes, Remove</button>
    </div>
  </div>
</div>

<script>
// Handle checkbox selection with visual feedback - allows toggle on/off
document.querySelectorAll('.career-option:not(.disabled)').forEach(option => {
  option.addEventListener('click', function(e) {
    // Don't interfere with button clicks (like delete buttons)
    if(e.target.tagName === 'BUTTON' || e.target.closest('button')) {
      return;
    }
    
    // Prevent default label behavior to avoid conflicts
    e.preventDefault();
    
    const checkbox = this.querySelector('input[type="checkbox"]');
    if(checkbox && !checkbox.disabled) {
      // Toggle the checkbox state
      checkbox.checked = !checkbox.checked;
      // Update visual state based on checkbox
      if(checkbox.checked) {
        this.classList.add('selected');
      } else {
        this.classList.remove('selected');
      }
    }
  });
});

// Delete Modal Logic
let goalIdToDelete = null;
const deleteModal = document.getElementById('deleteModal');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

function openDeleteModal(goalId) {
  goalIdToDelete = goalId;
  deleteModal.style.display = 'flex';
}

function closeDeleteModal() {
  goalIdToDelete = null;
  deleteModal.style.display = 'none';
}

// Handle delete buttons
document.querySelectorAll('.delete-goal-btn').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    openDeleteModal(this.dataset.goalId);
  });
});

// Handle Confirm Delete
confirmDeleteBtn.addEventListener('click', function() {
  if(goalIdToDelete) {
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
    idInput.name = 'goal_id';
    idInput.value = goalIdToDelete;
    
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
