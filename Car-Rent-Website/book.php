<?php
session_start();
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
                  <b><a href="book.php" class="nav-item nav-link" style="color: white;">Book Now</a></b>
                  </li>
                  <li class="nav-item">
                   <a href="about.php" class="nav-item nav-link" style="color: white;">About Us</a>
                  </li>
                    <li class="nav-item dropdown">
                      <b><a href="#con" class="nav-item nav-link" style="color: white;">Contact Us</a></b>
                      </li>
                      <li class="nav-item dropdown">
                        <b><a href="#" class="nav-item nav-link"></a></b>
                        </li>
                      
              
       
      <!-- <div class="signup">
        <div class="nav-item dropdown">
          <b><a href="./data/index.php" class="nav-item nav-link" style="color: white;">Sign Up</a></b>
        </div>
      </div> -->
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
  </nav>

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
    <script src="./js/dropdown.js"></script>
    <script type='module' src="./js/script.js"></script>
    <script type="module" src="./js/checkout.js"></script>
</body>
</html>
