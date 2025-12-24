<?php
session_start();
include 'includes/db.php';

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if($email && $password) {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        // Compare passwords in plain text (no hashing)
        if($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password. Please try again.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}

$page_title = "Login - SkillGap";
include 'includes/header.php';
?>

<div class="auth-container">
  <div class="card fade-in">
    <h2 style="text-align: center; margin-bottom: 8px; font-weight: var(--font-medium);">Welcome Back</h2>
    <p style="text-align: center; color: var(--text-secondary); margin-bottom: 32px; font-weight: var(--font-light);">
      Sign in to continue your career journey
    </p>
    
    <?php if($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" class="input" placeholder="you@example.com" required autocomplete="email">
      </div>
      
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="input" placeholder="••••••••" required autocomplete="current-password">
      </div>
      
      <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
        Sign In
      </button>
    </form>
    
    <p style="text-align: center; margin-top: 24px; color: var(--text-secondary); font-size: 14px; font-weight: var(--font-light);">
      Don't have an account? <a href="register.php" style="color: var(--accent-1); text-decoration: none; font-weight: var(--font-regular);">Create one</a>
    </p>
  </div>
  
  <div class="insight-box fade-in" style="margin-top: 24px;">
    <div class="insight-title">
      <span>💡</span>
      <span>New here?</span>
    </div>
    <p style="font-size: 14px;">
      Create a free account to analyze your skills, discover gaps, and get personalized recommendations for your dream career.
    </p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
