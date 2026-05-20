<?php
include 'includes/db.php';

if (isset($_POST['send'])) {
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        $success = "Your message has been sent successfully!";
    } else {
        $error = "Failed to send message. Please try again.";
    }
}

session_start();

if (isset($_SESSION['admin'])) { header("Location: admin/dashboard.php"); exit(); }
if (isset($_SESSION['staff'])) { header("Location: staff/dashboard.php"); exit(); }
if (isset($_SESSION['user']))  { header("Location: user/dashboard.php");  exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ODM Library Management System</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --primary: #1a3a5c;
    --primary-dark: #0f2540;
    --accent: #0ea5e9;
    --accent-dark: #0284c7;
    --success: #10b981;
    --text: #1e293b;
    --text-muted: #64748b;
    --bg: #f0f4f8;
    --border: #e2e8f0;
    --surface: #fff;
}

body {
    font-family: 'Inter', Arial, sans-serif;
    color: var(--text);
    line-height: 1.6;
}

/* ===== NAVBAR ===== */
nav {
    background: var(--primary-dark);
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 20px rgba(0,0,0,0.3);
}

.nav-inner {
    max-width: 1200px;
    margin: auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 64px;
}

.nav-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.nav-brand-icon {
    width: 36px;
    height: 36px;
    background: var(--accent);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.nav-brand-text h2 {
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.2;
}

.nav-brand-text span {
    color: rgba(255,255,255,0.5);
    font-size: 11px;
    font-weight: 400;
    letter-spacing: 0.5px;
}

.nav-links {
    list-style: none;
    display: flex;
    align-items: center;
    gap: 4px;
}

.nav-links a {
    color: rgba(255,255,255,0.75);
    text-decoration: none;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
}

.nav-links a:hover {
    color: #fff;
    background: rgba(255,255,255,0.08);
}

.nav-links .btn-login {
    background: var(--accent);
    color: #fff;
    padding: 8px 18px;
    border-radius: 8px;
    font-weight: 600;
}

.nav-links .btn-login:hover {
    background: var(--accent-dark);
}

.menu-toggle {
    display: none;
    background: none;
    border: none;
    color: #fff;
    font-size: 22px;
    cursor: pointer;
    padding: 4px;
}

/* ===== HERO ===== */
/*.hero {
    position: relative;
    min-height: 92vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    background: linear-gradient(135deg, rgba(15,37,64,0.92) 0%, rgba(26,58,92,0.88) 100%),
                url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=1600&q=80') center/cover no-repeat;
    color: #fff;
    padding: 60px 24px;
}*/
.hero {
        height: 90vh;
        background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                    url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f');
        background-size: cover;
        background-position: center;
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }


.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(14,165,233,0.2);
    border: 1px solid rgba(14,165,233,0.4);
    color: #7dd3fc;
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 20px;
}

.hero h1 {
    font-size: clamp(2rem, 4vw, 3.4rem);
    font-weight: 800;
    line-height: 1.15;
    max-width: 760px;
    margin: 0 auto 18px;
    letter-spacing: -0.5px;
}

.hero h1 span {
    color: var(--accent);
}

.hero p {
    font-size: 17px;
    color: rgba(255,255,255,0.72);
    max-width: 560px;
    margin: 0 auto 36px;
    font-weight: 400;
}

.hero-btns {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
}

.hero-btn-primary {
    background: var(--accent);
    color: #fff;
    padding: 14px 28px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 700;
    font-size: 15px;
    transition: all 0.2s;
    box-shadow: 0 4px 16px rgba(14,165,233,0.35);
}

.hero-btn-primary:hover {
    background: var(--accent-dark);
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(14,165,233,0.45);
}

.hero-btn-ghost {
    background: rgba(255,255,255,0.1);
    color: #fff;
    padding: 14px 28px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    border: 1px solid rgba(255,255,255,0.25);
    transition: all 0.2s;
}

.hero-btn-ghost:hover {
    background: rgba(255,255,255,0.18);
}

.hero-stats {
    display: flex;
    gap: 40px;
    justify-content: center;
    margin-top: 60px;
    flex-wrap: wrap;
}

.hero-stat {
    text-align: center;
}

.hero-stat strong {
    display: block;
    font-size: 28px;
    font-weight: 800;
    color: #fff;
}

.hero-stat span {
    font-size: 13px;
    color: rgba(255,255,255,0.55);
    font-weight: 400;
}

/* ===== SECTIONS ===== */
section {
    padding: 80px 24px;
}

.section-inner {
    max-width: 1200px;
    margin: auto;
}

.section-label {
    display: inline-block;
    background: rgba(14,165,233,0.1);
    color: var(--accent-dark);
    padding: 5px 14px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 12px;
}

.section-title {
    font-size: clamp(1.6rem, 3vw, 2.4rem);
    font-weight: 800;
    color: var(--primary);
    line-height: 1.2;
    margin-bottom: 12px;
}

.section-sub {
    font-size: 15px;
    color: var(--text-muted);
    max-width: 560px;
    margin: 0 auto 48px;
}

/* ===== SERVICES ===== */
#services {
    background: var(--bg);
    text-align: center;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 24px;
}

.service-card {
    background: var(--surface);
    border-radius: 16px;
    padding: 32px 24px;
    border: 1px solid var(--border);
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transition: all 0.25s;
    text-align: left;
}

.service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 36px rgba(0,0,0,0.12);
    border-color: var(--accent);
}

.service-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 18px;
}

.service-card h3 {
    font-size: 17px;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 8px;
}

.service-card p {
    font-size: 14px;
    color: var(--text-muted);
    line-height: 1.6;
}

/* ===== ABOUT ===== */
#about {
    background: var(--surface);
}

.about-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 64px;
    align-items: center;
}

.about-img {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    position: relative;
}

.about-img img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    display: block;
}

.about-img-overlay {
    position: absolute;
    bottom: 24px;
    left: 24px;
    background: rgba(15,37,64,0.9);
    backdrop-filter: blur(8px);
    border-radius: 12px;
    padding: 14px 18px;
    color: #fff;
}

.about-img-overlay strong {
    display: block;
    font-size: 22px;
    font-weight: 800;
    color: var(--accent);
}

.about-img-overlay span {
    font-size: 12px;
    color: rgba(255,255,255,0.6);
}

.about-content .section-title {
    text-align: left;
}

.about-content .section-label {
    text-align: left;
}

.about-content p {
    font-size: 15px;
    color: var(--text-muted);
    margin-bottom: 16px;
    line-height: 1.7;
}

.about-features {
    list-style: none;
    margin-top: 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.about-features li {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 500;
    color: var(--text);
}

.about-features li span.check {
    width: 22px;
    height: 22px;
    background: rgba(16,185,129,0.15);
    color: #059669;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    flex-shrink: 0;
    font-weight: 700;
}

/* ===== CONTACT ===== */
#contact {
    background: var(--bg);
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 32px;
    max-width: 960px;
    margin: auto;
}

.contact-info {
    background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: 16px;
    padding: 36px 28px;
    color: #fff;
}

.contact-info h3 {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 8px;
}

.contact-info > p {
    font-size: 14px;
    color: rgba(255,255,255,0.6);
    margin-bottom: 28px;
}

.contact-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 20px;
}

.contact-item-icon {
    width: 36px;
    height: 36px;
    background: rgba(255,255,255,0.1);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.contact-item-text strong {
    display: block;
    font-size: 12px;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}

.contact-item-text span {
    font-size: 14px;
    color: #fff;
}

.contact-form-card {
    background: var(--surface);
    border-radius: 16px;
    padding: 36px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    border: 1px solid var(--border);
}

.contact-form-card h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 6px;
}

.contact-form-card > p {
    font-size: 14px;
    color: var(--text-muted);
    margin-bottom: 24px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.cf-group {
    margin-bottom: 16px;
}

.cf-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 6px;
}

.cf-group input,
.cf-group textarea {
    width: 100%;
    padding: 11px 14px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    color: var(--text);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: #f8fafc;
}

.cf-group input:focus,
.cf-group textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(14,165,233,0.1);
    background: #fff;
}

.cf-group textarea {
    resize: vertical;
    min-height: 110px;
}

.cf-submit {
    background: var(--primary);
    color: #fff;
    border: none;
    padding: 13px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    width: 100%;
    transition: all 0.2s;
    font-family: inherit;
}

.cf-submit:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 16px;
    font-weight: 500;
}

.alert-success {
    background: rgba(16,185,129,0.1);
    color: #059669;
    border: 1px solid rgba(16,185,129,0.25);
}

.alert-error {
    background: rgba(239,68,68,0.1);
    color: #dc2626;
    border: 1px solid rgba(239,68,68,0.25);
}

/* ===== FOOTER ===== */
footer {
    background: var(--primary-dark);
    color: rgba(255,255,255,0.75);
}

.footer-top {
    max-width: 1200px;
    margin: auto;
    padding: 56px 24px 40px;
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 48px;
}

.footer-brand h3 {
    color: #fff;
    font-size: 17px;
    font-weight: 700;
    margin-bottom: 10px;
}

.footer-brand p {
    font-size: 14px;
    line-height: 1.7;
    max-width: 320px;
}

.footer-col h4 {
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 16px;
}

.footer-col ul {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.footer-col ul li a {
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    font-size: 14px;
    transition: color 0.2s;
}

.footer-col ul li a:hover {
    color: var(--accent);
}

.footer-col p {
    font-size: 14px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.08);
    text-align: center;
    padding: 18px 24px;
    font-size: 13px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
    .about-grid { grid-template-columns: 1fr; gap: 32px; }
    .contact-grid { grid-template-columns: 1fr; }
    .footer-top { grid-template-columns: 1fr 1fr; }
    .form-row { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
    .nav-links { display: none; flex-direction: column; position: absolute; top: 64px; left: 0; right: 0; background: var(--primary-dark); padding: 12px; }
    .nav-links.open { display: flex; }
    .menu-toggle { display: block; }
    .hero h1 { font-size: 2rem; }
    .footer-top { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="nav-inner">
        <a href="#home" class="nav-brand">
            <div class="nav-brand-icon">📚</div>
            <div class="nav-brand-text">
                <h2>ODM</h2>
                <span>Library</span>
            </div>
        </a>

        <button class="menu-toggle" onclick="document.querySelector('.nav-links').classList.toggle('open')">☰</button>

        <ul class="nav-links" id="navLinks">
            <li><a href="#home">Home</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
            <li><a href="includes/login.php" class="btn-login">Login →</a></li>
        </ul>
    </div>
</nav>

<!-- HERO -->
<section id="home" style="padding:0;">
    <div class="hero">
        <div>
            <!--<div class="hero-badge">🎓 ODM Library</div>-->
            <h1>ODM Library Management System</h1>
<p>An integrated platform for managing books, members, staff, and borrowing activities efficiently.</p>
            <div class="hero-btns">
                <a href="includes/login.php" class="hero-btn-primary">Get Started →</a>
                <a href="#services" class="hero-btn-ghost">Explore Services</a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <strong>100+</strong>
                    <span>Books Available</span>
                </div>
                <div class="hero-stat">
                    <strong>50+</strong>
                    <span>Registered Members</span>
                </div>
                <div class="hero-stat">
                    <strong>24/7</strong>
                    <span>Online Access</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SERVICES -->
<section id="services">
    <div class="section-inner">
        <div style="text-align:center;">
            <span class="section-label">What We Offer</span>
            <h2 class="section-title">Library Services</h2>
            <p class="section-sub">Everything you need to manage and access academic resources efficiently.</p>
        </div>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">📖</div>
                <h3>Book Borrowing</h3>
                <p>Borrow books easily and return them on time. Get reminders before your due date to avoid fines.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">🔍</div>
                <h3>Catalog Search</h3>
                <p>Search our extensive catalog by title, author, or subject and instantly see availability status.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">👥</div>
                <h3>Member Management</h3>
                <p>Admins can register, update, and manage student and staff members from a central dashboard.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">📊</div>
                <h3>Analytics &amp; Reports</h3>
                <p>Track borrowing trends, overdue books, and fine collections through intuitive dashboards.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">⚡</div>
                <h3>Quick Returns</h3>
                <p>Staff can process book returns instantly and update availability in real-time.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">💳</div>
                <h3>Fine Management</h3>
                <p>Automatic fine calculation for overdue books with easy payment tracking and records.</p>
            </div>
        </div>
    </div>
</section>

<!-- ABOUT -->
<section id="about">
    <div class="section-inner">
        <div class="about-grid">
            <div class="about-img">
                <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=800&q=80" alt="Library">
                <div class="about-img-overlay">
                    <strong>Est. 1989</strong>
                    <span>ODM Library</span>
                </div>
            </div>
            <div class="about-content">
                <span class="section-label">About Us</span>
                <h2 class="section-title">Empowering Academic Excellence</h2>
                <p>The ODM Library Management System was built to bring modern efficiency to one of Uganda's leading institutions of higher learning.</p>
                <p>Our platform bridges the gap between students, staff, and administrators — providing a seamless experience for accessing and managing the university's rich collection of academic resources.</p>
                <ul class="about-features">
                    <li><span class="check">✓</span> Role-based access for Admins, Staff, and Members</li>
                    <li><span class="check">✓</span> Real-time book availability tracking</li>
                    <li><span class="check">✓</span> Automated borrow and return management</li>
                    <li><span class="check">✓</span> Fine tracking for overdue books</li>
                    <li><span class="check">✓</span> Complete member and staff registration</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT -->
<section id="contact">
    <div class="section-inner">
        <div style="text-align:center;">
            <span class="section-label">Get in Touch</span>
            <h2 class="section-title">Contact Us</h2>
            <p class="section-sub">Have a question or need support? We're here to help you.</p>
        </div>
        <div class="contact-grid">
            <div class="contact-info">
                <h3>Library Information</h3>
                <p>Reach out and we'll respond as soon as possible.</p>
                <div class="contact-item">
                    <div class="contact-item-icon">✉️</div>
                    <div class="contact-item-text">
                        <strong>Email</strong>
                        <span>library@odm.ac.ug</span>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-item-icon">📞</div>
                    <div class="contact-item-text">
                        <strong>Phone</strong>
                        <span>+256 700 000 000</span>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-item-icon">📍</div>
                    <div class="contact-item-text">
                        <strong>Location</strong>
                        <span>ODM Library, Uganda</span>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-item-icon">🕐</div>
                    <div class="contact-item-text">
                        <strong>Working Hours</strong>
                        <span>Mon – Fri, 8:00 AM – 5:00 PM</span>
                    </div>
                </div>
            </div>
            <div class="contact-form-card">
                <h3>Send Us a Message</h3>
                <p>Fill in the form and our team will get back to you.</p>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success">✓ <?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">✕ <?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-row">
                        <div class="cf-group">
                            <label>Your Name</label>
                            <input type="text" name="name" placeholder="Bit Boss" required>
                        </div>
                        <div class="cf-group">
                            <label>Email Address</label>
                            <input type="email" name="email" placeholder="bit.boss@odm.ac.ug" required>
                        </div>
                    </div>
                    <div class="cf-group">
                        <label>Message</label>
                        <textarea name="message" placeholder="How can we help you?" required></textarea>
                    </div>
                    <button type="submit" name="send" class="cf-submit">Send Message →</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-top">
        <div class="footer-brand">
            <h3>📚 ODM Library</h3>
            <p>The ODM Library Management System helps students and staff access books, manage borrowing records, and enhance the learning experience.</p>
        </div>
        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="includes/login.php">Login</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Contact</h4>
            <p>✉️ library@odm.ac.ug</p>
            <p>📞 +256 700 000 000</p>
            <p>📍 ODM Library</p>
            <p>🕐 Mon–Fri, 8AM–5PM</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© <?php echo date('Y'); ?> ODM Library Management System — All Rights Reserved</p>
    </div>
</footer>

<script>
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener("click", function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute("href"));
        if (target) target.scrollIntoView({ behavior: "smooth" });
    });
});
</script>
</body>
</html>
