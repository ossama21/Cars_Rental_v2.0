<?php
session_start();
$firstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : '';
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="./images/image.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <!-- Loading animation CSS -->
    <link rel="stylesheet" href="./loading/loading.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/index2.css">
    <link rel="stylesheet" href="./css/index1.css">
    <link rel="stylesheet" href="./css/modern.css">

    <title>CARrent</title>
    <style>
      #loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(63, 81, 181, 0.9), rgba(63, 81, 181, 0) 100%);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
      }
      
      .loading-container {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
      }

      #loading-overlay.hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
      }

      body {
        overflow-y: auto !important;
        height: auto !important;
      }

      body.loading-active {
        overflow: hidden;
      }
    </style>
  </head>
  <body class="loading-active">
    <!-- Loading Animation Overlay -->
    <div id="loading-overlay">
      <div class="loading-container">
        <?php include('./loading/loading.php'); ?>
      </div>
    </div>

    <!-- Header -->
    <header>
      <nav class="navbar">
        <div class="navbar-container">
          <div class="navbar-left">
            <a href="index.php" class="navbar-brand">
              <span class="brand-highlight">CARS</span>RENT
            </a>
            <div class="nav-menu">
              <ul class="nav-list">
                <li class="nav-item">
                  <a href="index.php" class="nav-link active">Home</a>
                </li>
                <li class="nav-item">
                  <a href="about.php" class="nav-link">About</a>
                </li>
                <li class="nav-item">
                  <a href="book.php" class="nav-link">Cars</a>
                </li>
                <li class="nav-item">
                  <a href="#contact" class="nav-link">Contact</a>
                </li>
              </ul>
            </div>
          </div>

          <div class="nav-buttons">
            <?php if (isset($_SESSION['firstName'])): ?>
              <div class="profile-dropdown">
                <button class="profile-toggle">
                  <div class="profile-avatar">
                    <img src="./images/profile-pic.png" alt="Profile">
                  </div>
                  <span class="profile-name"><?= htmlspecialchars($_SESSION['firstName']); ?></span>
                  <i class="fas fa-chevron-down"></i>
                </button>
                <div class="profile-menu">
                  <a href="data/homepage.php" class="profile-menu-item">
                    <i class="fas fa-user"></i> My Account
                  </a>
                  <?php if ($isAdmin): ?>
                  <a href="admin/admin.php" class="profile-menu-item">
                    <i class="fas fa-cog"></i> Admin Dashboard
                  </a>
                  <?php endif; ?>
                  <a href="data/logout.php" class="profile-menu-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                  </a>
                </div>
              </div>
            <?php else: ?>
              <a href="data/index.php" class="nav-btn login-btn">Login</a>
              <a href="data/index.php" class="nav-btn signup-btn">Sign Up</a>
            <?php endif; ?>
          </div>

          <button class="menu-toggle" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
          </button>
        </div>
      </nav>
    </header>

    <!-- Hero Section with Carousel -->
    <section class="hero-section">
      <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="./images/wallpaperslide1.jpg" class="d-block w-100" alt="Luxury Car">
            <div class="carousel-caption" data-aos="fade-up" data-aos-delay="200">
              <h1>Premium Cars at Affordable Rates</h1>
              <p>Experience the ultimate driving comfort with our fleet</p>
              <a href="./book.php" class="btn btn-primary btn-lg">Book Now</a>
            </div>
          </div>
          <div class="carousel-item">
            <img src="./images/wallpaperslide2.jpg" class="d-block w-100" alt="SUV">
            <div class="carousel-caption" data-aos="fade-up" data-aos-delay="200">
              <h1>Find Your Perfect Ride</h1>
              <p>Wide selection of vehicles for any occasion</p>
              <a href="./book.php" class="btn btn-primary btn-lg">Book Now</a>
            </div>
          </div>
          <div class="carousel-item">
            <img src="./images/wallpaperslide3.jpg" class="d-block w-100" alt="Sports Car">
            <div class="carousel-caption" data-aos="fade-up" data-aos-delay="200">
              <h1>Drive in Style</h1>
              <p>Luxury and comfort at your fingertips</p>
              <a href="./book.php" class="btn btn-primary btn-lg">Book Now</a>
            </div>
          </div>
          <div class="carousel-item">
            <img src="./images/wallpaperslide4.jpg" class="d-block w-100" alt="Family Car">
            <div class="carousel-caption" data-aos="fade-up" data-aos-delay="200">
              <h1>Hassle-Free Car Rental</h1>
              <p>Quick booking, exceptional service</p>
              <a href="./book.php" class="btn btn-primary btn-lg">Book Now</a>
            </div>
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>
    </section>

    <!-- Search Bar Section -->
    <section class="search-section">
      <div class="container">
        <div class="search-container" data-aos="fade-up">
          <h3>Find Your Ideal Car</h3>
          <form class="search-form" action="book.php" method="GET">
            <div class="row g-3">
              <div class="col-md-3">
                <select class="form-select" name="type" required>
                  <option value="">Select Car Type</option>
                  <option value="sedan">Sedan</option>
                  <option value="suv">SUV</option>
                  <option value="luxury">Luxury</option>
                  <option value="sports">Sports</option>
                </select>
              </div>
              <div class="col-md-3">
                <input type="date" class="form-control" name="pickup_date" placeholder="Pick-up Date" required min="<?php echo date('Y-m-d'); ?>">
              </div>
              <div class="col-md-3">
                <input type="date" class="form-control" name="return_date" placeholder="Return Date" required min="<?php echo date('Y-m-d'); ?>">
              </div>
              <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Search Cars</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- Featured Cars Section -->
    <section class="featured-cars">
      <div class="container">
        <h2 class="section-title" data-aos="fade-up">Featured Vehicles</h2>
        <p class="section-description" data-aos="fade-up">Discover our selection of premium vehicles available for rent</p>
        
        <div class="swiper car-slider">
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <div class="car-card" data-aos="fade-up">
                <div class="car-image">
                  <img src="./images/img1.png" alt="Luxury Sedan">
                  <div class="car-tag">Best Seller</div>
                </div>
                <div class="car-info">
                  <h3>Mercedes Benz</h3>
                  <div class="car-specs">
                    <span><i class="fas fa-cog"></i> Automatic</span>
                    <span><i class="fas fa-gas-pump"></i> Petrol</span>
                    <span><i class="fas fa-user"></i> 5 Seats</span>
                  </div>
                  <div class="car-price">
                    <h4>$89 <span>/ day</span></h4>
                    <?php if (isset($_SESSION['firstName'])): ?>
                      <a href="checkout.php?car_id=1" class="btn btn-sm btn-primary">Rent Now</a>
                    <?php else: ?>
                      <a href="data/index.php" class="btn btn-sm btn-primary">Login to Rent</a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="swiper-slide">
              <div class="car-card" data-aos="fade-up" data-aos-delay="100">
                <div class="car-image">
                  <img src="./images/img2.png" alt="SUV">
                </div>
                <div class="car-info">
                  <h3>Audi Q5</h3>
                  <div class="car-specs">
                    <span><i class="fas fa-cog"></i> Automatic</span>
                    <span><i class="fas fa-gas-pump"></i> Petrol</span>
                    <span><i class="fas fa-user"></i> 5 Seats</span>
                  </div>
                  <div class="car-price">
                    <h4>$75 <span>/ day</span></h4>
                    <?php if (isset($_SESSION['firstName'])): ?>
                      <a href="checkout.php?car_id=2" class="btn btn-sm btn-primary">Rent Now</a>
                    <?php else: ?>
                      <a href="data/index.php" class="btn btn-sm btn-primary">Login to Rent</a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="swiper-slide">
              <div class="car-card" data-aos="fade-up" data-aos-delay="200">
                <div class="car-image">
                  <img src="./images/img3.png" alt="Sports Car">
                  <div class="car-tag">Premium</div>
                </div>
                <div class="car-info">
                  <h3>BMW Series 3</h3>
                  <div class="car-specs">
                    <span><i class="fas fa-cog"></i> Automatic</span>
                    <span><i class="fas fa-gas-pump"></i> Petrol</span>
                    <span><i class="fas fa-user"></i> 5 Seats</span>
                  </div>
                  <div class="car-price">
                    <h4>$95 <span>/ day</span></h4>
                    <?php if (isset($_SESSION['firstName'])): ?>
                      <a href="checkout.php?car_id=3" class="btn btn-sm btn-primary">Rent Now</a>
                    <?php else: ?>
                      <a href="data/index.php" class="btn btn-sm btn-primary">Login to Rent</a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="swiper-slide">
              <div class="car-card" data-aos="fade-up" data-aos-delay="300">
                <div class="car-image">
                  <img src="./images/img4.png" alt="Economy Car">
                </div>
                <div class="car-info">
                  <h3>Toyota Camry</h3>
                  <div class="car-specs">
                    <span><i class="fas fa-cog"></i> Automatic</span>
                    <span><i class="fas fa-gas-pump"></i> Hybrid</span>
                    <span><i class="fas fa-user"></i> 5 Seats</span>
                  </div>
                  <div class="car-price">
                    <h4>$65 <span>/ day</span></h4>
                    <?php if (isset($_SESSION['firstName'])): ?>
                      <a href="checkout.php?car_id=4" class="btn btn-sm btn-primary">Rent Now</a>
                    <?php else: ?>
                      <a href="data/index.php" class="btn btn-sm btn-primary">Login to Rent</a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="swiper-pagination"></div>
          <div class="swiper-button-next"></div>
          <div class="swiper-button-prev"></div>
        </div>
      </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
      <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
          <span class="section-subtitle">Our Advantages</span>
          <h2 class="section-title">Why Choose <span class="highlight">CARSRENT</span></h2>
          <p class="section-description">Experience the perfect blend of luxury, convenience, and reliability with our premium car rental services</p>
        </div>

        <div class="features">
          <div class="feature" data-aos="fade-up">
            <div class="feature-wrapper">
              <div class="icon-box">
                <div class="icon">
                  <i class="fas fa-car-side"></i>
                </div>
              </div>
              <div class="feature-content">
                <h3 class="feature-title">Premium Fleet</h3>
                <p class="feature-description">Choose from our extensive collection of well-maintained luxury and economy vehicles</p>
                <ul class="feature-list">
                  <li><i class="fas fa-check"></i> Latest Models</li>
                  <li><i class="fas fa-check"></i> Regular Maintenance</li>
                  <li><i class="fas fa-check"></i> Variety of Brands</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="feature" data-aos="fade-up" data-aos-delay="100">
            <div class="feature-wrapper">
              <div class="icon-box">
                <div class="icon">
                  <i class="fas fa-hand-holding-dollar"></i>
                </div>
              </div>
              <div class="feature-content">
                <h3 class="feature-title">Best Value</h3>
                <p class="feature-description">Competitive pricing with no hidden charges and exclusive member benefits</p>
                <ul class="feature-list">
                  <li><i class="fas fa-check"></i> Transparent Pricing</li>
                  <li><i class="fas fa-check"></i> Member Discounts</li>
                  <li><i class="fas fa-check"></i> Flexible Plans</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="feature" data-aos="fade-up" data-aos-delay="200">
            <div class="feature-wrapper">
              <div class="icon-box">
                <div class="icon">
                  <i class="fas fa-headset"></i>
                </div>
              </div>
              <div class="feature-content">
                <h3 class="feature-title">24/7 Support</h3>
                <p class="feature-description">Round-the-clock customer service to assist you whenever you need help</p>
                <ul class="feature-list">
                  <li><i class="fas fa-check"></i> 24/7 Assistance</li>
                  <li><i class="fas fa-check"></i> Roadside Support</li>
                  <li><i class="fas fa-check"></i> Quick Response</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="feature" data-aos="fade-up" data-aos-delay="300">
            <div class="feature-wrapper">
              <div class="icon-box">
                <div class="icon">
                  <i class="fas fa-shield-heart"></i>
                </div>
              </div>
              <div class="feature-content">
                <h3 class="feature-title">Safety First</h3>
                <p class="feature-description">Your safety is our priority with comprehensive insurance coverage</p>
                <ul class="feature-list">
                  <li><i class="fas fa-check"></i> Full Insurance</li>
                  <li><i class="fas fa-check"></i> Sanitized Vehicles</li>
                  <li><i class="fas fa-check"></i> Safety Protocols</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="feature" data-aos="fade-up" data-aos-delay="400">
            <div class="feature-wrapper">
              <div class="icon-box">
                <div class="icon">
                  <i class="fas fa-bolt"></i>
                </div>
              </div>
              <div class="feature-content">
                <h3 class="feature-title">Express Service</h3>
                <p class="feature-description">Quick and efficient rental process with minimal waiting time</p>
                <ul class="feature-list">
                  <li><i class="fas fa-check"></i> Fast Booking</li>
                  <li><i class="fas fa-check"></i> Express Pickup</li>
                  <li><i class="fas fa-check"></i> Digital Contracts</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="feature" data-aos="fade-up" data-aos-delay="500">
            <div class="feature-wrapper">
              <div class="icon-box">
                <div class="icon">
                  <i class="fas fa-calendar-check"></i>
                </div>
              </div>
              <div class="feature-content">
                <h3 class="feature-title">Flexible Rentals</h3>
                <p class="feature-description">Adaptable rental periods and options to suit your schedule</p>
                <ul class="feature-list">
                  <li><i class="fas fa-check"></i> Custom Duration</li>
                  <li><i class="fas fa-check"></i> Free Cancellation</li>
                  <li><i class="fas fa-check"></i> Easy Extension</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-6 mb-4 mb-md-0">
            <div class="stats-content" data-aos="fade-right">
              <span class="section-subtitle">Our Numbers</span>
              <h2 class="section-title">Trusted by Thousands <br>of Happy Customers</h2>
              <p class="section-description">We take pride in our growing community and the trust our customers place in us. Our numbers speak for themselves.</p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="stats-wrapper">
              <div class="stat-item" data-aos="fade-up">
                <div class="stat-icon-wrapper">
                  <i class="fas fa-car"></i>
                  <div class="stat-highlight"></div>
                </div>
                <div class="stat-details">
                  <div class="stat-number" data-target="150">0</div>
                  <div class="stat-label">Premium Vehicles</div>
                  <div class="stat-progress">
                    <div class="progress-bar"></div>
                  </div>
                </div>
              </div>

              <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-icon-wrapper">
                  <i class="fas fa-users"></i>
                  <div class="stat-highlight"></div>
                </div>
                <div class="stat-details">
                  <div class="stat-number" data-target="5000">0</div>
                  <div class="stat-label">Happy Clients</div>
                  <div class="stat-progress">
                    <div class="progress-bar"></div>
                  </div>
                </div>
              </div>

              <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-icon-wrapper">
                  <i class="fas fa-map-marker-alt"></i>
                  <div class="stat-highlight"></div>
                </div>
                <div class="stat-details">
                  <div class="stat-number" data-target="25">0</div>
                  <div class="stat-label">Locations</div>
                  <div class="stat-progress">
                    <div class="progress-bar"></div>
                  </div>
                </div>
              </div>

              <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-icon-wrapper">
                  <i class="fas fa-thumbs-up"></i>
                  <div class="stat-highlight"></div>
                </div>
                <div class="stat-details">
                  <div class="stat-number" data-target="99">0</div>
                  <div class="stat-label">Satisfaction Rate</div>
                  <div class="stat-progress">
                    <div class="progress-bar"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
      <div class="container">
        <h2 class="section-title" data-aos="fade-up">What Our Clients Are Saying</h2>
        <p class="section-description" data-aos="fade-up">Hear directly from our valued clients about their experiences with us.</p>
        
        <div class="swiper testimonials-slider" data-aos="fade-up">
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <div class="testimonial">
                <div class="testimonial-content">
                  <div class="rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                  </div>
                  <h3 class="testimonial-heading">Exceptional Service</h3>
                  <p class="testimonial-text">Fast, reliable, and incredibly affordable. This was a top-notch experience from start to finish.</p>
                </div>
                <div class="testimonial-avatar">
                  <img src="./images/person_1.jpg" alt="HICHAM" class="avatar"/>
                  <div class="avatar-info">
                    <p class="avatar-name">HICHAM</p>
                    <p class="avatar-title">CEO, Company data</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="swiper-slide">
              <div class="testimonial">
                <div class="testimonial-content">
                  <div class="rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                  </div>
                  <h3 class="testimonial-heading">Highly Recommended</h3>
                  <p class="testimonial-text">The team was both professional and welcoming. I wholeheartedly recommend their services to anyone in need.</p>
                </div>
                <div class="testimonial-avatar">
                  <img src="./images/person_3.jpg" alt="KHALID" class="avatar"/>
                  <div class="avatar-info">
                    <p class="avatar-name">KHALID</p>
                    <p class="avatar-title">Manager, Company streaming</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="swiper-slide">
              <div class="testimonial">
                <div class="testimonial-content">
                  <div class="rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                  </div>
                  <h3 class="testimonial-heading">A Reliable Choice</h3>
                  <p class="testimonial-text">I appreciated the team's efficiency and their commitment to transparency throughout the rental process.</p>
                </div>
                <div class="testimonial-avatar">
                  <img src="./images/person_4.jpg" alt="HAMZA" class="avatar"/>
                  <div class="avatar-info">
                    <p class="avatar-name">HAMZA</p>
                    <p class="avatar-title">Supervisor, Hotel company</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-container" data-aos="fade-up">
          <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
              <div class="cta-content">
                <span class="cta-subtitle">Start Your Journey</span>
                <h2>Ready to Experience the <br><span class="highlight">Ultimate Drive?</span></h2>
                <p>Join thousands of satisfied customers who trust us for their travel needs. Book your dream car today and enjoy the freedom of the open road.</p>
                <div class="cta-features">
                  <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Instant Booking</span>
                  </div>
                  <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>24/7 Support</span>
                  </div>
                  <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Free Cancellation</span>
                  </div>
                </div>
                <div class="cta-buttons">
                  <a href="./book.php" class="btn btn-primary btn-lg">Book Now</a>
                  <a href="about.php" class="btn btn-outline-light btn-lg">Learn More</a>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="cta-image">
                <img src="./images/img21.png" alt="Luxury Car" class="floating-image">
                <div class="cta-shapes">
                  <div class="shape shape-1"></div>
                  <div class="shape shape-2"></div>
                  <div class="shape shape-3"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer Section -->
    <footer id="contact">
      <div class="footer-content">
        <div class="container">
          <div class="row">
            <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
              <a style="font-weight: 900;" href="#" class="footer-brand">
                <span class="brand-highlight">CARS</span>RENT
              </a>
              <p class="mt-3">Providing quality car rentals with exceptional service. Best cars at competitive prices.</p>
              <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
              </div>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
              <h5>Quick Links</h5>
              <ul class="footer-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="./book.php">Book Now</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="#contact">Contact Us</a></li>
              </ul>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
              <h5>Contact Info</h5>
              <ul class="contact-info">
                <li><i class="fas fa-map-marker-alt"></i> 123 Street, City, Country</li>
                <li><i class="fas fa-phone"></i> +1 234 5678 900</li>
                <li><i class="fas fa-envelope"></i> info@carsrent.com</li>
              </ul>
            </div>
            
            <div class="col-lg-3 col-md-6">
              <h5>TEAM</h5>
              <div class="team-info">
                <a href="https://github.com/ossama21/Cars_Rental_WebSite-Project" class="github-link">
                  <i class="fab fa-github"></i> Cars_Rental_WebSite-Project
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="footer-bottom">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-md-6">
              <p class="mb-0">&copy;2024 <span>CARS</span>RENT - All Rights Reserved.</p>
            </div>
            <div class="col-md-6 text-md-end">
              <p class="mb-0">Made by: Mohammed Ali & Oussama</p>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <!-- Optional JavaScript -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    
    <!-- GSAP Animation Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/2.1.3/TweenMax.min.js"></script>
    <script src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/16327/MorphSVGPlugin.min.js"></script>
    
    <!-- Loading Animation Script -->
    <script src="./loading/loading.js"></script>
    
    <!-- Bootstrap 5 Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
      window.addEventListener('load', function() {
        const loadingOverlay = document.getElementById('loading-overlay');
        const body = document.body;
        
        if (loadingOverlay) {
          setTimeout(function() {
            loadingOverlay.classList.add('hidden');
            body.classList.remove('loading-active');
          }, 1500);
        }
      });
      
      // Initialize AOS
      AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        mirror: false
      });
      
      // Navbar color change on scroll
      $(window).scroll(function() {
        if ($(window).scrollTop() > 50) {
          $('.navbar').addClass('scrolled');
        } else {
          $('.navbar').removeClass('scrolled');
        }
      });
      
      // Initialize Swiper for car slider
      const carSwiper = new Swiper('.car-slider', {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,
        autoplay: {
          delay: 3000,
          disableOnInteraction: false,
        },
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
        breakpoints: {
          640: {
            slidesPerView: 2,
          },
          1024: {
            slidesPerView: 3,
          },
        }
      });
      
      // Initialize Swiper for testimonials
      const testimonialSwiper = new Swiper('.testimonials-slider', {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        autoplay: {
          delay: 5000,
          disableOnInteraction: false,
        },
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
        breakpoints: {
          768: {
            slidesPerView: 2,
          },
          1024: {
            slidesPerView: 3,
          },
        }
      });
      
      // Profile dropdown toggle
      document.addEventListener('DOMContentLoaded', function() {
        const profileToggle = document.querySelector('.profile-toggle');
        const profileMenu = document.querySelector('.profile-menu');
        
        if (profileToggle) {
          profileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            profileMenu.classList.toggle('active');
          });
          
          // Close dropdown when clicking outside
          document.addEventListener('click', function(e) {
            if (!profileToggle.contains(e.target) && !profileMenu.contains(e.target)) {
              profileMenu.classList.remove('active');
            }
          });
        }
      });
      
      // Smooth scroll for anchor links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          const targetId = this.getAttribute('href');
          if (targetId === '#') return;
          
          e.preventDefault();
          const targetElement = document.querySelector(targetId);
          
          if (targetElement) {
            const headerOffset = 100;
            const elementPosition = targetElement.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            
            window.scrollTo({
              top: offsetPosition,
              behavior: 'smooth'
            });
          }
        });
      });
      
      // Stats Counter Animation
      function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-target'));
        const duration = 2000; // 2 seconds
        const step = target / (duration / 16); // 60fps
        let current = 0;

        const timer = setInterval(() => {
          current += step;
          if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
          } else {
            element.textContent = Math.floor(current);
          }
        }, 16);
      }

      // Create and animate floating particles
      function createParticles() {
        const particlesContainer = document.querySelector('.floating-particles');
        const particleCount = 50;

        for (let i = 0; i < particleCount; i++) {
          const particle = document.createElement('div');
          particle.className = 'particle';
          particle.style.setProperty('--size', Math.random() * 3 + 1 + 'px');
          particle.style.setProperty('--left', Math.random() * 100 + '%');
          particle.style.setProperty('--delay', Math.random() * 5 + 's');
          particle.style.setProperty('--duration', Math.random() * 10 + 10 + 's');
          particlesContainer.appendChild(particle);
        }
      }

      // Initialize stats animations when they come into view
      const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            // Start counter animations
            entry.target.querySelectorAll('.stat-number').forEach(counter => {
              animateCounter(counter);
            });
            // Create particles
            createParticles();
            statsObserver.unobserve(entry.target);
          }
        });
      }, { threshold: 0.2 });

      // Observe the stats section
      const statsSection = document.querySelector('.stats-section');
      if (statsSection) {
        statsObserver.observe(statsSection);
      }
    </script>
  </body>
</html>
