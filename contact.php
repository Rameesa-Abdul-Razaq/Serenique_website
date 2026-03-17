<?php
/**
 * Contact Us Page
 */
require_once __DIR__ . '/session.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // In production, you would send an email here
        $success = 'Thank you for your message! We will get back to you soon.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Serenique | Contact Us</title>
    <style>
        .contact-form { max-width: 600px; margin: 0 auto; }
        .contact-info { background: var(--card-bg, #f8f5f2); padding: 2rem; border-radius: 12px; }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <main class="container py-5">
        <h1 class="text-center mb-5" style="font-family: 'Playfair Display', serif;">Contact Us</h1>
        
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="contact-info h-100">
                    <h3>Get in Touch</h3>
                    <p>We'd love to hear from you! Whether you have a question about our products, need help with an order, or just want to say hello.</p>
                    
                    <h5 class="mt-4">📍 Address</h5>
                    <p>Serenique Beauty<br>123 Beauty Lane<br>London, UK</p>
                    
                    <h5>📧 Email</h5>
                    <p>support@serenique.com</p>
                    
                    <h5>📞 Phone</h5>
                    <p>+44 123 456 7890</p>
                    
                    <h5>🕐 Hours</h5>
                    <p>Monday - Friday: 9am - 6pm<br>Saturday: 10am - 4pm</p>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="contact-form">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo e($success); ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo e($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   value="<?php echo e($_POST['name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   value="<?php echo e($_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required><?php echo e($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

