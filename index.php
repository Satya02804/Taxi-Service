<?php
session_start();
include_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taxi Service - Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-image: url('https://images.unsplash.com/photo-1518614919089-568f0daa305b?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Nnx8aW5kaWFuJTIwdGF4aXxlbnwwfHwwfHx8MA%3D%3D');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .hero {
            height: 100vh;
            width: 100%;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                        url('https://images.unsplash.com/photo-1518783211485-10fd3bfb2ce2?auto=format&fit=crop&q=80') no-repeat center center;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 15px 30px;
            border-radius: 0 0 50px 50px;
            margin: 0 20px;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            margin: 10px 20px;
            border-radius: 50px;
            background-color: rgba(0, 0, 0, 0.9) !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .navbar-brand {
            color: #ffc107 !important;
            font-size: 24px;
            font-weight: bold;
        }

        .nav-link {
            color: white !important;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #ffc107 !important;
        }

        .hero-text {
            text-align: center;
            color: white;
            animation: fadeIn 1.5s ease-in;
        }

        .hero-text h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .hero-text p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        .btn-warning {
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .features {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            transition: transform 0.3s ease;
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-card i {
            font-size: 3rem;
            color: #ffc107;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            margin-bottom: 15px;
            color: #333;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .hero-text h1 {
                font-size: 2.5rem;
            }
            .feature-card {
                margin-bottom: 20px;
            }
        }

        html {
            scroll-behavior: smooth;
            overflow-x: hidden;
        }

        /* For Safari compatibility */
        body {
            -webkit-overflow-scrolling: touch;
        }

        /* Smooth transition for all elements */
        * {
            transition: all 0.3s ease-out;
        }

        /* Smooth scroll for anchor links */
        a[href^="#"] {
            scroll-margin-top: 100px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Taxi Service</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="driver/login.php">Driver</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user/register.php">Register</a>
                        
                    </li>
                    <li class="nav-item">
    <a class="nav-link" href="taxi-gallery.php">Our Fleet</a>
</li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto hero-text">
                    <h1>Welcome to Our Taxi Service</h1>
                    <p>Your reliable ride, anytime, anywhere.</p>
                    <a href="user/login.php" class="btn btn-warning btn-lg">
                        Get Started
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2>Why Choose Us?</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-clock"></i>
                        <h3>24/7 Service</h3>
                        <p>Available round the clock for your convenience</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Safe Rides</h3>
                        <p>Professional drivers and verified vehicles</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-wallet"></i>
                        <h3>Best Rates</h3>
                        <p>Competitive pricing with no hidden charges</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row">
                <!-- About Us -->
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">About Us</h5>
                    <p class="text-muted">We provide reliable and safe taxi services 24/7. Our mission is to ensure comfortable and timely transportation for all our customers.</p>
                </div>

                <!-- Quick Links -->
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="#features" class="text-muted text-decoration-none">Features</a></li>
                        <li class="mb-2"><a href="user/login.php" class="text-muted text-decoration-none">Login</a></li>
                        <li class="mb-2"><a href="user/register.php" class="text-muted text-decoration-none">Register</a></li>
                    </ul>
                </div>

                <!-- Social Media Links -->
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">Connect With Us</h5>
                    <div class="social-links">
                        <a href="https://facebook.com" class="text-light me-3" target="_blank">
                            <i class="fab fa-facebook-f fa-lg"></i>
                        </a>
                        <a href="https://twitter.com" class="text-light me-3" target="_blank">
                            <i class="fab fa-twitter fa-lg"></i>
                        </a>
                        <a href="https://instagram.com" class="text-light me-3" target="_blank">
                            <i class="fab fa-instagram fa-lg"></i>
                        </a>
                        <a href="https://linkedin.com" class="text-light" target="_blank">
                            <i class="fab fa-linkedin-in fa-lg"></i>
                        </a>
                    </div>
                    <div class="mt-3">
                        <p class="text-muted mb-1">Contact Us:</p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-phone me-2"></i>+1 234 567 890
                        </p>
                        <p class="text-muted">
                            <i class="fas fa-envelope me-2"></i>info@taxiservice.com
                        </p>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="row mt-4">
                <div class="col-12">
                    <hr class="bg-light">
                    
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html> 