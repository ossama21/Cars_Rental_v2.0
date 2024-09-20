<?php
session_start();
?>
<!doctype html>
<html lang="en">
  <head>
    <title>About Us - CARS RENT</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="./images/image.png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&family=Open+Sans&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    

    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/about.css">
  </head>
  
  <body>
    <!-- Navbar Section -->
    <nav class="navbar navbar-expand-md">
    <a style="font-weight: 900;color:#333" href="#" class="navbar-brand">
      <span style="color: #ffff;">CARS</span>RENT
    </a>
    <div class="collapse navbar-collapse" id="navbarCollapse">
      <div class="navbar-nav ml-auto">
        <li class="nav-item">
          <a href="./index.php" class="nav-item nav-link" style="color: white;">Home</a>
        </li>
        <li class="nav-item">
          <a href="./book.php" class="nav-item nav-link" style="color: white;">Book Now</a>
        </li>
        <li class="nav-item">
          <a href="about.php" class="nav-item nav-link" style="color: white;">About Us</a>
        </li>
        <li class="nav-item">
          <a href="#con" class="nav-item nav-link" style="color: white;">Contact Us</a>
        </li>
        <!-- <div class="signup">
          <div class="nav-item dropdown">
            <b><a href="./data/index.php" class="nav-item nav-link" style="color: white;">Sign Up</a></b>
          </div>
        </div> -->
        <!-- Profile Dropdown for Signed-In User -->
        <li  class="nav-item dropdown">
  <div  class="profile-dropdown">
    <?php if (empty($_SESSION['firstName'])): ?>
      <!-- Display sign-in link if the user is not signed in -->
     <div class="signin-link" style="margin: 8px !important;">
      <a  id="sign-in-link" style="text-decoration: none; color: #fff;" href="./data/index.php">Sign In</a>
      </div>
      <?php else: ?>
      <!-- Show dropdown with the user's name if signed in -->
      <div id="profile-btn" onclick="toggleDropdown()" class="profile-dropdown-btn">
        <div class="profile-img">
          <i class="fa-solid fa-circle"></i>
        </div>
        <span id="user-name-display" style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:#fff; font-size:16px; font-weight:900">
          <?= htmlspecialchars($_SESSION['firstName']); ?>
        </span>
        <i class="fa-solid fa-angle-down"></i>
      </div>
      <ul class="profile-dropdown-list">
        <li class="profile-dropdown-list-item">
          <a href="./data/logout.php">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Log out
          </a>
        </li>
      </ul>
    <?php endif; ?>
  </div>
</li>

      </div>
    </div>
  </nav>

    <!-- About Us Section -->
    <section id="about-section" class="about-us">
      <div class="container">
        <h1 class="text-center mt-5">About CARS RENT</h1>
        <hr class="mb-5">
        <div class="row">
          <div class="col-md-6">
            <img src="images/14.png" alt="About Us" class="img-fluid rounded">
          </div>
          <div class="col-md-6">
            <p class="lead">
              CARS RENT is dedicated to providing top-notch car rental services at affordable prices. Whether you're looking for a luxury vehicle or a compact car for city driving, we have a wide selection to suit your needs.
            </p>
            <p class="lead">
              Our goal is to offer a seamless rental experience with excellent customer service, competitive rates, and a smooth booking process. Explore our collection of vehicles and find the perfect car for your next journey.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Us Section -->
    <section id="contact-section" class="contact-us bg-light mt-5 p-5">
      <div class="container">
        <h2 class="text-center mb-4">Contact Us</h2>
        <p class="text-center">Have questions? Reach out to us!</p>
        <div class="row">
          <div class="col-md-4 text-center">
            <i class="fa fa-envelope fa-2x mb-3"></i>
            <p>Email: support@carsrent.com</p>
          </div>
          <div class="col-md-4 text-center">
            <i class="fa fa-phone fa-2x mb-3"></i>
            <p>Phone: +212 0678963254</p>
          </div>
          <div class="col-md-4 text-center">
            <i class="fa fa-map-marker fa-2x mb-3"></i>
            <p>Location: Morocco CasaBlanca, City</p>
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


    <!-- Bootstrap JS and Dependencies -->
     <script src="./js/dropdown.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  </body>
</html>
