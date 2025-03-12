<?php
session_start();
$firstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : '';
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!doctype html>
<html lang="en">
  <head>
    <title>About Us - CARS RENT</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="./images/image.png">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/about.css">
    <link rel="stylesheet" href="./css/modern.css">
    <link rel="stylesheet" href="./css/index1.css">

    <style>
      /* Loading overlay styles */
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
        transition: opacity 0.5s ease-out;
      }
      
      .loading-container {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
      }
    </style>
  </head>
  
  <body class="loading-active">
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
                  <a href="index.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                  <a href="about.php" class="nav-link active">About</a>
                </li>
                <li class="nav-item">
                  <a href="book.php" class="nav-link">Cars</a>
                </li>
                <li class="nav-item">
                  <a href="#contact-section" class="nav-link">Contact</a>
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

    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="container">
            <div class="hero-content">
                <h1 data-aos="fade-up">About CARS RENT</h1>
                <p data-aos="fade-up" data-aos-delay="200">Driving your journey with comfort, style, and reliability</p>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section id="about-section" class="about-us">
      <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
          <h2>Our Story</h2>
          <div class="divider mx-auto"></div>
          <p class="lead-text">Delivering exceptional car rental experiences since 2016</p>
        </div>
        <div class="row align-items-center">
          <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
            <img src="images/14.png" alt="About Us" class="img-fluid rounded shadow">
          </div>
          <div class="col-lg-6" data-aos="fade-left" data-aos-delay="400">
            <div class="about-content pl-lg-4 mt-5 mt-lg-0">
              <h3>Your Premium Car Rental Service</h3>
              <p class="mb-4">
                CARS RENT is dedicated to providing top-notch car rental services at affordable prices. Whether you're looking for a luxury vehicle or a compact car for city driving, we have a wide selection to suit your needs.
              </p>
              <p class="mb-4">
                Our mission is to make car rental easy, convenient, and reliable for all our customers. With a focus on quality service and customer satisfaction, we've grown to become a trusted name in the industry.
              </p>
              <div class="row mt-4">
                <div class="col-6">
                  <div class="feature-box">
                    <i class="fas fa-car mb-3"></i>
                    <h5>Premium Fleet</h5>
                    <p>Wide selection of well-maintained vehicles</p>
                  </div>
                </div>
                <div class="col-6">
                  <div class="feature-box">
                    <i class="fas fa-shield-alt mb-3"></i>
                    <h5>Safety First</h5>
                    <p>Regular maintenance and thorough inspections</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us bg-light py-5">
      <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
          <h2>Why Choose Us</h2>
          <div class="divider mx-auto"></div>
          <p class="lead-text">What makes CARS RENT your best choice</p>
        </div>
        <div class="row">
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card feature-card mb-4">
              <div class="card-body text-center">
                <div class="icon-box">
                  <i class="fas fa-dollar-sign"></i>
                </div>
                <h4>Competitive Pricing</h4>
                <p>We offer the best rates in the market with no hidden fees or charges.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card feature-card mb-4">
              <div class="card-body text-center">
                <div class="icon-box">
                  <i class="fas fa-headset"></i>
                </div>
                <h4>24/7 Support</h4>
                <p>Our customer service team is always ready to assist you whenever you need help.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
            <div class="card feature-card mb-4">
              <div class="card-body text-center">
                <div class="icon-box">
                  <i class="fas fa-check-circle"></i>
                </div>
                <h4>Easy Booking</h4>
                <p>Our streamlined booking process ensures a hassle-free experience.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Our History Timeline -->
    <section class="history-section py-5">
      <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
          <h2>Our Journey</h2>
          <div class="divider mx-auto"></div>
          <p class="lead-text">How we've grown over the years</p>
        </div>
        <div class="timeline">
          <div class="timeline-item" data-aos="fade-right">
            <div class="timeline-content">
              <h4>2016</h4>
              <p>Founded CARS RENT with just 5 vehicles in our fleet</p>
            </div>
          </div>
          <div class="timeline-item" data-aos="fade-left">
            <div class="timeline-content">
              <h4>2018</h4>
              <p>Expanded our fleet to 20 vehicles and launched our online booking platform</p>
            </div>
          </div>
          <div class="timeline-item" data-aos="fade-right">
            <div class="timeline-content">
              <h4>2020</h4>
              <p>Introduced premium luxury vehicles to our fleet and expanded to new locations</p>
            </div>
          </div>
          <div class="timeline-item" data-aos="fade-left">
            <div class="timeline-content">
              <h4>2024</h4>
              <p>Recognized as one of the top car rental services with a growing customer base</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Meet Our Team -->
    <section class="team-section bg-light py-5">
      <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
          <h2>Our Team</h2>
          <div class="divider mx-auto"></div>
          <p class="lead-text">Meet the people who make it all happen</p>
        </div>
        <div class="row">
          <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="team-member">
              <div class="member-img">
                <img src="images/person_1.jpg" class="img-fluid" alt="Team Member">
              </div>
              <div class="member-info">
                <h4>John Doe</h4>
                <span>CEO & Founder</span>
                <div class="social-links">
                  <a href="#"><i class="fab fa-facebook-f"></i></a>
                  <a href="#"><i class="fab fa-twitter"></i></a>
                  <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="team-member">
              <div class="member-img">
                <img src="images/person_2.jpg" class="img-fluid" alt="Team Member">
              </div>
              <div class="member-info">
                <h4>Jane Smith</h4>
                <span>Operations Manager</span>
                <div class="social-links">
                  <a href="#"><i class="fab fa-facebook-f"></i></a>
                  <a href="#"><i class="fab fa-twitter"></i></a>
                  <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="500">
            <div class="team-member">
              <div class="member-img">
                <img src="images/person_3.jpg" class="img-fluid" alt="Team Member">
              </div>
              <div class="member-info">
                <h4>Mike Johnson</h4>
                <span>Fleet Manager</span>
                <div class="social-links">
                  <a href="#"><i class="fab fa-facebook-f"></i></a>
                  <a href="#"><i class="fab fa-twitter"></i></a>
                  <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="700">
            <div class="team-member">
              <div class="member-img">
                <img src="images/person_4.jpg" class="img-fluid" alt="Team Member">
              </div>
              <div class="member-info">
                <h4>Sarah Wilson</h4>
                <span>Customer Relations</span>
                <div class="social-links">
                  <a href="#"><i class="fab fa-facebook-f"></i></a>
                  <a href="#"><i class="fab fa-twitter"></i></a>
                  <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials-section py-5">
      <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
          <h2>Customer Testimonials</h2>
          <div class="divider mx-auto"></div>
          <p class="lead-text">What our customers say about us</p>
        </div>
        <div class="row">
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="testimonial-card">
              <div class="testimonial-content">
                <div class="rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                </div>
                <p>"Great service and amazing cars! The rental process was smooth and the staff was very friendly. Will definitely rent from CARS RENT again."</p>
              </div>
              <div class="testimonial-author">
                <img src="images/avatar-1.png" alt="Customer">
                <div>
                  <h5>Michael Brown</h5>
                  <small>Regular Customer</small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="testimonial-card">
              <div class="testimonial-content">
                <div class="rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star-half-alt"></i>
                </div>
                <p>"I needed a car last minute for a business trip and CARS RENT made everything easy. The car was clean and in perfect condition. Highly recommend!"</p>
              </div>
              <div class="testimonial-author">
                <img src="images/avatar-2.png" alt="Customer">
                <div>
                  <h5>Lisa Johnson</h5>
                  <small>Business Traveler</small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
            <div class="testimonial-card">
              <div class="testimonial-content">
                <div class="rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                </div>
                <p>"The prices are competitive and the service is exceptional. I rented a luxury car for my wedding day and it was perfect. Thank you CARS RENT!"</p>
              </div>
              <div class="testimonial-author">
                <img src="images/avatar-3.png" alt="Customer">
                <div>
                  <h5>David Miller</h5>
                  <small>Special Occasion</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section bg-light py-5">
      <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
          <h2>Frequently Asked Questions</h2>
          <div class="divider mx-auto"></div>
          <p class="lead-text">Find answers to common questions</p>
        </div>
        <div class="row">
          <div class="col-lg-8 mx-auto">
            <div class="accordion" id="faqAccordion" data-aos="fade-up" data-aos-delay="200">
              <!-- FAQ Item 1 -->
              <div class="card">
                <div class="card-header" id="headingOne">
                  <h2 class="mb-0">
                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                      What documents do I need to rent a car?
                    </button>
                  </h2>
                </div>
                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#faqAccordion">
                  <div class="card-body">
                    You will need a valid driver's license, a credit or debit card for payment, and a valid ID or passport. International customers may need additional documentation.
                  </div>
                </div>
              </div>
              
              <!-- FAQ Item 2 -->
              <div class="card">
                <div class="card-header" id="headingTwo">
                  <h2 class="mb-0">
                    <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                      Can I return the car to a different location?
                    </button>
                  </h2>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#faqAccordion">
                  <div class="card-body">
                    Yes, in many cases you can return the car to a different location, though there may be an additional fee for this service. Please check with us in advance.
                  </div>
                </div>
              </div>
              
              <!-- FAQ Item 3 -->
              <div class="card">
                <div class="card-header" id="headingThree">
                  <h2 class="mb-0">
                    <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                      Is there a mileage limit on rentals?
                    </button>
                  </h2>
                </div>
                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#faqAccordion">
                  <div class="card-body">
                    Most of our rentals come with unlimited mileage. However, some specific vehicle categories or special offers may have mileage restrictions. This will be clearly indicated during the booking process.
                  </div>
                </div>
              </div>
              
              <!-- FAQ Item 4 -->
              <div class="card">
                <div class="card-header" id="headingFour">
                  <h2 class="mb-0">
                    <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                      What is your cancellation policy?
                    </button>
                  </h2>
                </div>
                <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#faqAccordion">
                  <div class="card-body">
                    Cancellations made at least 48 hours before the rental start date typically receive a full refund. Cancellations made within 48 hours may be subject to a cancellation fee. No-shows are usually charged the full rental amount.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Us Section -->
    <section id="contact-section" class="contact-us py-5">
      <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
          <h2>Contact Us</h2>
          <div class="divider mx-auto"></div>
          <p class="lead-text">We'd love to hear from you</p>
        </div>
        <div class="row">
          <div class="col-lg-5" data-aos="fade-right" data-aos-delay="200">
            <div class="contact-info">
              <div class="contact-item">
                <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                <div class="content">
                  <h5>Our Location</h5>
                  <p>Morocco CasaBlanca, City</p>
                </div>
              </div>
              <div class="contact-item">
                <div class="icon"><i class="fas fa-phone"></i></div>
                <div class="content">
                  <h5>Call Us</h5>
                  <p>+212 0678963254</p>
                </div>
              </div>
              <div class="contact-item">
                <div class="icon"><i class="fas fa-envelope"></i></div>
                <div class="content">
                  <h5>Email Us</h5>
                  <p>support@carsrent.com</p>
                </div>
              </div>
              <div class="contact-item">
                <div class="icon"><i class="fas fa-clock"></i></div>
                <div class="content">
                  <h5>Working Hours</h5>
                  <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-7" data-aos="fade-left" data-aos-delay="400">
            <form class="contact-form">
              <div class="row">
                <div class="col-md-6 form-group">
                  <label for="name">Your Name</label>
                  <input type="text" id="name" class="form-control" placeholder="Enter your name" required>
                </div>
                <div class="col-md-6 form-group">
                  <label for="email">Your Email</label>
                  <input type="email" id="email" class="form-control" placeholder="Enter your email" required>
                </div>
              </div>
              <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" class="form-control" placeholder="Enter subject" required>
              </div>
              <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" class="form-control" rows="5" placeholder="Enter your message" required></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
      <div class="container-fluid p-0">
        <div class="google-map">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d106376.72692355985!2d-7.6923766915774675!3d33.57250709573663!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda7cd4778aa113b%3A0xb06c1d84f310fd3!2sCasablanca%2C%20Morocco!5e0!3m2!1sen!2sus!4v1655391301269!5m2!1sen!2sus" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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
                <li><a href="#contact-section">Contact Us</a></li>
              </ul>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
              <h5>Contact Info</h5>
              <ul class="contact-info">
                <li><i class="fas fa-map-marker-alt"></i> Morocco CasaBlanca, City</li>
                <li><i class="fas fa-phone"></i> +212 0678963254</li>
                <li><i class="fas fa-envelope"></i> support@carsrent.com</li>
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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    
    <!-- GSAP Animation Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/2.1.3/TweenMax.min.js"></script>
    <script src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/16327/MorphSVGPlugin.min.js"></script>
    
    <!-- Bootstrap 5 Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
      // Initialize AOS
      AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        mirror: false
      });
    </script>
  </body>
</html>
