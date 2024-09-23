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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="./images/image.png">

    <!-- Bootstrap 4.3.1 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/index2.css">
    <link rel="stylesheet" href="./css/index1.css">

    <title>CARrent</title>
  </head>
  <body>
  <nav class="navbar navbar-expand-md">
    <a style="font-weight: 900;color:#333" href="#" class="navbar-brand">
        <span style="color: #ffff;">CARS</span>RENT
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a href="#" class="nav-link" style="color: white;">Home</a></li>
            <li class="nav-item"><a href="./book.php" class="nav-link" style="color: white;">Book Now</a></li>
            <li class="nav-item"><a href="about.php" class="nav-link" style="color: white;">About Us</a></li>
            <li class="nav-item"><a href="#con" class="nav-link" style="color: white;">Contact Us</a></li>

            <?php if (empty($firstName)): ?>
                <li class="nav-item"><a class="nav-link" href="./data/index.php" style="color: white;">Sign In</a></li>
            <?php else: ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="color: white;" data-toggle="dropdown">
                        <?= htmlspecialchars($firstName); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="./data/logout.php">Log out</a>
                        <?php if ($isAdmin): ?>
                            <a class="dropdown-item" href="./admin/admin.php">Admin Panel</a>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endif; ?>
        </ul>
    </div>
  </nav>

  <!-- Carousel Section -->
  <div id="carouselExampleInterval" class="carousel slide" data-ride="carousel" data-interval="5000">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="./images/wallpaperslide1.jpg" class="d-block w-100" alt="...">
      </div>
      <div class="carousel-item">
        <img src="./images/wallpaperslide2.jpg" class="d-block w-100" alt="...">
      </div>
      <div class="carousel-item">
        <img src="./images/wallpaperslide3.jpg" class="d-block w-100" alt="...">
      </div>
      <div class="carousel-item">
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

  <!-- About Section -->
  <section id="about" class="why-choose-us">
    <div class="container">
      <h2 class="title">Why Rent With Us</h2>
      <p class="description">Discover the ultimate car rental experience with unparalleled service and an extensive fleet of vehicles tailored to your needs.</p>

      <div class="features">
        <div class="feature">
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

        <div class="feature">
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

        <div class="feature">
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

        <div class="feature">
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

  <!-- Testimonials Section -->
  <section class="client-speak">
    <div class="container2 container">
      <div class="header">
        <h2 class="title">What Our Clients Are Saying</h2>
        <p class="description">
          Hear directly from our valued clients about their experiences with us.
        </p>
      </div>

      <div class="testimonials">
        <!-- Testimonial 1 -->
         <div style="display: flex; flex-direction:column">
          <div class="testimonial">
            <div class="testimonial-content">
              <h3 class="testimonial-heading">Exceptional Service</h3>
              <p class="testimonial-text">Fast, reliable, and incredibly affordable. This was a top-notch experience from start to finish.</p>
            </div>
          </div>
          <div class="testimonial-avatar">
            <img src="./images/person_1.jpg" alt="HICHAM" class="avatar"/>
            <div class="avatar-info">
              <p class="avatar-name">HICHAM</p>
              <p class="avatar-title">CEO, Company data</p>
            </div>
          </div>
         </div>
       
        <!-- Testimonial 2 -->
        <div style="display: flex;flex-direction:column">
          <div class="testimonial">
            <div class="testimonial-content">
              <h3 class="testimonial-heading">Highly Recommended</h3>
              <p class="testimonial-text">The team was both professional and welcoming. I wholeheartedly recommend their services to anyone in need.</p>
            </div>
          </div>
          <div class="testimonial-avatar">
            <img src="./images/person_3.jpg" alt="KHALID" class="avatar"/>
            <div class="avatar-info">
              <p class="avatar-name">KHALID</p>
              <p class="avatar-title">Manager, Company streaming</p>
            </div>
          </div>
        </div>

        <!-- Testimonial 3 -->
        <div style="display: flex;flex-direction:column">
          <div class="testimonial">
            <div class="testimonial-content">
              <h3 class="testimonial-heading">A Reliable Choice</h3>
              <p class="testimonial-text">I appreciated the team's efficiency and their commitment to transparency throughout the rental process.</p>
            </div>
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
  </section>

  <!-- Footer Section -->
  <footer>
  <div class="footer-top">
    <div class="container-fluid">
      <div class="row">

        <div class="col-md-3 col-sm-6 col-xs-12">
          <a style="font-weight: 900 ;color:#333;" href="#" class="navbar-brand">
            <span style="color: #ffff;" >CARS</span>RENT
         </a>
          <p class="content1">
            Best cars at low cost.
          </p>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12" id="con">
          <h6 class="content2">TEAM</h6>
          <div class="icons">
          <a style="color: #fff;" href="https://github.com/ossama21/Cars_Rental_WebSite-Project">  <i class="fa fa-envelope" aria-hidden="true"><span style="font-family: 'montserrat';">&nbsp;&nbsp;https://github.com/ossama21/Cars_Rental_WebSite-Project</span></i></a>
           
          </div>
        </div>
      </div>
    </div>

    <div class="footer2">
      &copy;2024 <span style="font-family: 'montserrat';">CARS</span>
      All Rights Reserved. <br>
      Made by : Mohammed Ali & Oussama
    </div>
  </div>
</footer>


  <!-- Optional JavaScript -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  </body>
</html>
