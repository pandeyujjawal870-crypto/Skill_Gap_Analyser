<?php
session_start();
include 'includes/db.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if($name && $email && $password && $confirm) {
        if($password !== $confirm) {
            $error = "Passwords do not match. Please try again.";
        } elseif(strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            // Check if email exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            
            if($stmt->fetch()) {
                $error = "This email is already registered. Try signing in instead.";
            } else {
                // Store password in plain text (no hashing)
                $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
                $stmt->execute(['name' => $name, 'email' => $email, 'password' => $password]);
                
                $_SESSION['user_id'] = $conn->lastInsertId();
                $_SESSION['user_name'] = $name;
                header("Location: career_goal.php");
                exit;
            }
        }
    } else {
        $error = "Please fill in all fields.";
    }
}

$page_title = "Create Account - SkillGap";
include 'includes/header.php';
?>

<div class="auth-container">
  <div class="card fade-in">
    <h2 style="text-align: center; margin-bottom: 8px; font-weight: var(--font-medium);">Create Account</h2>
    <p style="text-align: center; color: var(--text-secondary); margin-bottom: 32px; font-weight: var(--font-light);">
      Start your career journey today — it's free!
    </p>
    
    <?php if($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" class="input" placeholder="John Doe" required autocomplete="name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
      </div>
      
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" class="input" placeholder="you@example.com" required autocomplete="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
      </div>
      
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="input" placeholder="At least 6 characters" required autocomplete="new-password" minlength="6">
      </div>
      
      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" class="input" placeholder="Re-enter your password" required autocomplete="new-password">
      </div>
      
      <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
        Create Account
      </button>
    </form>
    
    <p style="text-align: center; margin-top: 24px; color: var(--text-secondary); font-size: 14px; font-weight: var(--font-light);">
      Already have an account? <a href="login.php" style="color: var(--accent-1); text-decoration: none; font-weight: var(--font-regular);">Sign in</a>
    </p>
  </div>
  
  <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 32px; text-align: center;">
    <div class="fade-in" style="padding: 16px; background: var(--bg-card); border-radius: var(--radius-md);">
      <div style="font-size: 24px; margin-bottom: 8px;">🎯</div>
      <div style="font-size: 13px; color: var(--text-secondary); font-weight: var(--font-light);">Set career goals</div>
    </div>
    <div class="fade-in" style="padding: 16px; background: var(--bg-card); border-radius: var(--radius-md);">
      <div style="font-size: 24px; margin-bottom: 8px;">📊</div>
      <div style="font-size: 13px; color: var(--text-secondary); font-weight: var(--font-light);">Analyze skill gaps</div>
    </div>
    <div class="fade-in" style="padding: 16px; background: var(--bg-card); border-radius: var(--radius-md);">
      <div style="font-size: 24px; margin-bottom: 8px;">📚</div>
      <div style="font-size: 13px; color: var(--text-secondary); font-weight: var(--font-light);">Get resources</div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
