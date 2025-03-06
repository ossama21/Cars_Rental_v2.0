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

    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/index2.css">
    <link rel="stylesheet" href="./css/index1.css">
    <link rel="stylesheet" href="./css/modern.css">

    <title>CARrent</title>
  </head>
  <body>
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
        <form class="search-form">
          <div class="row g-3">
            <div class="col-md-3">
              <select class="form-select">
                <option selected>Select Car Type</option>
                <option value="sedan">Sedan</option>
                <option value="suv">SUV</option>
                <option value="luxury">Luxury</option>
                <option value="sports">Sports</option>
              </select>
            </div>
            <div class="col-md-3">
              <input type="text" class="form-control" placeholder="Pick-up Date">
            </div>
            <div class="col-md-3">
              <input type="text" class="form-control" placeholder="Return Date">
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
                  <a href="./book.php" class="btn btn-sm btn-primary">Rent Now</a>
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
                  <a href="./book.php" class="btn btn-sm btn-primary">Rent Now</a>
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
                  <a href="./book.php" class="btn btn-sm btn-primary">Rent Now</a>
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
                  <a href="./book.php" class="btn btn-sm btn-primary">Rent Now</a>
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
      <h2 class="section-title" data-aos="fade-up">Why Rent With Us</h2>
      <p class="section-description" data-aos="fade-up">Discover the ultimate car rental experience with unparalleled service and an extensive fleet of vehicles tailored to your needs.</p>

      <div class="features">
        <div class="feature" data-aos="fade-up">
          <div class="icon-box">
            <div class="icon">
                <i class="fa-solid fa-car"></i>
            </div>
          </div>
          <div class="feature-content">
            <h3 class="feature-title">Diverse Vehicle Selection</h3>
            <p class="feature-description">From sleek city cars to robust SUVs, our fleet is designed to meet your every need.</p>
          </div>
        </div>

        <div class="feature" data-aos="fade-up" data-aos-delay="100">
          <div class="icon-box">
            <div class="icon">
              <i class="fas fa-dollar-sign"></i>
            </div>
          </div>
          <div class="feature-content">
            <h3 class="feature-title">Transparent Pricing</h3>
            <p class="feature-description">Enjoy competitive rates with no hidden fees. Our clear, straightforward pricing ensures you receive the best value.</p>
          </div>
        </div>

        <div class="feature" data-aos="fade-up" data-aos-delay="200">
          <div class="icon-box">
            <div class="icon">
              <i class="fas fa-clock"></i>
            </div>
          </div>
          <div class="feature-content">
            <h3 class="feature-title">Effortless Booking</h3>
            <p class="feature-description">Our intuitive online booking system allows you to quickly reserve your vehicle in just a few clicks.</p>
          </div>
        </div>

        <div class="feature" data-aos="fade-up" data-aos-delay="300">
          <div class="icon-box">
            <div class="icon">
              <i class="fas fa-shield-alt"></i>
            </div>
          </div>
          <div class="feature-content">
            <h3 class="feature-title">Proven Reliability</h3>
            <p class="feature-description">We are committed to delivering exceptional service and dependable vehicles.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Count Stats -->
  <section class="stats-section">
    <div class="container">
      <div class="row">
        <div class="col-md-3 col-sm-6" data-aos="fade-up">
          <div class="stat-item">
            <i class="fas fa-car"></i>
            <div class="counter">150+</div>
            <h4>Vehicles</h4>
          </div>
        </div>
        <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="100">
          <div class="stat-item">
            <i class="fas fa-users"></i>
            <div class="counter">5,000+</div>
            <h4>Happy Customers</h4>
          </div>
        </div>
        <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="200">
          <div class="stat-item">
            <i class="fas fa-map-marker-alt"></i>
            <div class="counter">25+</div>
            <h4>Locations</h4>
          </div>
        </div>
        <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="300">
          <div class="stat-item">
            <i class="fas fa-thumbs-up"></i>
            <div class="counter">99%</div>
            <h4>Satisfaction Rate</h4>
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
        <h2>Ready to Start Your Journey?</h2>
        <p>Book your perfect rental car today and hit the road with confidence.</p>
        <a href="./book.php" class="btn btn-light btn-lg">Book Now</a>
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
  <!-- Bootstrap 5 Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- AOS Animation Library -->
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <!-- Swiper JS -->
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <!-- Custom JS -->
  <script>
    // Initialize AOS
    AOS.init({
      duration: 800,
      easing: 'ease-in-out',
      once: true
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
  </script>
  </body>
</html>
