<?php
session_start();
$firstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : '';
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CARSrent - Premium Car Rental Services</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="./images/image.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: var(--light-color);
            color: var(--dark-color);
        }
        
        .navbar {
            background-color: var(--primary-color);
            padding: 1rem 0;
        }
        
        .navbar .container {
            max-width: 95%;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white !important;
        }
        
        .nav-link {
            color: white !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--accent-color) !important;
        }
        
        .carousel-item img {
            object-fit: cover;
            height: 600px;
        }
        
        .features {
            padding: 4rem 0;
            background-color: white;
        }
        
        .features h2 {
            text-align: center;
            margin-bottom: 3rem;
            color: var(--primary-color);
        }
        
        .feature-item {
            text-align: center;
            padding: 1.5rem;
            background-color: var(--light-color);
            border-radius: 10px;
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .feature-item:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .feature-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .testimonials {
            padding: 4rem 0;
            background-color: var(--secondary-color);
            color: white;
        }
        
        .testimonials h2 {
            text-align: center;
            margin-bottom: 3rem;
            color: var(--light-color);
        }
        
        .testimonial-item {
            background-color: white;
            color: var(--dark-color);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        
        .testimonial-content {
            margin-bottom: 1rem;
        }
        
        .testimonial-author {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .testimonial-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 2rem 0;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .footer-links a {
            color: white;
            margin-left: 1rem;
            text-decoration: none;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">CARS<span style="color: var(--accent-color);">rent</span></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="./book.php">Book Now</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <?php if (empty($firstName)): ?>
                        <li class="nav-item"><a class="nav-link" href="./data/index.php">Sign In</a></li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?= htmlspecialchars($firstName); ?>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="./data/logout.php">Log out</a>
                                <?php if ($isAdmin): ?>
                                    <a class="dropdown-item" href="./admin/admin.php">Admin Panel</a>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div id="carouselExampleInterval" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active" data-interval="5000">
                <img src="./images/wallpaperslide1.jpg" class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item" data-interval="5000">
                <img src="./images/wallpaperslide2.jpg" class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item" data-interval="5000">
                <img src="./images/wallpaperslide3.jpg" class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item" data-interval="5000">
                <img src="./images/wallpaperslide4.jpg" class="d-block w-100" alt="...">
            </div>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleInterval" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleInterval" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <section class="features">
        <div class="container">
            <h2 data-aos="fade-up">Why Choose Us</h2>
            <div class="row">
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-item">
                        <img src="./images/feature1.jpg" alt="Wide Selection" class="feature-image">
                        <i class="fas fa-car feature-icon"></i>
                        <h3>Wide Selection</h3>
                        <p>Choose from our extensive fleet of premium vehicles</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-item">
                        <img src="./images/feature2.jpg" alt="Best Prices" class="feature-image">
                        <i class="fas fa-dollar-sign feature-icon"></i>
                        <h3>Best Prices</h3>
                        <p>Competitive rates with no hidden fees</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-item">
                        <img src="./images/feature3.jpg" alt="24/7 Support" class="feature-image">
                        <i class="fas fa-headset feature-icon"></i>
                        <h3>24/7 Support</h3>
                        <p>Our customer service team is always here to help</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-item">
                        <img src="./images/feature4.jpg" alt="Convenient Locations" class="feature-image">
                        <i class="fas fa-map-marker-alt feature-icon"></i>
                        <h3>Convenient Locations</h3>
                        <p>Pick up and drop off at multiple locations</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials">
        <div class="container">
            <h2 data-aos="fade-up">What Our Clients Say</h2>
            <div class="row">
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-item">
                        <img src="./images/person_1.jpg" alt="John Doe" class="testimonial-image">
                        <div class="testimonial-content">
                            <p>"Exceptional service and top-notch vehicles. Highly recommended!"</p>
                        </div>
                        <p class="testimonial-author">- John Doe</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-item">
                        <img src="./images/person_2.jpg" alt="Jane Smith" class="testimonial-image">
                        <div class="testimonial-content">
                            <p>"Smooth booking process and great customer support. Will use again!"</p>
                        </div>
                        <p class="testimonial-author">- Jane Smith</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-item">
                        <img src="./images/person_3.jpg" alt="Mike Johnson" class="testimonial-image">
                        <div class="testimonial-content">
                            <p>"The best car rental experience I've had. Fantastic fleet and service!"</p>
                        </div>
                        <p class="testimonial-author">- Mike Johnson</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer id="contact">
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2024 CARSrent. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="mailto:info@carsrent.com">Contact Us</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html>