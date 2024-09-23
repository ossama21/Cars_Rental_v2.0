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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/book.css">
    <!-- Import products.js -->

    

  </head>
  
  <body>
    <!-- Navbar Section -->
    <nav class="navbar navbar-expand-md">
    <a style="font-weight: 900;color:#333" href="#" class="navbar-brand">
        <span style="color: #ffff;">CARS</span>RENT
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a href="./index.php" class="nav-link" style="color: white;">Home</a></li>
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
