<?php
// Add comprehensive learning resources for all skills
// Run this file once: http://localhost/skill_gap_analyzer/skill_gap_analyzer/add_resources.php

include 'includes/db.php';

echo "<h2>Adding User-Friendly Learning Resources...</h2>";

try {
    // Clear existing resources
    $conn->exec("DELETE FROM resources");
    echo "<p>✓ Cleared old resources</p>";
    
    // Prepare insert statement
    $stmt = $conn->prepare("INSERT INTO resources (skill_name, title, url, type, platform, difficulty) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Comprehensive resources for all skills
    $resources = [
        // HTML/CSS
        ['HTML/CSS', 'HTML & CSS Full Course - Beginner to Pro', 'https://www.youtube.com/watch?v=G3e-cpL7ofc', 'video', 'YouTube (SuperSimpleDev)', 'beginner'],
        ['HTML/CSS', 'HTML Crash Course For Absolute Beginners', 'https://www.youtube.com/watch?v=UB1O30fR-EE', 'video', 'YouTube (Traversy Media)', 'beginner'],
        ['HTML/CSS', 'CSS Tutorial - Zero to Hero', 'https://www.youtube.com/watch?v=1Rs2ND1ryYc', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['HTML/CSS', 'MDN Web Docs - HTML', 'https://developer.mozilla.org/en-US/docs/Web/HTML', 'tutorial', 'MDN (Mozilla)', 'beginner'],
        ['HTML/CSS', 'W3Schools HTML Tutorial', 'https://www.w3schools.com/html/', 'tutorial', 'W3Schools', 'beginner'],
        ['HTML/CSS', 'Flexbox Froggy - Learn CSS Flexbox', 'https://flexboxfroggy.com/', 'tutorial', 'Flexbox Froggy', 'intermediate'],
        
        // JavaScript
        ['JavaScript', 'JavaScript Full Course for Beginners', 'https://www.youtube.com/watch?v=PkZNo7MFNFg', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['JavaScript', 'JavaScript Tutorial for Beginners', 'https://www.youtube.com/watch?v=W6NZfCO5SIk', 'video', 'YouTube (Mosh)', 'beginner'],
        ['JavaScript', 'Modern JavaScript From The Beginning', 'https://www.youtube.com/watch?v=BI1o2H9z9fo', 'video', 'YouTube (Traversy Media)', 'intermediate'],
        ['JavaScript', 'JavaScript.info - The Modern Tutorial', 'https://javascript.info/', 'tutorial', 'JavaScript.info', 'beginner'],
        ['JavaScript', 'MDN JavaScript Guide', 'https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide', 'tutorial', 'MDN (Mozilla)', 'intermediate'],
        ['JavaScript', 'Eloquent JavaScript (Free Book)', 'https://eloquentjavascript.net/', 'book', 'Online Book', 'intermediate'],
        
        // React/Vue
        ['React/Vue', 'React Course - Beginner\'s Tutorial', 'https://www.youtube.com/watch?v=bMknfKXIFA8', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['React/Vue', 'React JS Full Course 2024', 'https://www.youtube.com/watch?v=CgkZ7MvWUAA', 'video', 'YouTube (Mosh)', 'beginner'],
        ['React/Vue', 'Vue.js Course for Beginners', 'https://www.youtube.com/watch?v=FXpIoQ_rT_c', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['React/Vue', 'React Official Documentation', 'https://react.dev/learn', 'tutorial', 'React.dev', 'beginner'],
        ['React/Vue', 'Vue.js Official Guide', 'https://vuejs.org/guide/introduction.html', 'tutorial', 'Vue.js', 'beginner'],
        
        // Node.js
        ['Node.js', 'Node.js Full Course for Beginners', 'https://www.youtube.com/watch?v=Oe421EPjeBE', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['Node.js', 'Node.js Crash Course', 'https://www.youtube.com/watch?v=fBNz5xF-Kx4', 'video', 'YouTube (Traversy Media)', 'beginner'],
        ['Node.js', 'Node.js Official Documentation', 'https://nodejs.org/en/docs/', 'tutorial', 'Node.js', 'intermediate'],
        ['Node.js', 'Express.js Tutorial', 'https://www.youtube.com/watch?v=L72fhGm1tfE', 'video', 'YouTube (freeCodeCamp)', 'intermediate'],
        
        // Python
        ['Python', 'Python for Beginners - Full Course', 'https://www.youtube.com/watch?v=rfscVS0vtbw', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['Python', 'Python Tutorial for Beginners', 'https://www.youtube.com/watch?v=_uQrJ0TkZlc', 'video', 'YouTube (Mosh)', 'beginner'],
        ['Python', 'Python Official Tutorial', 'https://docs.python.org/3/tutorial/', 'tutorial', 'Python.org', 'beginner'],
        ['Python', 'Real Python Tutorials', 'https://realpython.com/', 'tutorial', 'Real Python', 'intermediate'],
        ['Python', 'Google\'s Python Class', 'https://developers.google.com/edu/python', 'course', 'Google', 'beginner'],
        
        // SQL/Database
        ['SQL/Database', 'SQL Tutorial - Full Course', 'https://www.youtube.com/watch?v=HXV3zeQKqGY', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['SQL/Database', 'MySQL Tutorial for Beginners', 'https://www.youtube.com/watch?v=7S_tz1z_5bA', 'video', 'YouTube (Mosh)', 'beginner'],
        ['SQL/Database', 'W3Schools SQL Tutorial', 'https://www.w3schools.com/sql/', 'tutorial', 'W3Schools', 'beginner'],
        ['SQL/Database', 'PostgreSQL Tutorial', 'https://www.postgresqltutorial.com/', 'tutorial', 'PostgreSQL Tutorial', 'intermediate'],
        ['SQL/Database', 'MongoDB Crash Course', 'https://www.youtube.com/watch?v=-56x56UppqQ', 'video', 'YouTube (Traversy Media)', 'beginner'],
        
        // Git
        ['Git', 'Git and GitHub for Beginners', 'https://www.youtube.com/watch?v=RGOj5yH7evk', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['Git', 'Git Tutorial for Beginners', 'https://www.youtube.com/watch?v=8JJ101D3knE', 'video', 'YouTube (Mosh)', 'beginner'],
        ['Git', 'Pro Git Book (Free)', 'https://git-scm.com/book/en/v2', 'book', 'Git-SCM', 'beginner'],
        ['Git', 'GitHub Skills', 'https://skills.github.com/', 'course', 'GitHub', 'beginner'],
        
        // Machine Learning
        ['Machine Learning', 'Machine Learning Course - Google', 'https://developers.google.com/machine-learning/crash-course', 'course', 'Google', 'beginner'],
        ['Machine Learning', 'Machine Learning for Beginners', 'https://www.youtube.com/watch?v=gmvvaobm7eQ', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['Machine Learning', 'ML Course by Andrew Ng', 'https://www.coursera.org/learn/machine-learning', 'course', 'Coursera', 'intermediate'],
        ['Machine Learning', 'Scikit-learn Tutorial', 'https://scikit-learn.org/stable/tutorial/index.html', 'tutorial', 'Scikit-learn', 'intermediate'],
        
        // Deep Learning
        ['Deep Learning', 'Deep Learning Specialization', 'https://www.coursera.org/specializations/deep-learning', 'course', 'Coursera (Andrew Ng)', 'intermediate'],
        ['Deep Learning', 'Deep Learning Crash Course', 'https://www.youtube.com/watch?v=VyWAvY2CF9c', 'video', 'YouTube (freeCodeCamp)', 'intermediate'],
        ['Deep Learning', 'Fast.ai - Practical Deep Learning', 'https://course.fast.ai/', 'course', 'Fast.ai', 'intermediate'],
        
        // TensorFlow/PyTorch
        ['TensorFlow/PyTorch', 'TensorFlow 2.0 Complete Course', 'https://www.youtube.com/watch?v=tPYj3fFJGjk', 'video', 'YouTube (freeCodeCamp)', 'intermediate'],
        ['TensorFlow/PyTorch', 'PyTorch for Deep Learning', 'https://www.youtube.com/watch?v=V_xro1bcAuA', 'video', 'YouTube (freeCodeCamp)', 'intermediate'],
        ['TensorFlow/PyTorch', 'TensorFlow Official Tutorials', 'https://www.tensorflow.org/tutorials', 'tutorial', 'TensorFlow', 'intermediate'],
        ['TensorFlow/PyTorch', 'PyTorch Official Tutorials', 'https://pytorch.org/tutorials/', 'tutorial', 'PyTorch', 'intermediate'],
        
        // Data Visualization
        ['Data Visualization', 'Data Visualization with Python', 'https://www.youtube.com/watch?v=_YWwU-gJI5U', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['Data Visualization', 'Matplotlib Tutorial', 'https://www.youtube.com/watch?v=3Xc3CA655Y4', 'video', 'YouTube (Corey Schafer)', 'beginner'],
        ['Data Visualization', 'Tableau Tutorial for Beginners', 'https://www.youtube.com/watch?v=aHaOIvR00So', 'video', 'YouTube (Edureka)', 'beginner'],
        
        // Statistics
        ['Statistics', 'Statistics Full Course', 'https://www.youtube.com/watch?v=xxpc-HPKN28', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['Statistics', 'Khan Academy Statistics', 'https://www.khanacademy.org/math/statistics-probability', 'course', 'Khan Academy', 'beginner'],
        
        // Mathematics
        ['Mathematics', 'Mathematics for Machine Learning', 'https://www.youtube.com/watch?v=1VSZtNYMntM', 'video', 'YouTube (freeCodeCamp)', 'intermediate'],
        ['Mathematics', 'Linear Algebra - Khan Academy', 'https://www.khanacademy.org/math/linear-algebra', 'course', 'Khan Academy', 'intermediate'],
        ['Mathematics', 'Calculus - Khan Academy', 'https://www.khanacademy.org/math/calculus-1', 'course', 'Khan Academy', 'intermediate'],
        
        // PHP/Python/Node
        ['PHP/Python/Node', 'PHP Tutorial for Beginners', 'https://www.youtube.com/watch?v=OK_JCtrrv-c', 'video', 'YouTube (Mosh)', 'beginner'],
        ['PHP/Python/Node', 'PHP Full Course', 'https://www.youtube.com/watch?v=2eebptXfEvw', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['PHP/Python/Node', 'W3Schools PHP Tutorial', 'https://www.w3schools.com/php/', 'tutorial', 'W3Schools', 'beginner'],
        
        // REST APIs
        ['REST APIs', 'REST API Tutorial', 'https://www.youtube.com/watch?v=-MTSQjw5DrM', 'video', 'YouTube (freeCodeCamp)', 'intermediate'],
        ['REST APIs', 'REST API Crash Course', 'https://www.youtube.com/watch?v=Q-BpqyOT3a8', 'video', 'YouTube (Traversy Media)', 'intermediate'],
        ['REST APIs', 'RESTful API Design', 'https://restfulapi.net/', 'tutorial', 'RESTful API', 'intermediate'],
        
        // Authentication
        ['Authentication', 'JWT Authentication Tutorial', 'https://www.youtube.com/watch?v=mbsmsi7l3r4', 'video', 'YouTube (Web Dev Simplified)', 'intermediate'],
        ['Authentication', 'OAuth 2.0 Explained', 'https://www.youtube.com/watch?v=996OiexHze0', 'video', 'YouTube (OAuth)', 'intermediate'],
        
        // Server Management
        ['Server Management', 'Linux for Beginners', 'https://www.youtube.com/watch?v=sWbUDq4S6Y8', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['Server Management', 'Docker Tutorial for Beginners', 'https://www.youtube.com/watch?v=fqMOX6JJhGo', 'video', 'YouTube (freeCodeCamp)', 'intermediate'],
        ['Server Management', 'AWS Tutorial for Beginners', 'https://www.youtube.com/watch?v=ulprqHHWlng', 'video', 'YouTube (Edureka)', 'intermediate'],
        
        // UI/UX Design
        ['UI/UX Design', 'UI/UX Design Tutorial', 'https://www.youtube.com/watch?v=c9Wg6Cb_YlU', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['UI/UX Design', 'Figma Tutorial for Beginners', 'https://www.youtube.com/watch?v=FTFaQWZBqQ8', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['UI/UX Design', 'Google UX Design Course', 'https://www.coursera.org/professional-certificates/google-ux-design', 'course', 'Google (Coursera)', 'beginner'],
        
        // Responsive Design
        ['Responsive Design', 'Responsive Web Design Tutorial', 'https://www.youtube.com/watch?v=srvUrASNj0s', 'video', 'YouTube (freeCodeCamp)', 'beginner'],
        ['Responsive Design', 'CSS Grid & Flexbox Tutorial', 'https://www.youtube.com/watch?v=EiNiSFIPIQE', 'video', 'YouTube (Traversy Media)', 'intermediate'],
    ];
    
    $count = 0;
    foreach ($resources as $resource) {
        $stmt->execute($resource);
        $count++;
    }
    
    echo "<p>✓ Added $count comprehensive learning resources</p>";
    echo "<h3 style='color: green;'>✅ Success! User-friendly resources added.</h3>";
    echo "<p><a href='roadmap.php'>Go to Roadmap to see the resources</a></p>";
    echo "<p><strong>Note:</strong> You can delete this file (add_resources.php) after running it once.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
