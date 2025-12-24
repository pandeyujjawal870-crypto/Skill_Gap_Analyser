<?php
// Complete Database Setup with New Features
// Run this file once to set up all tables

$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Connect without database first
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS skillgap_db");
    $pdo->exec("USE skillgap_db");
    
    // Drop old tables to recreate with new schema
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS skill_history");
    $pdo->exec("DROP TABLE IF EXISTS quiz_questions");
    $pdo->exec("DROP TABLE IF EXISTS quiz_results");
    $pdo->exec("DROP TABLE IF EXISTS resources");
    $pdo->exec("DROP TABLE IF EXISTS user_skills");
    $pdo->exec("DROP TABLE IF EXISTS required_skills");
    $pdo->exec("DROP TABLE IF EXISTS career_paths");
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Users table
    $pdo->exec("CREATE TABLE users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        career_goal VARCHAR(100) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Career paths
    $pdo->exec("CREATE TABLE career_paths (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        icon VARCHAR(10) DEFAULT '💼',
        avg_salary VARCHAR(50),
        demand_level VARCHAR(20) DEFAULT 'High'
    )");
    
    // Required skills with weights and dependencies
    $pdo->exec("CREATE TABLE required_skills (
        id INT PRIMARY KEY AUTO_INCREMENT,
        career_path VARCHAR(100) NOT NULL,
        skill_name VARCHAR(100) NOT NULL,
        required_level INT DEFAULT 70,
        weight INT DEFAULT 3,
        priority INT DEFAULT 1,
        depends_on VARCHAR(255) DEFAULT NULL,
        description TEXT
    )");
    
    // User skills
    $pdo->exec("CREATE TABLE user_skills (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        skill_name VARCHAR(100) NOT NULL,
        current_level INT DEFAULT 0,
        assessed_via VARCHAR(20) DEFAULT 'self',
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    // Skill history for tracking progress
    $pdo->exec("CREATE TABLE skill_history (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        skill_name VARCHAR(100) NOT NULL,
        old_level INT,
        new_level INT,
        changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    // Quiz questions
    $pdo->exec("CREATE TABLE quiz_questions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        skill_name VARCHAR(100) NOT NULL,
        question TEXT NOT NULL,
        option_a VARCHAR(500),
        option_b VARCHAR(500),
        option_c VARCHAR(500),
        option_d VARCHAR(500),
        correct_answer CHAR(1) NOT NULL,
        difficulty INT DEFAULT 1,
        explanation TEXT
    )");
    
    // Quiz results
    $pdo->exec("CREATE TABLE quiz_results (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        skill_name VARCHAR(100) NOT NULL,
        score INT,
        total_questions INT,
        calculated_level INT,
        taken_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    // Resources
    $pdo->exec("CREATE TABLE resources (
        id INT PRIMARY KEY AUTO_INCREMENT,
        skill_name VARCHAR(100) NOT NULL,
        title VARCHAR(255) NOT NULL,
        url TEXT,
        type VARCHAR(50) DEFAULT 'course',
        platform VARCHAR(100),
        difficulty VARCHAR(20) DEFAULT 'beginner'
    )");
    
    // Insert Career Paths
    $careers = [
        ['Data Scientist', 'Analyze complex data to help organizations make better decisions', '📊', '$120,000', 'Very High'],
        ['Full Stack Developer', 'Build complete web applications from frontend to backend', '💻', '$95,000', 'Very High'],
        ['Frontend Developer', 'Create beautiful, responsive user interfaces', '🎨', '$85,000', 'High'],
        ['Backend Developer', 'Design and build server-side logic and databases', '⚙️', '$90,000', 'High'],
        ['DevOps Engineer', 'Streamline development and deployment processes', '🔧', '$110,000', 'Very High'],
        ['Mobile Developer', 'Create apps for iOS and Android devices', '📱', '$95,000', 'High'],
        ['Cybersecurity Analyst', 'Protect organizations from cyber threats', '🔒', '$100,000', 'Very High'],
        ['AI/ML Engineer', 'Build intelligent systems that learn and adapt', '🤖', '$130,000', 'Very High']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO career_paths (name, description, icon, avg_salary, demand_level) VALUES (?, ?, ?, ?, ?)");
    foreach($careers as $c) {
        $stmt->execute($c);
    }
    
    // Required skills with weights and priorities
    $skills = [
        // Data Scientist
        ['Data Scientist', 'Python', 85, 5, 1, NULL, 'Core programming language for data science'],
        ['Data Scientist', 'Statistics', 80, 5, 2, NULL, 'Foundation for understanding data patterns'],
        ['Data Scientist', 'SQL/Database', 75, 4, 3, NULL, 'Essential for data extraction and management'],
        ['Data Scientist', 'Machine Learning', 80, 5, 4, 'Python,Statistics', 'Building predictive models'],
        ['Data Scientist', 'Data Visualization', 70, 3, 5, 'Python', 'Communicating insights effectively'],
        ['Data Scientist', 'Deep Learning', 70, 4, 6, 'Machine Learning', 'Advanced neural network techniques'],
        
        // Full Stack Developer
        ['Full Stack Developer', 'HTML/CSS', 85, 4, 1, NULL, 'Foundation of web development'],
        ['Full Stack Developer', 'JavaScript', 90, 5, 2, 'HTML/CSS', 'Core language for web interactivity'],
        ['Full Stack Developer', 'React/Vue', 80, 5, 3, 'JavaScript', 'Modern frontend frameworks'],
        ['Full Stack Developer', 'Node.js', 80, 5, 4, 'JavaScript', 'Server-side JavaScript runtime'],
        ['Full Stack Developer', 'SQL/Database', 75, 4, 5, NULL, 'Data storage and retrieval'],
        ['Full Stack Developer', 'Git', 70, 3, 6, NULL, 'Version control essentials'],
        
        // Frontend Developer
        ['Frontend Developer', 'HTML/CSS', 90, 5, 1, NULL, 'Building blocks of web pages'],
        ['Frontend Developer', 'JavaScript', 85, 5, 2, 'HTML/CSS', 'Making pages interactive'],
        ['Frontend Developer', 'React/Vue', 85, 5, 3, 'JavaScript', 'Component-based development'],
        ['Frontend Developer', 'UI/UX Design', 70, 4, 4, NULL, 'Creating user-friendly interfaces'],
        ['Frontend Developer', 'Responsive Design', 80, 4, 5, 'HTML/CSS', 'Mobile-first development'],
        
        // Backend Developer
        ['Backend Developer', 'Python', 80, 4, 1, NULL, 'Popular backend language'],
        ['Backend Developer', 'SQL/Database', 85, 5, 2, NULL, 'Database design and queries'],
        ['Backend Developer', 'REST APIs', 85, 5, 3, 'Python', 'Building web services'],
        ['Backend Developer', 'Authentication', 75, 4, 4, 'REST APIs', 'Security implementation'],
        ['Backend Developer', 'Server Management', 70, 3, 5, NULL, 'Deployment and maintenance'],
        
        // DevOps Engineer
        ['DevOps Engineer', 'Linux/Shell', 85, 5, 1, NULL, 'Command line proficiency'],
        ['DevOps Engineer', 'Docker', 85, 5, 2, 'Linux/Shell', 'Containerization'],
        ['DevOps Engineer', 'CI/CD', 80, 5, 3, 'Docker', 'Automated pipelines'],
        ['DevOps Engineer', 'Cloud Platforms', 85, 5, 4, NULL, 'AWS/GCP/Azure expertise'],
        ['DevOps Engineer', 'Git', 80, 4, 5, NULL, 'Version control mastery'],
        
        // Mobile Developer
        ['Mobile Developer', 'JavaScript', 80, 4, 1, NULL, 'React Native foundation'],
        ['Mobile Developer', 'React Native/Flutter', 85, 5, 2, 'JavaScript', 'Cross-platform development'],
        ['Mobile Developer', 'Mobile UI Design', 75, 4, 3, NULL, 'Platform design guidelines'],
        ['Mobile Developer', 'API Integration', 80, 4, 4, 'JavaScript', 'Connecting to backends'],
        
        // Cybersecurity
        ['Cybersecurity Analyst', 'Network Security', 85, 5, 1, NULL, 'Protecting network infrastructure'],
        ['Cybersecurity Analyst', 'Linux/Shell', 80, 4, 2, NULL, 'System administration'],
        ['Cybersecurity Analyst', 'Ethical Hacking', 75, 4, 3, 'Network Security', 'Penetration testing'],
        ['Cybersecurity Analyst', 'Security Tools', 80, 5, 4, NULL, 'SIEM, firewalls, IDS'],
        ['Cybersecurity Analyst', 'Risk Assessment', 70, 3, 5, NULL, 'Threat analysis'],
        
        // AI/ML Engineer
        ['AI/ML Engineer', 'Python', 90, 5, 1, NULL, 'Primary ML programming language'],
        ['AI/ML Engineer', 'Mathematics', 85, 5, 2, NULL, 'Linear algebra, calculus, probability'],
        ['AI/ML Engineer', 'Machine Learning', 90, 5, 3, 'Python,Mathematics', 'Core ML algorithms'],
        ['AI/ML Engineer', 'Deep Learning', 85, 5, 4, 'Machine Learning', 'Neural networks'],
        ['AI/ML Engineer', 'TensorFlow/PyTorch', 80, 4, 5, 'Deep Learning', 'ML frameworks'],
    ];
    
    $stmt = $pdo->prepare("INSERT INTO required_skills (career_path, skill_name, required_level, weight, priority, depends_on, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach($skills as $s) {
        $stmt->execute($s);
    }
    
    // Quiz Questions - Python
    $python_questions = [
        ['Python', 'What is the output of: print(type([]))', 'int', 'str', 'list', 'tuple', 'C', 1, 'Square brackets create a list'],
        ['Python', 'Which keyword is used to define a function?', 'func', 'define', 'def', 'function', 'C', 1, 'def is used to define functions in Python'],
        ['Python', 'What does len([1,2,3]) return?', '2', '3', '4', 'Error', 'B', 1, 'len() returns the number of items'],
        ['Python', 'How do you start a comment in Python?', '//', '/*', '#', '--', 'C', 1, 'Hash symbol starts a comment'],
        ['Python', 'What is a dictionary in Python?', 'List of items', 'Key-value pairs', 'Ordered sequence', 'Immutable list', 'B', 2, 'Dictionaries store key-value pairs'],
        ['Python', 'What does range(5) produce?', '1 to 5', '0 to 5', '0 to 4', '1 to 4', 'C', 2, 'range(n) produces 0 to n-1'],
        ['Python', 'Which is used for list comprehension?', '{}', '()', '[]', '<>', 'C', 2, 'Square brackets with expression'],
        ['Python', 'What is __init__ in a class?', 'Destructor', 'Constructor', 'Iterator', 'Generator', 'B', 3, 'Constructor initializes object'],
        ['Python', 'What is a decorator in Python?', 'Class type', 'Function wrapper', 'Variable type', 'Loop structure', 'B', 3, 'Decorators modify function behavior'],
        ['Python', 'What does *args mean?', 'Required arguments', 'Variable positional args', 'Error handler', 'Import statement', 'B', 3, '*args accepts variable number of arguments'],
    ];
    
    // Quiz Questions - JavaScript
    $js_questions = [
        ['JavaScript', 'Which keyword declares a constant?', 'var', 'let', 'const', 'constant', 'C', 1, 'const creates unchangeable binding'],
        ['JavaScript', 'What is console.log() used for?', 'Creating elements', 'Debugging output', 'User input', 'File operations', 'B', 1, 'Outputs to browser console'],
        ['JavaScript', 'How do you create an array?', '(1,2,3)', '[1,2,3]', '{1,2,3}', '<1,2,3>', 'B', 1, 'Square brackets create arrays'],
        ['JavaScript', 'What does === compare?', 'Value only', 'Type only', 'Value and type', 'Reference', 'C', 2, 'Strict equality checks both'],
        ['JavaScript', 'What is a Promise?', 'Variable type', 'Async operation handler', 'Loop structure', 'Class type', 'B', 2, 'Promises handle async operations'],
        ['JavaScript', 'What does async/await do?', 'Create loops', 'Handle async code', 'Define classes', 'Import modules', 'B', 2, 'Simplifies Promise handling'],
        ['JavaScript', 'What is the DOM?', 'Data Object Model', 'Document Object Model', 'Direct Output Method', 'Data Order Manager', 'B', 2, 'Browser page representation'],
        ['JavaScript', 'What is closure?', 'Loop type', 'Function with scope', 'Error handler', 'Class method', 'B', 3, 'Function retaining outer scope'],
        ['JavaScript', 'What is event bubbling?', 'Animation effect', 'Event propagation up', 'Error handling', 'Memory management', 'B', 3, 'Events propagate from child to parent'],
        ['JavaScript', 'What is the spread operator?', '...', '***', '%%%', '&&&', 'A', 3, 'Three dots expand iterables'],
    ];
    
    // Quiz Questions - SQL
    $sql_questions = [
        ['SQL/Database', 'What does SELECT do?', 'Delete data', 'Retrieve data', 'Update data', 'Create table', 'B', 1, 'SELECT retrieves data from tables'],
        ['SQL/Database', 'Which clause filters results?', 'ORDER BY', 'GROUP BY', 'WHERE', 'HAVING', 'C', 1, 'WHERE filters rows'],
        ['SQL/Database', 'What is a PRIMARY KEY?', 'Any column', 'Unique identifier', 'Foreign reference', 'Index type', 'B', 1, 'Uniquely identifies each row'],
        ['SQL/Database', 'What does JOIN do?', 'Split tables', 'Combine tables', 'Delete rows', 'Create index', 'B', 2, 'Combines data from multiple tables'],
        ['SQL/Database', 'What is normalization?', 'Adding duplicates', 'Reducing redundancy', 'Creating indexes', 'Backing up data', 'B', 2, 'Organizing data to reduce redundancy'],
        ['SQL/Database', 'What does GROUP BY do?', 'Filters rows', 'Sorts results', 'Aggregates data', 'Joins tables', 'C', 2, 'Groups rows for aggregation'],
        ['SQL/Database', 'What is an INDEX?', 'Table copy', 'Speed optimization', 'Data backup', 'Error log', 'B', 2, 'Speeds up data retrieval'],
        ['SQL/Database', 'What is a transaction?', 'Single query', 'Unit of work', 'Table type', 'User session', 'B', 3, 'Atomic unit of database operations'],
        ['SQL/Database', 'What does ACID stand for?', 'Add, Create, Insert, Delete', 'Atomicity, Consistency, Isolation, Durability', 'Access, Control, Index, Data', 'None of these', 'B', 3, 'Database transaction properties'],
        ['SQL/Database', 'What is a stored procedure?', 'Saved query', 'Reusable code block', 'Backup method', 'Table type', 'B', 3, 'Pre-compiled SQL statements'],
    ];
    
    // Quiz Questions - HTML/CSS
    $html_questions = [
        ['HTML/CSS', 'What does HTML stand for?', 'Hyper Text Markup Language', 'High Tech Modern Language', 'Home Tool Markup Language', 'None', 'A', 1, 'HyperText Markup Language'],
        ['HTML/CSS', 'Which tag creates a link?', '<link>', '<a>', '<href>', '<url>', 'B', 1, 'Anchor tag creates hyperlinks'],
        ['HTML/CSS', 'What is CSS used for?', 'Adding logic', 'Styling elements', 'Server requests', 'Database queries', 'B', 1, 'CSS styles HTML elements'],
        ['HTML/CSS', 'Which property changes text color?', 'text-color', 'font-color', 'color', 'text-style', 'C', 1, 'color property sets text color'],
        ['HTML/CSS', 'What is flexbox?', 'Image format', 'Layout module', 'Animation type', 'Font style', 'B', 2, 'Flexible box layout module'],
        ['HTML/CSS', 'What does z-index control?', 'Font size', 'Element stacking', 'Animation speed', 'Border width', 'B', 2, 'Controls element stacking order'],
        ['HTML/CSS', 'What is the box model?', 'Image container', 'Layout concept', 'Animation type', 'Color scheme', 'B', 2, 'Content, padding, border, margin'],
        ['HTML/CSS', 'What is responsive design?', 'Fast loading', 'Adapts to screen sizes', 'Colorful design', 'Interactive elements', 'B', 2, 'Adapts layout to device screen'],
        ['HTML/CSS', 'What is a CSS Grid?', '2D layout system', 'Table style', 'Animation framework', 'Color palette', 'A', 3, 'Two-dimensional layout system'],
        ['HTML/CSS', 'What are CSS variables?', 'JavaScript variables', 'Reusable values', 'HTML attributes', 'Media queries', 'B', 3, 'Custom properties for reuse'],
    ];
    
    // Quiz Questions - Machine Learning
    $ml_questions = [
        ['Machine Learning', 'What is supervised learning?', 'No labels needed', 'Learning with labeled data', 'Self-learning', 'Random learning', 'B', 1, 'Uses labeled training data'],
        ['Machine Learning', 'What is a feature in ML?', 'Output variable', 'Input variable', 'Algorithm name', 'Model type', 'B', 1, 'Input variable used for prediction'],
        ['Machine Learning', 'What is overfitting?', 'Underfitting model', 'Model too complex', 'Perfect accuracy', 'Fast training', 'B', 2, 'Model memorizes training data'],
        ['Machine Learning', 'What is cross-validation?', 'Data cleaning', 'Model evaluation technique', 'Feature selection', 'Data visualization', 'B', 2, 'Splitting data for evaluation'],
        ['Machine Learning', 'What is gradient descent?', 'Data visualization', 'Optimization algorithm', 'Feature engineering', 'Model deployment', 'B', 2, 'Optimizes model parameters'],
        ['Machine Learning', 'What is regularization?', 'Data cleaning', 'Prevents overfitting', 'Improves speed', 'Creates features', 'B', 2, 'Prevents model overfitting'],
        ['Machine Learning', 'What is a neural network?', 'Database type', 'Brain-inspired model', 'File format', 'API type', 'B', 3, 'Layered learning model'],
        ['Machine Learning', 'What is backpropagation?', 'Forward pass', 'Error propagation', 'Data loading', 'Model saving', 'B', 3, 'Gradient calculation in neural networks'],
        ['Machine Learning', 'What is ensemble learning?', 'Single model', 'Combining multiple models', 'Data augmentation', 'Feature reduction', 'B', 3, 'Combining multiple models'],
        ['Machine Learning', 'What is transfer learning?', 'Moving data', 'Reusing pretrained models', 'Copying code', 'Sharing datasets', 'B', 3, 'Leveraging pretrained models'],
    ];
    
    // Insert all quiz questions
    $stmt = $pdo->prepare("INSERT INTO quiz_questions (skill_name, question, option_a, option_b, option_c, option_d, correct_answer, difficulty, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $all_questions = array_merge($python_questions, $js_questions, $sql_questions, $html_questions, $ml_questions);
    foreach($all_questions as $q) {
        $stmt->execute($q);
    }
    
    // Insert resources
    $resources = [
        ['Python', 'Python for Everybody', 'https://www.coursera.org/specializations/python', 'course', 'Coursera', 'beginner'],
        ['Python', 'Automate the Boring Stuff', 'https://automatetheboringstuff.com/', 'tutorial', 'Al Sweigart', 'beginner'],
        ['Python', 'Real Python Tutorials', 'https://realpython.com/', 'tutorial', 'Real Python', 'intermediate'],
        ['JavaScript', 'JavaScript30', 'https://javascript30.com/', 'course', 'Wes Bos', 'beginner'],
        ['JavaScript', 'Eloquent JavaScript', 'https://eloquentjavascript.net/', 'book', 'Marijn Haverbeke', 'intermediate'],
        ['JavaScript', 'MDN JavaScript Guide', 'https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide', 'tutorial', 'MDN', 'beginner'],
        ['SQL/Database', 'SQLBolt', 'https://sqlbolt.com/', 'tutorial', 'SQLBolt', 'beginner'],
        ['SQL/Database', 'SQL for Data Science', 'https://www.coursera.org/learn/sql-for-data-science', 'course', 'Coursera', 'beginner'],
        ['HTML/CSS', 'freeCodeCamp Responsive Design', 'https://www.freecodecamp.org/learn/2022/responsive-web-design/', 'course', 'freeCodeCamp', 'beginner'],
        ['HTML/CSS', 'CSS-Tricks', 'https://css-tricks.com/', 'tutorial', 'CSS-Tricks', 'intermediate'],
        ['Machine Learning', 'Machine Learning by Stanford', 'https://www.coursera.org/learn/machine-learning', 'course', 'Coursera', 'intermediate'],
        ['Machine Learning', 'Fast.ai', 'https://course.fast.ai/', 'course', 'Fast.ai', 'intermediate'],
        ['Statistics', 'Khan Academy Statistics', 'https://www.khanacademy.org/math/statistics-probability', 'course', 'Khan Academy', 'beginner'],
        ['Statistics', 'StatQuest', 'https://www.youtube.com/c/joshstarmer', 'video', 'YouTube', 'beginner'],
        ['React/Vue', 'React Official Tutorial', 'https://react.dev/learn', 'tutorial', 'React.dev', 'beginner'],
        ['React/Vue', 'Vue.js Guide', 'https://vuejs.org/guide/introduction.html', 'tutorial', 'Vue.js', 'beginner'],
        ['Git', 'Learn Git Branching', 'https://learngitbranching.js.org/', 'tutorial', 'Interactive', 'beginner'],
        ['Docker', 'Docker Getting Started', 'https://docs.docker.com/get-started/', 'tutorial', 'Docker', 'beginner'],
        ['Deep Learning', 'Deep Learning Specialization', 'https://www.coursera.org/specializations/deep-learning', 'course', 'Coursera', 'advanced'],
        ['Node.js', 'Node.js Documentation', 'https://nodejs.org/en/learn', 'tutorial', 'Node.js', 'beginner'],
    ];
    
    $stmt = $pdo->prepare("INSERT INTO resources (skill_name, title, url, type, platform, difficulty) VALUES (?, ?, ?, ?, ?, ?)");
    foreach($resources as $r) {
        $stmt->execute($r);
    }
    
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Setup Complete</title>";
    echo "<link rel='stylesheet' href='assets/css/style.css'></head><body>";
    echo "<div class='content-container' style='text-align: center; padding-top: 60px;'>";
    echo "<div class='card' style='max-width: 600px; margin: 0 auto;'>";
    echo "<h1 style='color: var(--success);'>✅ Database Setup Complete!</h1>";
    echo "<p style='color: var(--text-secondary); margin: 20px 0;'>All tables have been created with the new smart features:</p>";
    echo "<ul style='text-align: left; color: var(--text-secondary); line-height: 2;'>";
    echo "<li>✓ Career paths with salary data</li>";
    echo "<li>✓ Weighted skills with dependencies</li>";
    echo "<li>✓ Quiz questions for skill assessment</li>";
    echo "<li>✓ Progress tracking history</li>";
    echo "<li>✓ Learning resources</li>";
    echo "</ul>";
    echo "<a href='index.php' class='btn btn-primary' style='margin-top: 20px;'>Go to Homepage →</a>";
    echo "</div></div></body></html>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
