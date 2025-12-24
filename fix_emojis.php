<?php
// Fix emoji encoding in database
// Run this file once by visiting: http://localhost/skill_gap_analyzer/skill_gap_analyzer/fix_emojis.php

include 'includes/db.php';

echo "<h2>Fixing Emoji Encoding...</h2>";

try {
    // Update database charset
    $conn->exec("ALTER DATABASE skillgap_db CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci");
    echo "<p>✓ Database charset updated to utf8mb4</p>";
    
    // Update career_paths table
    $conn->exec("ALTER TABLE career_paths CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✓ Career paths table converted to utf8mb4</p>";
    
    // Delete old career paths
    $conn->exec("DELETE FROM career_paths");
    echo "<p>✓ Cleared old career paths</p>";
    
    // Re-insert career paths with proper emojis
    $stmt = $conn->prepare("INSERT INTO career_paths (name, description, icon) VALUES (?, ?, ?)");
    
    $careers = [
        ['Full Stack Developer', 'Build complete web applications from frontend to backend', '💻'],
        ['Frontend Developer', 'Create beautiful, responsive user interfaces', '🎨'],
        ['Backend Developer', 'Design and build server-side logic and databases', '⚙️'],
        ['Data Scientist', 'Analyze complex data to help organizations make better decisions', '📊'],
        ['AI/ML Engineer', 'Build intelligent systems that learn and adapt', '🤖'],
        ['Mobile Developer', 'Create apps for iOS and Android devices', '📱'],
        ['DevOps Engineer', 'Streamline development and deployment processes', '🚀'],
        ['Cybersecurity Analyst', 'Protect organizations from cyber threats', '🔒']
    ];
    
    foreach ($careers as $career) {
        $stmt->execute($career);
    }
    
    echo "<p>✓ Re-inserted " . count($careers) . " career paths with proper emoji icons</p>";
    echo "<h3 style='color: green;'>✅ Success! Emoji encoding fixed.</h3>";
    echo "<p><a href='career_goal.php'>Go to Career Goal page to see the icons</a></p>";
    echo "<p><strong>Note:</strong> You can delete this file (fix_emojis.php) after running it once.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
