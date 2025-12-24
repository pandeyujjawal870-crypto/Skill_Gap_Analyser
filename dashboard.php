<?php
session_start();
include 'includes/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

// Get user career goal
$stmt = $conn->prepare("SELECT career_goal FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();
$career_goal = $user['career_goal'] ?? '';

// Get user's current skills
$stmt = $conn->prepare("SELECT skill_name, current_level, assessed_via FROM user_skills WHERE user_id = :uid");
$stmt->execute(['uid' => $user_id]);
$user_skills = [];
$assessed_count = 0;
while($row = $stmt->fetch()) {
    $user_skills[strtolower($row['skill_name'])] = [
        'level' => $row['current_level'],
        'via' => $row['assessed_via']
    ];
    if($row['assessed_via'] === 'quiz') $assessed_count++;
}

// Get required skills with weights
$required_skills = [];
$total_skills = 0;
$skills_met = 0;
$gap_analysis = [];
$total_weighted_progress = 0;
$total_weight = 0;

if($career_goal) {
    $stmt = $conn->prepare("SELECT skill_name, required_level, weight, priority, depends_on, description FROM required_skills WHERE career_path = :goal ORDER BY priority");
    $stmt->execute(['goal' => $career_goal]);
    $required_skills = $stmt->fetchAll();
    $total_skills = count($required_skills);
    
    foreach($required_skills as $req) {
        $skill_lower = strtolower($req['skill_name']);
        $user_level = $user_skills[$skill_lower]['level'] ?? 0;
        $user_via = $user_skills[$skill_lower]['via'] ?? 'none';
        $required_level = $req['required_level'];
        $weight = $req['weight'];
        
        $gap = max(0, $required_level - $user_level);
        $progress = min(100, ($user_level / $required_level) * 100);
        
        // Weighted progress
        $total_weighted_progress += $progress * $weight;
        $total_weight += $weight;
        
        // Priority Score = Gap × Weight (higher = more urgent)
        $priority_score = $gap * $weight;
        
        if($user_level >= $required_level) {
            $status = 'met';
            $skills_met++;
        } elseif($user_level > 0) {
            $status = 'partial';
        } else {
            $status = 'missing';
        }
        
        // Check dependencies
        $deps_met = true;
        if($req['depends_on']) {
            $deps = explode(',', $req['depends_on']);
            foreach($deps as $dep) {
                $dep_lower = strtolower(trim($dep));
                $dep_level = $user_skills[$dep_lower]['level'] ?? 0;
                if($dep_level < 60) { // Need at least 60% in prereq
                    $deps_met = false;
                    break;
                }
            }
        }
        
        $gap_analysis[] = [
            'skill' => $req['skill_name'],
            'current' => $user_level,
            'required' => $required_level,
            'weight' => $weight,
            'priority' => $req['priority'],
            'status' => $status,
            'gap' => $gap,
            'progress' => round($progress),
            'priority_score' => $priority_score,
            'assessed_via' => $user_via,
            'deps_met' => $deps_met,
            'depends_on' => $req['depends_on'],
            'description' => $req['description']
        ];
    }
}

// Calculate overall weighted readiness
$weighted_readiness = $total_weight > 0 ? round($total_weighted_progress / $total_weight) : 0;

// Sort by priority score (highest priority gaps first)
$sorted_gaps = $gap_analysis;
usort($sorted_gaps, fn($a, $b) => $b['priority_score'] - $a['priority_score']);

// Find quick wins (close to completion)
$quick_wins = array_filter($gap_analysis, fn($g) => $g['gap'] > 0 && $g['gap'] <= 20 && $g['deps_met']);
$quick_wins = array_slice($quick_wins, 0, 3);

// Find critical gaps
$critical_gaps = array_filter($sorted_gaps, fn($g) => $g['gap'] > 40 && $g['weight'] >= 4);
$critical_gaps = array_slice($critical_gaps, 0, 3);

// Today's focus - highest priority skill they can work on
$todays_focus = null;
foreach($sorted_gaps as $g) {
    if($g['gap'] > 0 && $g['deps_met']) {
        $todays_focus = $g;
        break;
    }
}

// Get recent progress (last 7 days)
$stmt = $conn->prepare("SELECT skill_name, old_level, new_level, changed_at FROM skill_history WHERE user_id = :uid ORDER BY changed_at DESC LIMIT 5");
$stmt->execute(['uid' => $user_id]);
$recent_progress = $stmt->fetchAll();

// Career info
$career_info = null;
if($career_goal) {
    $stmt = $conn->prepare("SELECT * FROM career_paths WHERE name = :name");
    $stmt->execute(['name' => $career_goal]);
    $career_info = $stmt->fetch();
}

// Motivational messages based on progress
function getMotivation($readiness, $skills_met, $total) {
    if($readiness >= 80) return ["🏆 Almost there!", "You're so close to reaching your goal!"];
    if($readiness >= 60) return ["🚀 Great momentum!", "Keep pushing, you're making excellent progress!"];
    if($readiness >= 40) return ["💪 Building strong!", "You're on the right track. Stay consistent!"];
    if($readiness >= 20) return ["🌱 Growing well!", "Every skill you learn brings you closer!"];
    return ["🎯 Just getting started!", "Every expert was once a beginner. Let's go!"];
}

$motivation = getMotivation($weighted_readiness, $skills_met, $total_skills);

$page_title = "Dashboard - SkillGap";
include 'includes/header.php';
?>

<div class="content-container">
    <!-- Welcome Header -->
    <div class="page-header fade-in">
        <h1>Welcome back, <?= htmlspecialchars(explode(' ', $user_name)[0]) ?>! 👋</h1>
        <p><?= $motivation[1] ?></p>
    </div>
    
    <?php if(!$career_goal): ?>
    <!-- No Career Goal Set -->
    <div class="card empty-state fade-in">
        <div class="empty-icon">🎯</div>
        <h3>Set Your Career Goal</h3>
        <p>Choose a career path to unlock personalized skill gap analysis, learning roadmap, and recommendations.</p>
        <a href="career_goal.php" class="btn btn-primary">Choose Career Goal →</a>
    </div>
    
    <?php else: ?>
    
    <!-- Readiness Score Card -->
    <div class="card fade-in" style="margin-bottom: 24px; background: var(--accent-gradient-soft); border: 2px solid rgba(99, 102, 241, 0.2);">
        <div style="display: grid; grid-template-columns: 1fr auto; gap: 24px; align-items: center;">
            <div>
                <div style="font-size: 14px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
                    Career Readiness Score
                </div>
                <div style="display: flex; align-items: baseline; gap: 12px; margin-bottom: 8px;">
                    <span style="font-size: 56px; font-weight: var(--font-bold); background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                        <?= $weighted_readiness ?>%
                    </span>
                    <span style="font-size: 18px; color: var(--text-secondary);">ready for</span>
                </div>
                <div style="font-size: 22px; font-weight: var(--font-semibold); color: var(--text-primary);">
                    <?= $career_info['icon'] ?? '💼' ?> <?= htmlspecialchars($career_goal) ?>
                </div>
                <?php if($career_info): ?>
                <div style="margin-top: 8px; display: flex; gap: 16px; font-size: 14px; color: var(--text-muted);">
                    <span>💰 <?= $career_info['avg_salary'] ?>/year</span>
                    <span>📈 <?= $career_info['demand_level'] ?> demand</span>
                </div>
                <?php endif; ?>
            </div>
            <div style="text-align: center;">
                <div style="width: 120px; height: 120px; border-radius: 50%; background: conic-gradient(var(--accent-1) <?= $weighted_readiness * 3.6 ?>deg, var(--bg-tertiary) 0deg); display: flex; align-items: center; justify-content: center;">
                    <div style="width: 90px; height: 90px; border-radius: 50%; background: white; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <span style="font-size: 28px; font-weight: var(--font-bold); color: var(--accent-1);"><?= $skills_met ?></span>
                        <span style="font-size: 12px; color: var(--text-muted);">of <?= $total_skills ?> skills</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="progress-bar" style="margin-top: 20px; height: 12px;">
            <div class="progress-fill" style="width: <?= $weighted_readiness ?>%"></div>
        </div>
    </div>
    
    <!-- Stats Grid -->
    <div class="stats-grid fade-in">
        <div class="stat-card">
            <div class="stat-value"><?= $skills_met ?>/<?= $total_skills ?></div>
            <div class="stat-label">Skills Mastered</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= count($user_skills) ?></div>
            <div class="stat-label">Skills Tracked</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= count($recent_progress) ?></div>
            <div class="stat-label">Recent Updates</div>
        </div>
    </div>
    
    <!-- Today's Focus -->
    <?php if($todays_focus): ?>
    <div class="card fade-in" style="margin-bottom: 24px; border-left: 4px solid var(--accent-1);">
        <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="font-size: 12px; color: var(--accent-1); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">
                    🎯 Today's Focus
                </div>
                <h2 style="margin-bottom: 8px;"><?= htmlspecialchars($todays_focus['skill']) ?></h2>
                <p style="color: var(--text-secondary); margin-bottom: 16px; font-size: 14px;">
                    <?= htmlspecialchars($todays_focus['description'] ?? 'Focus on this skill to maximize your career progress') ?>
                </p>
                <div style="display: flex; gap: 16px; font-size: 14px;">
                    <span>Current: <strong><?= $todays_focus['current'] ?>%</strong></span>
                    <span>Target: <strong><?= $todays_focus['required'] ?>%</strong></span>
                    <span class="badge badge-warning">Gap: <?= $todays_focus['gap'] ?>%</span>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="roadmap.php#<?= urlencode($todays_focus['skill']) ?>" class="btn btn-primary">View Resources</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Quick Wins & Critical Gaps -->
    <div class="dashboard-grid">
        <!-- Quick Wins -->
        <div class="card fade-in">
            <h3 style="margin-bottom: 16px; color: var(--success);">⚡ Quick Wins</h3>
            <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 16px;">Skills close to completion</p>
            
            <?php if(empty($quick_wins)): ?>
                <p style="color: var(--text-muted); font-style: italic;">No quick wins right now. Keep learning!</p>
            <?php else: ?>
                <?php foreach($quick_wins as $qw): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--success-bg); border-radius: var(--radius-md); margin-bottom: 10px;">
                    <div>
                        <div style="font-weight: var(--font-semibold);"><?= htmlspecialchars($qw['skill']) ?></div>
                        <div style="font-size: 13px; color: var(--text-muted);">Just <?= $qw['gap'] ?>% more to go!</div>
                    </div>
                    <span class="badge badge-success"><?= $qw['progress'] ?>%</span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Critical Gaps -->
        <div class="card fade-in">
            <h3 style="margin-bottom: 16px; color: var(--danger);">🚨 Critical Gaps</h3>
            <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 16px;">High-priority skills needing attention</p>
            
            <?php if(empty($critical_gaps)): ?>
                <p style="color: var(--text-muted); font-style: italic;">No critical gaps! You're doing great!</p>
            <?php else: ?>
                <?php foreach($critical_gaps as $cg): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--danger-bg); border-radius: var(--radius-md); margin-bottom: 10px;">
                    <div>
                        <div style="font-weight: var(--font-semibold);"><?= htmlspecialchars($cg['skill']) ?></div>
                        <div style="font-size: 13px; color: var(--text-muted);">
                            <?= $cg['current'] ?>% → <?= $cg['required'] ?>% needed
                            <?php if($cg['weight'] == 5): ?><span style="color: var(--danger);">★ Critical</span><?php endif; ?>
                        </div>
                    </div>
                    <a href="roadmap.php#<?= urlencode($cg['skill']) ?>" class="btn btn-sm btn-danger">View</a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Skill Gap Analysis Table -->
    <div class="card fade-in" style="margin-top: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>📊 Complete Skill Gap Analysis</h3>
            <a href="skills.php" class="btn btn-secondary btn-sm">Manage Skills</a>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Skill</th>
                        <th>Current</th>
                        <th>Required</th>
                        <th>Gap</th>
                        <th>Weight</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($sorted_gaps as $gap): ?>
                    <tr>
                        <td>
                            <div style="font-weight: var(--font-semibold);"><?= htmlspecialchars($gap['skill']) ?></div>
                            <?php if($gap['depends_on'] && !$gap['deps_met']): ?>
                                <div style="font-size: 11px; color: var(--warning);">⚠️ Requires: <?= htmlspecialchars($gap['depends_on']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="font-weight: var(--font-medium);"><?= $gap['current'] ?>%</span>
                            <?php if($gap['assessed_via'] === 'quiz'): ?>
                                <span style="font-size: 10px; color: var(--success);">✓</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $gap['required'] ?>%</td>
                        <td>
                            <?php if($gap['gap'] > 0): ?>
                                <span style="color: <?= $gap['gap'] > 40 ? 'var(--danger)' : ($gap['gap'] > 20 ? 'var(--warning)' : 'var(--success)') ?>;">
                                    <?= $gap['gap'] ?>%
                                </span>
                            <?php else: ?>
                                <span style="color: var(--success);">✓ None</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php for($i = 0; $i < $gap['weight']; $i++): ?>
                                <span style="color: var(--warning);">★</span>
                            <?php endfor; ?>
                        </td>
                        <td>
                            <?php if($gap['status'] === 'met'): ?>
                                <span class="badge badge-success">Mastered</span>
                            <?php elseif($gap['status'] === 'partial'): ?>
                                <span class="badge badge-warning">In Progress</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Not Started</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($gap['gap'] > 0): ?>
                                <a href="roadmap.php#<?= urlencode($gap['skill']) ?>" class="btn btn-primary btn-sm">📚 Learn</a>
                            <?php else: ?>
                                <span style="color: var(--success);">🎉 Done</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Recent Progress -->
    <?php if(!empty($recent_progress)): ?>
    <div class="card fade-in" style="margin-top: 24px;">
        <h3 style="margin-bottom: 16px;">📈 Recent Progress</h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach($recent_progress as $rp): ?>
            <div style="display: flex; align-items: center; gap: 16px; padding: 12px; background: var(--bg-tertiary); border-radius: var(--radius-md);">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--success-bg); display: flex; align-items: center; justify-content: center; color: var(--success);">
                    <?php if($rp['new_level'] > $rp['old_level']): ?>↑<?php else: ?>→<?php endif; ?>
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: var(--font-medium);"><?= htmlspecialchars($rp['skill_name']) ?></div>
                    <div style="font-size: 13px; color: var(--text-muted);">
                        <?= $rp['old_level'] ?>% → <?= $rp['new_level'] ?>%
                        <span style="color: var(--success);">(+<?= $rp['new_level'] - $rp['old_level'] ?>%)</span>
                    </div>
                </div>
                <div style="font-size: 13px; color: var(--text-muted);">
                    <?= date('M j', strtotime($rp['changed_at'])) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Action Buttons -->
    <div style="display: flex; gap: 16px; justify-content: center; margin-top: 40px; flex-wrap: wrap;">
        <a href="skills.php" class="btn btn-secondary">Add/Update Skills</a>
        <a href="roadmap.php" class="btn btn-primary">View Learning Roadmap →</a>
    </div>
    
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
