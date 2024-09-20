<?php
session_start();
// include('homepage.php');
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="./images/image.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="./css/index2.css">
    <link rel="stylesheet" href="./css/index1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <title>CARrent</title>
   
    <body>
      <?php
    // include './data/connect.php'; 
    
    $firstName = isset($_SESSION['firstName'])  ;
    // $sel = "SELECT * FROM users"; 
    // $query = mysqli_query($conn, $sel);
    // $resul = mysqli_fetch_assoc($query); 
    ?>
    <style>
      #sign-in-link{
        margin: 10px;
      }
    </style>
   
  </head>
  
  <nav class="navbar navbar-expand-md">
    <a style="font-weight: 900;color:#333" href="#" class="navbar-brand">
      <span style="color: #ffff;">CARS</span>RENT
    </a>
    <div class="collapse navbar-collapse" id="navbarCollapse">
      <div class="navbar-nav ml-auto">
        <li class="nav-item">
          <a href="#" class="nav-item nav-link" style="color: white;">Home</a>
        </li>
        <li class="nav-item">
          <a href="./book.php" class="nav-item nav-link" style="color: white;">Book Now</a>
        </li>
        <li class="nav-item">
          <a href="#about" class="nav-item nav-link" style="color: white;">About Us</a>
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
     <div style="margin: 8px !important;">
      <a id="sign-in-link" style="text-decoration: none; color: #fff;" href="./data/index.php">Sign In</a>
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
    <div id="carouselExampleInterval" class="carousel slide" data-ride="carousel" data-interval="5000">
        <div class="carousel-inner">
          <div class="carousel-item active" >
            <img src="./images/wallpaperslide1.jpg" class="d-block w-100" alt="...">
          </div>
          <div class="carousel-item" >
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
      <section id="about" class="why-choose-us"></section>
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
                <p class="feature-description">From sleek city cars to robust SUVs, our fleet is designed to meet your every need. Whether you're navigating urban streets or embarking on a cross-country adventure, we have the perfect vehicle for you.</p>
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
                <p class="feature-description">Enjoy competitive rates with no hidden fees. Our clear, straightforward pricing ensures you receive the best value, combining affordability with top-notch quality and service.</p>
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
                <p class="feature-description">Our intuitive online booking system allows you to quickly reserve your vehicle in just a few clicks. Choose your car, select your pickup location, and secure your rental with ease.</p>
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
                <p class="feature-description">With years of industry experience, we are committed to delivering exceptional service and dependable vehicles. Trust us for a smooth, hassle-free rental experience every time.</p>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="client-speak">
        <div class="container2 container">
          <!-- Title and description -->
          <div class="header">
            <h2 class="title">What Our Clients Are Saying</h2>
            <p class="description">
              Hear directly from our valued clients about their experiences with us.
            </p>
          </div>
    
          <!-- Testimonials container -->
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
                <img
                  src="./images/person_1.jpg"
                  alt="HICHAM"
                  class="avatar"
                />
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
                <img
                  src="./images/person_3.jpg"
                  alt="KHALID"
                  class="avatar"
                />
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
                  <h3 class="testimonial-heading">Outstanding Service</h3>
                  <p class="testimonial-text">They exceeded all my expectations with their outstanding service. Truly impressive!</p>
                </div>
                
              </div>
              <div class="testimonial-avatar">
                <img
                  src="./images/person_4.jpg"
                  alt="ZAKARIA"
                  class="avatar"
                />
                <div class="avatar-info">
                  <p class="avatar-name">ZAKARIA</p>
                  <p class="avatar-title">Director, Company phone</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

     

</div>


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
 <script src="./js/dropdown.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>

   
    
  </body>
</html>