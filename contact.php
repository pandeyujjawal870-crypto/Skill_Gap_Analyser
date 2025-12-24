<?php 
session_start();
$page_title = "Contact - SkillGap Analyzer";
$message_sent = false;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real app, you'd send an email here
    $message_sent = true;
}

include 'includes/header.php'; 
?>

<div class="content-container" style="max-width: 640px;">
  <div class="page-header">
    <h1>Contact Us</h1>
    <p style="font-weight: var(--font-light);">Have questions or feedback? We'd love to hear from you!</p>
  </div>
  
  <div class="card fade-in">
    <?php if($message_sent): ?>
      <div class="alert alert-success" style="margin-bottom: 24px;">
        <span>✓</span>
        <span>Thank you for your message! We'll get back to you soon.</span>
      </div>
    <?php endif; ?>
    
    <form method="POST" style="display: flex; flex-direction: column; gap: 20px;">
      <div class="form-group" style="margin-bottom: 0;">
        <label for="name">Your Name</label>
        <input type="text" id="name" name="name" class="input" placeholder="Enter name" required autocomplete="name">
      </div>
      
      <div class="form-group" style="margin-bottom: 0;">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" class="input" placeholder="Enter valid email" required autocomplete="email">
      </div>
      
      <div class="form-group" style="margin-bottom: 0;">
        <label for="subject">Subject</label>
        <select id="subject" name="subject" class="input" required>
          <option value="">Select a topic</option>
          <option value="feedback">General Feedback</option>
          <option value="bug">Report a Bug</option>
          <option value="feature">Feature Request</option>
          <option value="career">Career Path Suggestion</option>
          <option value="other">Other</option>
        </select>
      </div>
      
      <div class="form-group" style="margin-bottom: 0;">
        <label for="message">Message</label>
        <textarea id="message" name="message" class="input" rows="5" placeholder="Tell us what's on your mind..." required></textarea>
      </div>
      
      <button type="submit" class="btn btn-primary">Send Message</button>
    </form>
  </div>
  
  <div class="insight-box fade-in" style="margin-top: 24px;">
    <div class="insight-title">
      <span>📧</span>
      <span>Quick Response</span>
    </div>
    <p style="font-size: 14px;">
      We typically respond within 24-48 hours. For urgent matters, please mention it in your subject line.
    </p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
