<?php
session_start();
include 'includes/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user career goal
$stmt = $conn->prepare("SELECT career_goal FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();
$career_goal = $user['career_goal'] ?? '';

// Get required skills with dependencies
$required_skills = [];
$total_skills = 0;
$completed_skills = 0;

if($career_goal) {
    $stmt = $conn->prepare("SELECT skill_name, required_level, weight, priority, depends_on, description FROM required_skills WHERE career_path = :goal ORDER BY priority");
    $stmt->execute(['goal' => $career_goal]);
    $required_skills = $stmt->fetchAll();
    $total_skills = count($required_skills);
}

// Get user's current skills
$stmt = $conn->prepare("SELECT skill_name, current_level, assessed_via FROM user_skills WHERE user_id = :uid");
$stmt->execute(['uid' => $user_id]);
$user_skills = [];
while($row = $stmt->fetch()) {
    $user_skills[strtolower($row['skill_name'])] = [
        'level' => $row['current_level'],
        'via' => $row['assessed_via']
    ];
}

// Get resources for each skill
function getResources($conn, $skill_name) {
    $stmt = $conn->prepare("SELECT * FROM resources WHERE LOWER(skill_name) = LOWER(:skill) ORDER BY difficulty");
    $stmt->execute(['skill' => $skill_name]);
    return $stmt->fetchAll();
}

// Get resource count
function getResourceCount($conn, $skill_name) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM resources WHERE LOWER(skill_name) = LOWER(:skill)");
    $stmt->execute(['skill' => $skill_name]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

// Check if dependencies are met
function checkDependencies($depends_on, $user_skills, $min_level = 60) {
    if(empty($depends_on)) return ['met' => true, 'missing' => []];
    
    $deps = explode(',', $depends_on);
    $missing = [];
    
    foreach($deps as $dep) {
        $dep = trim($dep);
        $dep_lower = strtolower($dep);
        $current = $user_skills[$dep_lower]['level'] ?? 0;
        if($current < $min_level) {
            $missing[] = ['skill' => $dep, 'current' => $current, 'needed' => $min_level];
        }
    }
    
    return ['met' => empty($missing), 'missing' => $missing];
}

// Estimate learning time based on skill gap
function estimateLearningTime($gap) {
    if($gap <= 0) return "Completed";
    if($gap <= 20) return "~2-4 weeks";
    if($gap <= 40) return "~1-2 months";
    if($gap <= 60) return "~2-3 months";
    return "~3-6 months";
}

// Get resource type icon
function getResourceIcon($type) {
    $icons = [
        'course' => '📚',
        'tutorial' => '📖',
        'video' => '🎬',
        'article' => '📄',
        'book' => '📘'
    ];
    return $icons[$type] ?? '📚';
}


$page_title = "Learning Roadmap - SkillGap";
include 'includes/header.php';
?>

<div class="content-container">
    <div class="page-header fade-in">
        <h1>🗺️ Your Learning Roadmap</h1>
        <p>A step-by-step path to achieve your career goal with skill dependencies</p>
    </div>
    
    <?php if(!$career_goal): ?>
    <div class="card empty-state fade-in">
        <div class="empty-icon">🎯</div>
        <h3>Set Your Career Goal First</h3>
        <p>Choose a career path to see your personalized learning roadmap with step-by-step guidance.</p>
        <a href="career_goal.php" class="btn btn-primary">Choose Career Goal</a>
    </div>
    
    <?php else: ?>
    
    <!-- Goal Overview Card -->
    <div class="card fade-in" style="margin-bottom: 32px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h2 style="margin-bottom: 8px;">🎯 Goal: <?= htmlspecialchars($career_goal) ?></h2>
                <p style="color: var(--text-secondary); margin: 0;">Master <?= $total_skills ?> key skills in the right order</p>
            </div>
            <div style="text-align: right;">
                <?php
                $overall_progress = 0;
                foreach($required_skills as $skill) {
                    $skill_lower = strtolower($skill['skill_name']);
                    $user_level = $user_skills[$skill_lower]['level'] ?? 0;
                    $progress = min(100, ($user_level / $skill['required_level']) * 100);
                    $overall_progress += $progress;
                    if($user_level >= $skill['required_level']) $completed_skills++;
                }
                $overall_progress = $total_skills > 0 ? round($overall_progress / $total_skills) : 0;
                ?>
                <div style="font-size: 32px; font-weight: var(--font-bold); background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                    <?= $overall_progress ?>%
                </div>
                <div style="color: var(--text-muted); font-size: 14px;">
                    <?= $completed_skills ?> of <?= $total_skills ?> skills mastered
                </div>
            </div>
        </div>
        
        <div class="progress-bar" style="margin-top: 20px; height: 12px;">
            <div class="progress-fill" style="width: <?= $overall_progress ?>%"></div>
        </div>
    </div>
    
    <!-- Legend -->
    <div class="insight-box fade-in" style="margin-bottom: 32px;">
        <div class="insight-title">
            <span>📖</span>
            <span>Roadmap Legend</span>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 12px; font-size: 14px;">
            <span><span style="color: var(--success);">●</span> Mastered</span>
            <span><span style="color: var(--warning);">●</span> In Progress</span>
            <span><span style="color: var(--accent-1);">●</span> Ready to Start</span>
            <span><span style="color: var(--text-muted);">●</span> Locked (needs prerequisites)</span>
            <span>★ = Importance level</span>
        </div>
    </div>
    
    <!-- Roadmap Timeline -->
    <div style="position: relative; padding-left: 40px;">
        <!-- Timeline line -->
        <div style="position: absolute; left: 15px; top: 0; bottom: 0; width: 3px; background: linear-gradient(to bottom, var(--accent-1), var(--accent-2), var(--accent-3)); border-radius: 999px;"></div>
        
        <?php $step = 1; foreach($required_skills as $skill): 
            $skill_lower = strtolower($skill['skill_name']);
            $user_level = $user_skills[$skill_lower]['level'] ?? 0;
            $user_via = $user_skills[$skill_lower]['via'] ?? 'none';
            $is_complete = $user_level >= $skill['required_level'];
            $is_started = $user_level > 0;
            $progress_percent = min(100, round(($user_level / $skill['required_level']) * 100));
            $gap = max(0, $skill['required_level'] - $user_level);
            $learning_time = estimateLearningTime($gap);
            $resources = getResources($conn, $skill['skill_name']);
            $resource_count = count($resources);
            $deps = checkDependencies($skill['depends_on'], $user_skills);
            // UNLOCK ALL: User requested to remove criteria
            $is_locked = false; // logic was: !$deps['met'] && !$is_complete;
        ?>
        <div class="fade-in" style="position: relative; margin-bottom: 24px;" id="<?= htmlspecialchars($skill['skill_name']) ?>">
            <!-- Timeline dot -->
            <div style="position: absolute; left: -40px; top: 12px; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: var(--font-bold); transition: var(--transition);
                <?php if($is_complete): ?>
                    background: var(--success); color: white; box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
                <?php elseif($is_locked): ?>
                    background: var(--bg-tertiary); color: var(--text-muted); border: 2px dashed var(--text-muted);
                <?php elseif($is_started): ?>
                    background: var(--warning); color: white; box-shadow: 0 0 15px rgba(245, 158, 11, 0.4);
                <?php else: ?>
                    background: white; border: 3px solid var(--accent-1); color: var(--accent-1);
                <?php endif; ?>
            ">
                <?php if($is_complete): ?>✓
                <?php elseif($is_locked): ?>🔒
                <?php else: ?><?= $step ?><?php endif; ?>
            </div>
            
            <div class="card" style="
                <?= $is_complete ? 'opacity: 0.8; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(6, 182, 212, 0.05));' : '' ?>
                <?= $is_locked ? 'opacity: 0.6; filter: grayscale(30%);' : '' ?>
            ">
                <!-- Header -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <h3 style="margin: 0;"><?= htmlspecialchars($skill['skill_name']) ?></h3>
                            <!-- Weight stars -->
                            <span style="font-size: 12px; color: var(--warning);">
                                <?php for($i = 0; $i < $skill['weight']; $i++) echo '★'; ?>
                            </span>
                        </div>
                        <div style="font-size: 13px; color: var(--text-muted);">
                            <?= htmlspecialchars($skill['description'] ?? 'Required: ' . $skill['required_level'] . '% proficiency') ?>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <?php if($is_complete): ?>
                            <span class="badge badge-success">✓ Mastered</span>
                        <?php elseif($is_locked): ?>
                            <span class="badge" style="background: var(--bg-tertiary); color: var(--text-muted);">🔒 Locked</span>
                        <?php elseif($is_started): ?>
                            <span class="badge badge-warning"><?= $progress_percent ?>% Complete</span>
                        <?php else: ?>
                            <span class="badge" style="background: var(--accent-soft); color: var(--accent-1);">Step <?= $step ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Locked message -->
                <?php if($is_locked): ?>
                <div style="background: var(--warning-bg); padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 16px; font-size: 14px;">
                    <strong style="color: var(--warning);">⚠️ Prerequisites Required:</strong>
                    <div style="margin-top: 8px;">
                        <?php foreach($deps['missing'] as $m): ?>
                            <span style="display: inline-block; padding: 4px 10px; background: white; border-radius: 999px; margin: 4px 4px 4px 0; font-size: 13px;">
                                <?= htmlspecialchars($m['skill']) ?>: <?= $m['current'] ?>% → <?= $m['needed'] ?>% needed
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Progress Info -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 16px; margin-bottom: 12px;">
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Your Level</div>
                        <div style="font-size: 20px; font-weight: var(--font-medium); color: <?= $user_level > 0 ? 'var(--text-primary)' : 'var(--text-muted)' ?>;"><?= $user_level ?>%</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Required</div>
                        <div style="font-size: 20px; font-weight: var(--font-medium); color: var(--text-primary);"><?= $skill['required_level'] ?>%</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Est. Time</div>
                        <div style="font-size: 20px; font-weight: var(--font-medium); color: var(--text-primary);"><?= $learning_time ?></div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Resources</div>
                        <div style="font-size: 20px; font-weight: var(--font-medium); color: var(--accent-1);"><?= $resource_count ?> available</div>
                    </div>
                </div>
                
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $progress_percent ?>%"></div>
                </div>
                
                <?php if(!$is_complete && !$is_locked): ?>
                    <!-- Learning Resources -->
                    <?php if($resource_count > 0): ?>
                    <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(99, 102, 241, 0.1);">
                        <h4 style="font-size: 14px; margin-bottom: 12px; color: var(--text-secondary);">📚 Learning Resources</h4>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <?php foreach($resources as $resource): ?>
                            <a href="<?= htmlspecialchars($resource['url']) ?>" target="_blank" rel="noopener" class="resource-link">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <span style="font-size: 18px;"><?= getResourceIcon($resource['type']) ?></span>
                                    <div>
                                        <div style="color: var(--text-primary); font-weight: var(--font-medium);"><?= htmlspecialchars($resource['title']) ?></div>
                                        <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($resource['platform']) ?> • <?= ucfirst($resource['difficulty']) ?></div>
                                    </div>
                                </div>
                                <span style="color: var(--accent-1);">→</span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div style="margin-top: 20px; padding: 16px; background: var(--bg-tertiary); border-radius: var(--radius-md); text-align: center;">
                        <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
                            💡 Search for "<strong><?= htmlspecialchars($skill['skill_name']) ?> tutorials</strong>" to find learning resources
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <div style="margin-top: 16px; display: flex; gap: 12px; flex-wrap: wrap;">
                        <a href="skills.php" class="btn btn-secondary btn-sm">Update Skill Level</a>
                    </div>
                <?php elseif($is_complete): ?>
                    <div style="margin-top: 16px; color: var(--success); font-size: 14px; display: flex; align-items: center; gap: 8px;">
                        <span>🎉</span>
                        <span>Great job! You've mastered this skill.</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php $step++; endforeach; ?>
        
        <!-- Final Goal Milestone -->
        <div class="fade-in" style="position: relative;">
            <div style="position: absolute; left: -40px; top: 12px; width: 28px; height: 28px; border-radius: 50%; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-size: 14px; box-shadow: 0 0 20px rgba(99, 102, 241, 0.5);">
                🏆
            </div>
            <div class="card" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(139, 92, 246, 0.1)); border: 2px solid var(--accent-1);">
                <h3 style="margin-bottom: 8px;">🎉 You're a <?= htmlspecialchars($career_goal) ?>!</h3>
                <p style="color: var(--text-secondary); margin: 0;">
                    <?php if($completed_skills === $total_skills && $total_skills > 0): ?>
                        Congratulations! You've mastered all the required skills. You're ready for your dream career!
                    <?php else: ?>
                        Complete all <?= $total_skills ?> skills above to achieve your career goal. You're <?= $overall_progress ?>% of the way there!
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div style="display: flex; gap: 16px; justify-content: center; margin-top: 40px; flex-wrap: wrap;">
        <a href="skills.php" class="btn btn-secondary">Manage My Skills</a>
        <a href="dashboard.php" class="btn btn-primary">View Dashboard →</a>
    </div>
    
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
