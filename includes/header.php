<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?? 'SkillGap Analyzer' ?></title>
  <meta name="description" content="Personalized Career Advisor for Students - Analyze your skill gaps, get recommendations, and track your progress toward your dream career.">
  <meta name="keywords" content="skill gap, career advisor, student career, skill analysis, learning roadmap">
  <meta name="author" content="SkillGap Analyzer">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar">
  <div class="container">
    <a href="index.php" class="logo">SkillGap</a>
    
    <ul class="nav-links">
      <li><a href="index.php" class="<?= $current_page == 'index' ? 'active' : '' ?>">Home</a></li>
      
      <?php if($is_logged_in): ?>
        <li><a href="dashboard.php" class="<?= $current_page == 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
        <li><a href="career_goal.php" class="<?= $current_page == 'career_goal' ? 'active' : '' ?>">Career Goal</a></li>
        <li><a href="skills.php" class="<?= $current_page == 'skills' ? 'active' : '' ?>">My Skills</a></li>
        <li><a href="roadmap.php" class="<?= $current_page == 'roadmap' ? 'active' : '' ?>">Roadmap</a></li>
      <?php endif; ?>
      
      <li><a href="about.php" class="<?= $current_page == 'about' ? 'active' : '' ?>">About</a></li>
      <li><a href="contact.php" class="<?= $current_page == 'contact' ? 'active' : '' ?>">Contact</a></li>
      
      <?php if($is_logged_in): ?>
        <li><a href="logout.php" class="btn btn-secondary btn-sm">Logout</a></li>
      <?php else: ?>
        <li><a href="login.php" class="btn btn-primary btn-sm">Login</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>
