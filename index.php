<?php
require_once 'includes/config.php';  // starts session + defines SITE_URL
// If already logged in → redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!-- index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= SITE_NAME ?> - Book Your Appointment Easily</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #4a6cf7;
            --primary-light: #6a85f9;
            --primary-dark: #3a56d4;
            --secondary: #6c63ff;
            --accent: #ff6b9d;
            --accent-light: #ff8ab0;
            --light: #f8f9ff;
            --dark: #1e2a4a;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --success: #28a745;
            --danger: #dc3545;
            --border-radius: 16px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --box-shadow-lg: 0 20px 50px rgba(0, 0, 0, 0.12);
            --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7ff 0%, #e6ecff 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 1rem 0;
            transition: var(--transition);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            font-weight: 500;
            color: var(--dark) !important;
            margin: 0 0.5rem;
            transition: var(--transition);
            border-radius: 8px;
            padding: 0.5rem 1rem !important;
        }

        .nav-link:hover {
            background: rgba(74, 108, 247, 0.1);
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(74, 108, 247, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(108, 99, 255, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 107, 157, 0.1) 0%, transparent 50%);
            z-index: -1;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .display-3 {
            font-weight: 800;
            margin-top: 6rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        .lead {
            font-size: 1.3rem;
            color: var(--gray);
            margin-bottom: 2.5rem;
            font-weight: 400;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            border-radius: 50px;
            padding: 16px 40px;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 10px 25px rgba(74, 108, 247, 0.3);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn-primary-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .btn-primary-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(74, 108, 247, 0.4);
        }

        .btn-primary-custom:hover::before {
            left: 100%;
        }

        /* Stats Section */
        .stats-section {
            padding: 5rem 0;
            background: white;
            border-radius: var(--border-radius);
            margin: 2rem 0;
            box-shadow: var(--box-shadow);
        }

        .stat-item {
            text-align: center;
            padding: 1.5rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--gray);
            font-weight: 500;
        }

        /* Features Section */
        .features-section {
            padding: 6rem 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-title p {
            font-size: 1.2rem;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            height: 100%;
            border: 1px solid var(--gray-light);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--box-shadow-lg);
        }

        .feature-icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
            transition: var(--transition);
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1);
        }

        .feature-card h4 {
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
            font-size: 1.5rem;
        }

        .feature-card p {
            color: var(--gray);
            font-size: 1.1rem;
        }

        /* How It Works */
        .how-it-works {
            padding: 6rem 0;
            background: linear-gradient(135deg, #f8f9ff 0%, #e6ecff 100%);
            border-radius: var(--border-radius);
            margin: 4rem 0;
        }

        .step-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            text-align: center;
            box-shadow: var(--box-shadow);
            position: relative;
            transition: var(--transition);
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 auto 1.5rem;
            box-shadow: 0 5px 15px rgba(74, 108, 247, 0.3);
        }

        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow-lg);
        }

        /* Testimonials */
        .testimonials-section {
            padding: 6rem 0;
        }

        .testimonial-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--box-shadow);
            margin: 1rem;
            transition: var(--transition);
            border-left: 5px solid var(--primary);
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow-lg);
        }

        .testimonial-text {
            font-style: italic;
            color: var(--gray);
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            line-height: 1.7;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            margin-right: 1rem;
        }

        .author-info h5 {
            margin: 0;
            font-weight: 600;
        }

        .author-info p {
            margin: 0;
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* CTA Section */
        .cta-section {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: var(--border-radius);
            color: white;
            text-align: center;
            margin: 4rem 0;
        }

        .cta-section h2 {
            font-weight: 800;
            margin-bottom: 1.5rem;
            font-size: 2.5rem;
        }

        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
        }

        .btn-light-custom {
            background: white;
            color: var(--primary);
            border: none;
            border-radius: 50px;
            padding: 16px 40px;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .btn-light-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            color: var(--primary-dark);
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 4rem 0 2rem;
            margin-top: 4rem;
        }

        .footer-links h5 {
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: white;
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: white;
            padding-left: 5px;
        }

        .copyright {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 2rem;
            margin-top: 3rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Animations */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .display-3 {
                font-size: 2.5rem;
            }
            
            .lead {
                font-size: 1.1rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .feature-card, .step-card, .testimonial-card {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#"><?= SITE_NAME ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#features">Features</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#how-it-works">How It Works</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#testimonials">Testimonials</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="auth/login.php">Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<div class="hero-section">
    <div class="hero-bg"></div>
    <div class="container">
        <div class="hero-content animate-on-scroll">
            <h1 class="display-3 fw-bold">
                Welcome to <span class="text-primary"><?= SITE_NAME ?></span>
            </h1>
            <p class="lead">
                Experience healthcare reimagined. Book appointments with top doctors instantly, 
                manage your health records, and receive personalized care - all in one platform.
            </p>
            <a href="auth/login.php" class="btn btn-primary-custom text-white shadow-lg">
                <i class="fas fa-sign-in-alt me-2"></i> Get Started Today
            </a>
            
            <!-- Stats Section -->
            <div class="stats-section mt-5 animate-on-scroll">
                <div class="row">
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Expert Doctors</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-number">10K+</div>
                            <div class="stat-label">Happy Patients</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-number">15+</div>
                            <div class="stat-label">Specialties</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Support</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<section class="features-section" id="features">
    <div class="container">
        <div class="section-title animate-on-scroll">
            <h2>Why Choose <?= SITE_NAME ?></h2>
            <p>We're revolutionizing healthcare with technology that puts patients first</p>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                    <h4>Easy Booking</h4>
                    <p>Book appointments in seconds with our intuitive interface. View doctor availability in real-time and select the perfect slot for you.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon"><i class="fas fa-clock"></i></div>
                    <h4>Save Time</h4>
                    <p>No more waiting in lines or on hold. Arrive exactly at your appointment time and get seen by your doctor promptly.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon"><i class="fas fa-user-md"></i></div>
                    <h4>Expert Doctors</h4>
                    <p>Access our network of board-certified specialists across multiple medical fields with verified credentials and patient reviews.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon"><i class="fas fa-bell"></i></div>
                    <h4>Smart Reminders</h4>
                    <p>Receive automated appointment reminders via SMS and email, so you never miss an important healthcare visit.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon"><i class="fas fa-file-medical"></i></div>
                    <h4>Digital Records</h4>
                    <p>Access your medical history, prescriptions, and test results securely from anywhere, at any time.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h4>Secure & Private</h4>
                    <p>Your health data is protected with enterprise-grade security and strict privacy controls compliant with healthcare regulations.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="how-it-works" id="how-it-works">
    <div class="container">
        <div class="section-title animate-on-scroll">
            <h2>How It Works</h2>
            <p>Getting healthcare has never been easier with our simple 4-step process</p>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="step-card animate-on-scroll">
                    <div class="step-number">1</div>
                    <h4>Create Account</h4>
                    <p>Sign up in less than a minute with your basic information</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="step-card animate-on-scroll">
                    <div class="step-number">2</div>
                    <h4>Find a Doctor</h4>
                    <p>Browse specialists by specialty, location, or availability</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="step-card animate-on-scroll">
                    <div class="step-number">3</div>
                    <h4>Book Appointment</h4>
                    <p>Select your preferred date and time with instant confirmation</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="step-card animate-on-scroll">
                    <div class="step-number">4</div>
                    <h4>Visit Doctor</h4>
                    <p>Arrive at your appointment and receive quality care</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials-section" id="testimonials">
    <div class="container">
        <div class="section-title animate-on-scroll">
            <h2>What Our Patients Say</h2>
            <p>Don't just take our word for it - hear from our satisfied patients</p>
        </div>
        
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="testimonial-card animate-on-scroll">
                    <div class="testimonial-text">
                        "I've been using <?= SITE_NAME ?> for all my family's healthcare needs. Booking appointments is so simple, and the reminder system ensures we never miss a visit."
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">SD</div>
                        <div class="author-info">
                            <h5>Sarah Johnson</h5>
                            <p>Patient since 2022</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="testimonial-card animate-on-scroll">
                    <div class="testimonial-text">
                        "As a busy professional, I don't have time to wait on hold. With <?= SITE_NAME ?>, I can book my appointments during my commute and get seen right on time."
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">MR</div>
                        <div class="author-info">
                            <h5>Michael Roberts</h5>
                            <p>Patient since 2021</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="testimonial-card animate-on-scroll">
                    <div class="testimonial-text">
                        "The digital records feature has been a lifesaver. I can access my test results and share them with specialists without worrying about lost paperwork."
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">EP</div>
                        <div class="author-info">
                            <h5>Emily Parker</h5>
                            <p>Patient since 2023</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="animate-on-scroll">
            <h2>Ready to Take Control of Your Healthcare?</h2>
            <p>Join thousands of patients who are already experiencing better healthcare through technology</p>
            <a href="auth/login.php" class="btn btn-light-custom">
                <i class="fas fa-user-plus me-2"></i> Create Your Account Now
            </a>
        </div>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h3 class="navbar-brand"><?= SITE_NAME ?></h3>
                <p class="mt-3" style="color: rgba(255,255,255,0.7);">
                    Transforming healthcare through innovative technology that connects patients with quality medical care.
                </p>
            </div>
            <div class="col-lg-2 col-md-4 mb-4">
                <div class="footer-links">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#testimonials">Testimonials</a></li>
                        <li><a href="auth/login.php">Login</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="footer-links">
                    <h5>Specialties</h5>
                    <ul>
                        <li><a href="#">Cardiology</a></li>
                        <li><a href="#">Dermatology</a></li>
                        <li><a href="#">Pediatrics</a></li>
                        <li><a href="#">Orthopedics</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="footer-links">
                    <h5>Contact Us</h5>
                    <ul>
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Healthcare Ave, Medical City</li>
                        <li><i class="fas fa-phone me-2"></i> (555) 123-4567</li>
                        <li><i class="fas fa-envelope me-2"></i> info@<?= strtolower(SITE_NAME) ?>.com</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>© <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved. • Committed to your health and privacy</p>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Animation on scroll
    document.addEventListener('DOMContentLoaded', function() {
        const animatedElements = document.querySelectorAll('.animate-on-scroll');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, { threshold: 0.1 });
        
        animatedElements.forEach(element => {
            observer.observe(element);
        });
        
        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.08)';
            }
        });
    });
</script>
</body>
</html>