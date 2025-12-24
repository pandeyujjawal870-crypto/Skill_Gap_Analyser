<?php 
session_start();
$page_title = "About - SkillGap Analyzer";
include 'includes/header.php'; 
?>

<div class="content-container">
  <div class="page-header">
    <h1>About SkillGap Analyzer</h1>
    <p style="font-weight: var(--font-light);">Your Personalized Career Advisor</p>
  </div>
  
  <div class="card fade-in" style="margin-bottom: 24px;">
    <h2 style="margin-bottom: 16px;">🎯 Our Mission</h2>
    <p style="color: var(--text-secondary); line-height: 1.9; font-weight: var(--font-light);">
      Finding the right career and understanding the skills needed can be overwhelming for students. 
      Most career guidance tools provide generic advice that doesn't account for individual strengths, 
      interests, and aspirations.
    </p>
    <p style="color: var(--text-secondary); line-height: 1.9; margin-top: 16px; font-weight: var(--font-light);">
      SkillGap Analyzer bridges this gap by providing <strong style="color: var(--text-primary); font-weight: var(--font-medium);">precise, personalized analysis</strong> 
      of where you stand versus where you need to be. We help you understand exactly what skills to develop 
      and provide curated resources to get you there.
    </p>
  </div>
  
  <div class="dashboard-grid" style="margin-bottom: 24px;">
    <div class="card fade-in">
      <div style="font-size: 40px; margin-bottom: 12px;">🎯</div>
      <h3>Personalized Guidance</h3>
      <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px; font-weight: var(--font-light); line-height: 1.7;">
        Get focused guidance on developing the specific skills needed for your chosen career path
      </p>
    </div>
    
    <div class="card fade-in">
      <div style="font-size: 40px; margin-bottom: 12px;">📊</div>
      <h3>Precise Gap Analysis</h3>
      <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px; font-weight: var(--font-light); line-height: 1.7;">
        Compare your current skill levels against industry requirements with exact percentages
      </p>
    </div>
    
    <div class="card fade-in">
      <div style="font-size: 40px; margin-bottom: 12px;">📚</div>
      <h3>Smart Recommendations</h3>
      <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px; font-weight: var(--font-light); line-height: 1.7;">
        Receive curated courses, tutorials, and resources from trusted platforms
      </p>
    </div>
    
    <div class="card fade-in">
      <div style="font-size: 40px; margin-bottom: 12px;">📈</div>
      <h3>Progress Tracking</h3>
      <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px; font-weight: var(--font-light); line-height: 1.7;">
        Visual dashboard to monitor your development and celebrate milestones
      </p>
    </div>
  </div>
  
  <div class="card fade-in" style="margin-bottom: 24px;">
    <h2 style="margin-bottom: 16px;">🛠️ Technology Stack</h2>
    <p style="color: var(--text-secondary); margin-bottom: 16px; font-weight: var(--font-light);">Built with modern, reliable technologies:</p>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
      <span class="badge" style="background: var(--accent-soft); color: var(--accent-1); padding: 10px 18px; font-size: 14px; font-weight: var(--font-regular);">HTML5</span>
      <span class="badge" style="background: var(--accent-soft); color: var(--accent-1); padding: 10px 18px; font-size: 14px; font-weight: var(--font-regular);">CSS3</span>
      <span class="badge" style="background: var(--accent-soft); color: var(--accent-1); padding: 10px 18px; font-size: 14px; font-weight: var(--font-regular);">JavaScript</span>
      <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: var(--accent-2); padding: 10px 18px; font-size: 14px; font-weight: var(--font-regular);">PHP</span>
      <span class="badge" style="background: rgba(217, 70, 239, 0.15); color: var(--accent-3); padding: 10px 18px; font-size: 14px; font-weight: var(--font-regular);">MySQL</span>
    </div>
  </div>
  
  <div class="card fade-in" style="margin-bottom: 24px;">
    <h2 style="margin-bottom: 16px;">🚀 Key Features</h2>
    <div style="display: grid; gap: 12px;">
      <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--bg-secondary); border-radius: var(--radius-md);">
        <span style="color: var(--success);">✓</span>
        <span style="color: var(--text-light); font-weight: var(--font-light);">8+ career paths with detailed skill requirements</span>
      </div>
      <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--bg-secondary); border-radius: var(--radius-md);">
        <span style="color: var(--success);">✓</span>
        <span style="color: var(--text-light); font-weight: var(--font-light);">Priority-based skill gap identification (Critical/Moderate/Complete)</span>
      </div>
      <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--bg-secondary); border-radius: var(--radius-md);">
        <span style="color: var(--success);">✓</span>
        <span style="color: var(--text-light); font-weight: var(--font-light);">Curated learning resources from top platforms</span>
      </div>
      <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--bg-secondary); border-radius: var(--radius-md);">
        <span style="color: var(--success);">✓</span>
        <span style="color: var(--text-light); font-weight: var(--font-light);">Visual learning roadmap with estimated timelines</span>
      </div>
      <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--bg-secondary); border-radius: var(--radius-md);">
        <span style="color: var(--success);">✓</span>
        <span style="color: var(--text-light); font-weight: var(--font-light);">Progress tracking with motivational insights</span>
      </div>
    </div>
  </div>
  
  <div class="card fade-in text-center" style="background: linear-gradient(135deg, var(--accent-soft), rgba(139, 92, 246, 0.1));">
    <h2 style="margin-bottom: 16px;">📝 Academic Project</h2>
    <p style="color: var(--text-secondary); font-weight: var(--font-light);">
      This project was developed as a mini project to help students with career guidance.
    </p>
    <p style="color: var(--text-muted); margin-top: 12px; font-size: 14px;">
      © <?= date('Y') ?> SkillGap Analyzer. Made with ❤️ for students.
    </p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
