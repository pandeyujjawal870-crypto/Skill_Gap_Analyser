<?php 
session_start();
$page_title = "SkillGap Analyzer - Career Advisor for Students";
include 'includes/header.php'; 
?>

<section class="hero">
  <div class="container">
    <h1 class="fade-in">
      Analyze Your Skills.<br>
      <span class="gradient-text">Upgrade Your Future.</span>
    </h1>
    <p class="fade-in">
      Discover skill gaps, get personalized recommendations, and track your progress 
      toward your dream career. Built for students who want to succeed.
    </p>
    <div class="hero-buttons fade-in">
      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
        <a href="skills.php" class="btn btn-secondary">Manage Skills</a>
      <?php else: ?>
        <a href="register.php" class="btn btn-primary">Get Started Free</a>
        <a href="login.php" class="btn btn-secondary">I have an account</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- How It Works Section -->
<section class="container" style="padding: 60px 24px;">
  <h2 class="text-center fade-in" style="margin-bottom: 40px;">How It Works</h2>
  <div class="dashboard-grid">
    <div class="card fade-in">
      <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
        <div style="font-size: 40px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: var(--accent-soft); border-radius: var(--radius-md);">🎯</div>
        <span style="font-size: 14px; color: var(--accent-1); font-weight: var(--font-medium);">STEP 1</span>
      </div>
      <h3>Set Your Career Goal</h3>
      <p style="color: var(--text-secondary); font-size: 14px; font-weight: var(--font-light); line-height: 1.7;">
        Choose from 8+ popular career paths like Full Stack Developer, Data Scientist, or AI Engineer. We'll analyze what skills you need.
      </p>
    </div>
    
    <div class="card fade-in">
      <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
        <div style="font-size: 40px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: var(--accent-soft); border-radius: var(--radius-md);">📊</div>
        <span style="font-size: 14px; color: var(--accent-1); font-weight: var(--font-medium);">STEP 2</span>
      </div>
      <h3>Analyze Skill Gaps</h3>
      <p style="color: var(--text-secondary); font-size: 14px; font-weight: var(--font-light); line-height: 1.7;">
        Input your current skills and proficiency levels. Our analyzer compares them against industry requirements.
      </p>
    </div>
    
    <div class="card fade-in">
      <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
        <div style="font-size: 40px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: var(--accent-soft); border-radius: var(--radius-md);">📚</div>
        <span style="font-size: 14px; color: var(--accent-1); font-weight: var(--font-medium);">STEP 3</span>
      </div>
      <h3>Get Recommendations</h3>
      <p style="color: var(--text-secondary); font-size: 14px; font-weight: var(--font-light); line-height: 1.7;">
        Receive curated courses, tutorials, and resources from top platforms to close your skill gaps effectively.
      </p>
    </div>
    
    <div class="card fade-in">
      <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
        <div style="font-size: 40px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: var(--accent-soft); border-radius: var(--radius-md);">📈</div>
        <span style="font-size: 14px; color: var(--accent-1); font-weight: var(--font-medium);">STEP 4</span>
      </div>
      <h3>Track Progress</h3>
      <p style="color: var(--text-secondary); font-size: 14px; font-weight: var(--font-light); line-height: 1.7;">
        Visual dashboard to monitor your growth, celebrate milestones, and stay motivated on your career journey.
      </p>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="container" style="padding: 40px 24px 80px;">
  <div class="insight-box fade-in">
    <div class="insight-title">
      <span>💡</span>
      <span>Why Students Choose SkillGap Analyzer</span>
    </div>
    <p>
      Our platform has helped students identify and fill skill gaps across 8 career paths. 
      With personalized recommendations and progress tracking, you'll always know exactly what to learn next.
    </p>
  </div>
  
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-top: 32px;">
    <div class="text-center fade-in">
      <div style="font-size: 36px; color: var(--accent-1); font-weight: var(--font-medium);">8+</div>
      <div style="color: var(--text-muted); font-size: 14px; font-weight: var(--font-light);">Career Paths</div>
    </div>
    <div class="text-center fade-in">
      <div style="font-size: 36px; color: var(--accent-2); font-weight: var(--font-medium);">50+</div>
      <div style="color: var(--text-muted); font-size: 14px; font-weight: var(--font-light);">Skills Tracked</div>
    </div>
    <div class="text-center fade-in">
      <div style="font-size: 36px; color: var(--accent-3); font-weight: var(--font-medium);">100+</div>
      <div style="color: var(--text-muted); font-size: 14px; font-weight: var(--font-light);">Learning Resources</div>
    </div>
    <div class="text-center fade-in">
      <div style="font-size: 36px; color: var(--success); font-weight: var(--font-medium);">Free</div>
      <div style="color: var(--text-muted); font-size: 14px; font-weight: var(--font-light);">Forever</div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
