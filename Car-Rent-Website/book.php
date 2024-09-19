<?php
session_start();
// include('homepage.php');
?>
<!doctype html>
<html lang="en">
  <head>
    <title>BOOKING</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="./images/image.png">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&family=Open+Sans&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/book.css">
    <!-- Import products.js -->


  </head>
  
  <body>
    <!-- Navbar Section -->
    <nav  class="navbar navbar-expand-md  " > 
      <a style="font-weight: 900; color:#333;" href="#" class="navbar-brand">
         <span style="color: #ffff ;" >CARS</span>RENT
      </a>
      <!-- <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
          <span class="navbar-toggler-icon"></span>
      </button> -->
      <div class="collapse navbar-collapse" id="navbarCollapse">
          <div class="navbar-nav">
              
          </div>
  
      
          <div class="navbar-nav ml-auto">
              <li class="nav-item dropdown">
                
                <b><a href="index.php" class="nav-item nav-link"  style="color: white;">Home</a></b>
                </li>
                <li class="nav-item dropdown">
                  <b><a href="#" class="nav-item nav-link"></a></b>
                  </li>
                <li class="nav-item dropdown">
                  <b><a href="book.html" class="nav-item nav-link" style="color: white;">Book Now</a></b>
                  </li>
                  <li class="nav-item dropdown">
                    <b><a href="#" class="nav-item nav-link"></a></b>
                    </li>
                 
                    <li class="nav-item dropdown">
                      <b><a href="#" class="nav-item nav-link"></a></b>
                      </li>
                    <li class="nav-item dropdown">
                      <b><a href="#con" class="nav-item nav-link" style="color: white;">Contact Us</a></b>
                      </li>
                      <li class="nav-item dropdown">
                        <b><a href="#" class="nav-item nav-link"></a></b>
                        </li>
                      
              
          </div>
      </div>
      <!-- <div class="signup">
        <div class="nav-item dropdown">
          <b><a href="./data/index.php" class="nav-item nav-link" style="color: white;">Sign Up</a></b>
        </div>
      </div> -->
      <li style="display: unset;" class="nav-item dropdown">
          <div class="profile-dropdown">
            <div onclick="toggleDropdown()" class="profile-dropdown-btn">
              <div class="profile-img">
                <i class="fa-solid fa-circle"></i>
              </div>
              <span  style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;color:#fff;font-size:  16px;font-weight:900" id="user-name-display">
                <!-- Default name that will be updated via JavaScript -->
               

          <?php if (empty($_SESSION['firstName'])): ?>
    <!-- Assign and display the link if 'firstName' is empty -->
          <?php $_SESSION['firstName'] = '<a style="text-decoration: none;color:#ffff" href="./data/index.php">Sign In</a>'; ?>
         <?= $_SESSION['firstName']; ?> <!-- Short tag to display the link -->
        <?php else: ?>
    <!-- If 'firstName' is already set, display its value -->
       <?= $_SESSION['firstName']; ?>
        <?php endif; ?>

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
          </div>
        </li>
  </nav>

    <!-- Heading Section -->
    <h1 class="text-center">Choose the best here.</h1>

   <!-- Container for Cars (dynamic content will be injected here) -->
   <div class="row car-section" id="car-listings">
    <!-- Cars will be dynamically added here using JavaScript -->
  </div>

  <!-- Footer Section -->
  <footer>
    <div class="footer-top" id="con">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3 col-sm-6 col-xs-12">
            <a href="#" class="navbar-brand" style="font-weight: 900;color:#333;">
              <span style="color: #fff;">CARS</span>RENT
            </a>
            <p class="content1">Best cars at low cost.</p>
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12">
            <h6 class="content2">TEAM</h6>
            <div class="icons">
          <a style="color: #fff;" href="https://github.com/ossama21/Cars_Rental_WebSite-Project">  <i class="fa fa-envelope" aria-hidden="true"><span style="font-family: 'montserrat';">&nbsp;&nbsp;https://github.com/ossama21/Cars_Rental_WebSite-Project</span></i></a>
           
          </div>
          </div>
        </div>
      </div>
      <div class="footer2">
        &copy;2024 <span>CARS</span> All Rights Reserved. <br> Made by: Mohammed Ali & Oussama
      </div>
    </div>
  </footer>

  <!-- Bootstrap JS and Dependencies -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <script type="module" src="products.js"></script>
  <script src="./js/dropdown.js"></script>

  <!-- Custom JavaScript file -->
  <!-- <script defer src="script.js"></script> -->
  <!-- <script type="module" src="script.js"></script> -->
  <script type="module" src="./js/script.js"></script>

  <script type="module" src="./js/checkout.js"></script>
</body>
</html>
