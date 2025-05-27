<?php
session_start();

// Check if user is not logged in

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Fleet - Taxi Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Navbar Styles - Same as index.php */
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

        /* Gallery Styles */
        body {
            padding-top: 100px;
            background-color: #f8f9fa;
        }

        .gallery-title {
            text-align: center;
            margin-bottom: 50px;
            padding-top: 20px;
        }

        .gallery-title h2 {
            color: #333;
            font-weight: 700;
            position: relative;
            display: inline-block;
            padding-bottom: 15px;
        }

        .gallery-title h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background-color: #ffc107;
        }

        .car-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            transition: transform 0.3s ease;
            background: white;
        }

        .car-card:hover {
            transform: translateY(-10px);
        }

        .car-image {
            height: 250px;
            overflow: hidden;
            position: relative;
        }

        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .car-card:hover .car-image img {
            transform: scale(1.1);
        }

        .car-details {
            padding: 20px;
        }

        .car-type {
            color: #ffc107;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .car-features {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }

        .car-features li {
            margin-bottom: 8px;
            color: #666;
        }

        .car-features i {
            color: #ffc107;
            margin-right: 10px;
        }

        .book-now-btn {
            width: 100%;
            padding: 12px;
            background: #ffc107;
            border: none;
            border-radius: 5px;
            color: #333;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .book-now-btn:hover {
            background: #ffb300;
            transform: translateY(-2px);
        }

        /* Footer Styles - Same as index.php */
        footer {
            background-color: #1a1a1a;
            color: white;
            padding: 50px 0 20px;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Taxi Service</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="taxi-gallery.php">Our Fleet</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user/register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Gallery Content -->
    <div class="container">
        <div class="gallery-title">
            <h2>Our Premium Fleet</h2>
            <p class="text-muted">Choose from our wide range of comfortable and reliable vehicles</p>
        </div>

        <div class="row">
            <!-- Sedan -->
            <div class="col-md-4">
                <div class="car-card">
                    <div class="car-image">
                        <img src="https://images.unsplash.com/photo-1549317661-bd32c8ce0db2" alt="Luxury Sedan">
                    </div>
                    <div class="car-details">
                        <div class="car-type">Luxury Sedan</div>
                        <h4>Premium Comfort</h4>
                        <ul class="car-features">
                            <li><i class="fas fa-user"></i> 4 Passengers</li>
                            <li><i class="fas fa-suitcase"></i> 2 Large Bags</li>
                            <li><i class="fas fa-snowflake"></i> Air Conditioned</li>
                        </ul>
                        <a href="book-ride.php" class="btn book-now-btn">Book Now</a>
                    </div>
                </div>
            </div>

            <!-- SUV -->
            <div class="col-md-4">
                <div class="car-card">
                    <div class="car-image">
                        <img src="https://images.unsplash.com/photo-1533473359331-0135ef1b58bf" alt="Premium SUV">
                    </div>
                    <div class="car-details">
                        <div class="car-type">Premium SUV</div>
                        <h4>Family Comfort</h4>
                        <ul class="car-features">
                            <li><i class="fas fa-user"></i> 6 Passengers</li>
                            <li><i class="fas fa-suitcase"></i> 4 Large Bags</li>
                            <li><i class="fas fa-snowflake"></i> Air Conditioned</li>
                        </ul>
                        <a href="book-ride.php" class="btn book-now-btn">Book Now</a>
                    </div>
                </div>
            </div>

            <!-- Luxury Car -->
            <div class="col-md-4">
                <div class="car-card">
                    <div class="car-image">
                        <img src="https://images.unsplash.com/photo-1549399542-7e3f8b79c341" alt="Executive Class">
                    </div>
                    <div class="car-details">
                        <div class="car-type">Executive Class</div>
                        <h4>Ultimate Luxury</h4>
                        <ul class="car-features">
                            <li><i class="fas fa-user"></i> 3 Passengers</li>
                            <li><i class="fas fa-suitcase"></i> 2 Large Bags</li>
                            <li><i class="fas fa-star"></i> Premium Service</li>
                        </ul>
                        <a href="book-ride.php" class="btn book-now-btn">Book Now</a>
                    </div>
                </div>
            </div>

            <!-- 2 Seater Sports Taxi -->
            <!-- <div class="col-md-4">
                <div class="car-card">
                    <div class="car-image">
                        <img src="https://images.unsplash.com/photo-1549399542-7e3f8b79c341" alt="Sports Taxi">
                    </div>
                    <div class="car-details">
                        <div class="car-type">Sports Taxi</div>
                        <!-- <h4>Luxury Experience</h4>
                        <ul class="car-features">
                            <li><i class="fas fa-user"></i> 2 Passengers</li>
                            <li><i class="fas fa-suitcase"></i> 1 Large Bag</li>
                            <li><i class="fas fa-tachometer-alt"></i> Premium Speed</li>
                        </ul>
                        <a href="book.php" class="btn book-now-btn">Book Now</a> -->
           
            <!-- 7 Seater Family Van -->
            <div class="col-md-4">
                <div class="car-card">
                    <div class="car-image">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT4n4g7w0yw8qTYgi8B34-nlMsOWMHAOkSzJA&s" alt="Family Van">
                    </div>
                    <div class="car-details">
                        <div class="car-type">Family Van</div>
                        <h4>Group Travel</h4>
                        <ul class="car-features">
                            <li><i class="fas fa-users"></i> 7 Passengers</li>
                            <li><i class="fas fa-suitcase"></i> 5 Large Bags</li>
                            <li><i class="fas fa-baby"></i> Child Seat Available</li>
                        </ul>
                        <a href="book.php" class="btn book-now-btn">Book Now</a>
                    </div>
                </div>
            </div>

            <!-- Electric Eco Taxi -->
            <div class="col-md-4">
                <div class="car-card">
                    <div class="car-image">
                        <img src="https://images.unsplash.com/photo-1593941707882-a5bba14938c7" alt="Electric Taxi">
                    </div>
                    <div class="car-details">
                        <div class="car-type">Eco Friendly</div>
                        <h4>Electric Vehicle</h4>
                        <ul class="car-features">
                            <li><i class="fas fa-leaf"></i> Zero Emissions</li>
                            <li><i class="fas fa-user"></i> 4 Passengers</li>
                            <li><i class="fas fa-charging-station"></i> Electric Powered</li>
                        </ul>
                        <a href="book.php" class="btn book-now-btn">Book Now</a>
                    </div>
                </div>
            </div>

            <!-- Premium Business Class
            <div class="col-md-4">
                <div class="car-card">
                    <div class="car-image">
                        <img src="https://images.unsplash.com/photo-1549399542-7e3f8b79c341" alt="Business Class">
                    </div>
                    <div class="car-details">
                        <div class="car-type">Business Class</div>
                        <h4>Corporate Travel</h4>
                        <ul class="car-features">
                            <li><i class="fas fa-wifi"></i> Free WiFi</li>
                            <li><i class="fas fa-briefcase"></i> Work Space</li>
                            <li><i class="fas fa-glass-martini"></i> Refreshments</li>
                        </ul>
                        <a href="book.php" class="btn book-now-btn">Book Now</a>
                    </div>
                </div>
            </div> -->

            <!-- Standard Taxi -->
            <!-- <div class="col-md-4">
                <div class="car-card">
                    <div class="car-image">
                        <img src="https://images.unsplash.com/photo-1518783211485-10fd3bfb2ce2" alt="Standard Taxi">
                    </div>
                    <div class="car-details">
                        <div class="car-type">Standard Taxi</div>
                        <h4>City Comfort</h4>
                        <ul class="car-features">
                            <li><i class="fas fa-user"></i> 4 Passengers</li>
                            <li><i class="fas fa-suitcase"></i> 2 Large Bags</li>
                            <li><i class="fas fa-snowflake"></i> Air Conditioned</li>
                        </ul>
                        <a href="book.php" class="btn book-now-btn">Book Now</a>
                    </div>
                </div>
            </div> -->

            <!-- Night Taxi -->
            <!-- <div class="col-md-4">
                <div class="car-card">
                    <div class="car-image">
                        <img src="https://images.unsplash.com/photo-1534803005787-fa0b5c8087d1" alt="Night Taxi">
                    </div>
                    <div class="car-details">
                        <div class="car-type">Night Service</div>
                        <h4>24/7 Available</h4>
                        <ul class="car-features">
                            <li><i class="fas fa-moon"></i> Night Service</li>
                            <li><i class="fas fa-shield-alt"></i> Safe Travel</li>
                            <li><i class="fas fa-star"></i> Premium Service</li>
                        </ul>
                        <a href="book.php" class="btn book-now-btn">Book Now</a>
                    </div>
                </div>
            </div> -->

            <!-- City Taxi -->
            <!-- <div class="col-md-4">
                <div class="car-card">
                    <div class="car-image">
                        <img src="https://images.unsplash.com/photo-1661953379396-c5882d1bc5af" alt="City Taxi">
                    </div>
                    <div class="car-details">
                        <div class="car-type">City Explorer</div>
                        <h4>Urban Travel</h4>
                        <ul class="car-features">
                            <li><i class="fas fa-route"></i> City Tours</li>
                            <li><i class="fas fa-user"></i> 4 Passengers</li>
                            <li><i class="fas fa-map-marked-alt"></i> Local Expert</li>
                        </ul>
                        <a href="book.php" class="btn book-now-btn">Book Now</a>
                    </div>
                </div>
            </div> -->

            <!-- Yellow City Taxi -->
            <div class="col-md-4">
                <div class="car-card">
                    <div class="car-image">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRJaNuPcjPai9NrRpwsuXNh0T5SVuvLRfj9jA&s" alt="City Taxi">
                    </div>
                    <div class="car-details">
                        <div class="car-type">City Taxi</div>
                        <h4>Standard Comfort</h4>
                        <ul class="car-features">
                            <li><i class="fas fa-user"></i> 4 Passengers</li>
                            <li><i class="fas fa-suitcase"></i> 2 Large Bags</li>
                            <li><i class="fas fa-snowflake"></i> Air Conditioned</li>
                        </ul>
                        <a href="book.php" class="btn book-now-btn">Book Now</a>
                    </div>
                </div>
            </div>

            <!-- Night Service Taxi -->
            <div class="col-md-4">
                <div class="car-card">
                    <div class="car-image">
                        <img src="https://images.unsplash.com/photo-1518783211485-10fd3bfb2ce2" alt="Night Taxi">
                    </div>
                    <div class="car-details">
                        <div class="car-type">Night Service</div>
                        <h4>24/7 Available</h4>
                        <ul class="car-features">
                            <li><i class="fas fa-moon"></i> Night Service</li>
                            <li><i class="fas fa-shield-alt"></i> Safe Travel</li>
                            <li><i class="fas fa-star"></i> Premium Service</li>
                        </ul>
                        <a href="book.php" class="btn book-now-btn">Book Now</a>
                    </div>
                </div>
            </div>

            <!-- London Black Taxi -->
            <!-- <div class="col-md-4">
                <div class="car-card">
                    <div class="car-image">
                        <img src="https://images.unsplash.com/photo-1453728013993-6d66e9c9123a?auto=format&fit=crop&q=80" alt="Premium Taxi">
                    </div>
                    <div class="car-details">
                        <div class="car-type">Premium Service</div>
                        <h4>Executive Travel</h4>
                        <ul class="car-features">
                            <li><i class="fas fa-user"></i> 4 Passengers</li>
                            <li><i class="fas fa-wifi"></i> Free WiFi</li>
                            <li><i class="fas fa-star"></i> Luxury Service</li>
                        </ul>
                        <a href="book.php" class="btn book-now-btn">Book Now</a>
                    </div>
                </div>
            </div>
        </div> -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
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