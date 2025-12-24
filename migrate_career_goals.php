<?php
// Migrate to multiple career goals support
// Run this once: http://localhost/skill_gap_analyzer/skill_gap_analyzer/migrate_career_goals.php

include 'includes/db.php';

echo "<h2>Migrating to Multiple Career Goals Support...</h2>";

try {
    // Step 1: Create user_career_goals table
    echo "<p>Step 1: Creating user_career_goals table...</p>";
    $conn->exec("
        CREATE TABLE IF NOT EXISTS user_career_goals (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            career_goal VARCHAR(100) NOT NULL,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_goal (user_id, career_goal)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color: green;'>✓ Table created successfully</p>";
    
    // Step 2: Migrate existing career goals
    echo "<p>Step 2: Migrating existing career goals...</p>";
    $stmt = $conn->query("SELECT id, career_goal FROM users WHERE career_goal IS NOT NULL AND career_goal != ''");
    $users = $stmt->fetchAll();
    
    $migrated = 0;
    foreach($users as $user) {
        try {
            $insert = $conn->prepare("INSERT IGNORE INTO user_career_goals (user_id, career_goal) VALUES (:uid, :goal)");
            $insert->execute(['uid' => $user['id'], 'goal' => $user['career_goal']]);
            if($insert->rowCount() > 0) $migrated++;
        } catch(PDOException $e) {
            // Skip duplicates
        }
    }
    echo "<p style='color: green;'>✓ Migrated $migrated existing career goal(s)</p>";
    
    // Step 3: Verify migration
    echo "<p>Step 3: Verifying migration...</p>";
    $count = $conn->query("SELECT COUNT(*) FROM user_career_goals")->fetchColumn();
    echo "<p style='color: green;'>✓ Total career goals in new table: $count</p>";
    
    echo "<h3 style='color: green;'>✅ Migration Successful!</h3>";
    echo "<p><strong>What changed:</strong></p>";
    echo "<ul>";
    echo "<li>Created new <code>user_career_goals</code> table for multiple goals</li>";
    echo "<li>Migrated existing career goals from <code>users</code> table</li>";
    echo "<li>Users can now select multiple career paths</li>";
    echo "</ul>";
    echo "<p><a href='career_goal.php'>Go to Career Goals Page</a></p>";
    echo "<p><strong>Note:</strong> You can delete this file (migrate_career_goals.php) after running it once.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
