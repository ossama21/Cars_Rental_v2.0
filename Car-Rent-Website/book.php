<?php
session_start();
$firstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : '';
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$host="localhost";
$user="root";
$pass="";
$db="car_rent";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM cars";
$result = $conn->query($sql);

$cars = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
}

// Close the database connection
$conn->close();

// Check if it's an AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Return cars as a JSON response
    echo json_encode(['cars' => $cars]);
    exit;
}
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/book.css">
    <link rel="stylesheet" href="./css/modern.css">
  </head>
  
  <body>
    <!-- Navigation Bar -->
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
                  <a href="about.php" class="nav-link">About</a>
                </li>
                <li class="nav-item">
                  <a href="book.php" class="nav-link active">Cars</a>
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

    <!-- Heading Section -->
    <h1 class="text-center">Choose the best here.</h1>

    <!-- Container for Cars -->
    <div class="row car-section" id="car-listings">
      <?php foreach ($cars as $car) { ?>
        <div class="col-md-4">
          <div class="car">
            <img src="<?php echo $car['image']; ?>" class="img-fluid" alt="<?php echo $car['name']; ?>" style="max-width:100%; height:auto;">
            <h3><?php echo $car['name']; ?></h3>
            <p><?php echo $car['description']; ?></p>
            <p><strong>Price:</strong> $<?php echo $car['price']; ?> per day</p>
            <p><strong>Model:</strong> <?php echo $car['model']; ?></p>
            <p><strong>Transmission:</strong> <?php echo $car['transmission']; ?></p>
            <p><strong>Interior:</strong> <?php echo $car['interior']; ?></p>
            <p><strong>Brand:</strong> <?php echo $car['brand']; ?></p>
          </div>
        </div>
      <?php } ?>
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
    <script type='module' src="./js/script.js"></script>
    <script type="module" src="./js/checkout.js"></script>
    <script>
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
        
        // Mobile menu toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.nav-menu');
        
        if (menuToggle) {
          menuToggle.addEventListener('click', function() {
            menuToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
            document.body.classList.toggle('menu-open');
          });
        }
      });
    </script>
  </body>
</html>
