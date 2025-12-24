<?php
// Convert all hashed passwords to plain text
// Run this once: http://localhost/skill_gap_analyzer/skill_gap_analyzer/convert_passwords.php

include 'includes/db.php';

echo "<h2>Converting Hashed Passwords to Plain Text...</h2>";

try {
    // For demonstration, we'll set all passwords to a simple default
    // In a real scenario, you'd need users to reset their passwords
    
    echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>⚠️ Important:</strong> Since hashed passwords cannot be reversed, all user passwords will be reset to: <code>password123</code>";
    echo "</div>";
    
    // Update all user passwords to plain text default
    $stmt = $conn->prepare("UPDATE users SET password = 'password123'");
    $stmt->execute();
    
    $count = $stmt->rowCount();
    
    echo "<p style='color: green;'>✓ Updated $count user password(s) to plain text</p>";
    echo "<h3 style='color: green;'>✅ Success!</h3>";
    echo "<p><strong>All users can now login with password:</strong> <code>password123</code></p>";
    echo "<p>Users should change their passwords after logging in.</p>";
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    echo "<p><strong>Note:</strong> You can delete this file (convert_passwords.php) after running it once.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
