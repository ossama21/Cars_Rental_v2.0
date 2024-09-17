<?php
session_start();
// include('homepage.php');
?>
<!doctype html>
<html lang="en">
  <head>
    <title>BOOKING</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="../image.png">
    <link rel="stylesheet" href="../hello.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&family=Open+Sans&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  
  </head>

  <style>

    h1{
      font-family: 'montserrat';
      position: relative;
      top:10rem;
    }


    .card{
      margin-top: 8rem;
    }

    .card-img-top{
      padding: 25px;
      transition: transform .5s;

    }

    .button{
      float: right;
    }

    .card{
      background-color: #f5f5f5;
      border-style: none !important;
    }

    hr{
      border-color: #dbdbdb;
    }

    .card-text{
      font-weight: bold;
    }

    .card-img-top:hover{
      transform: scale(1.1);
    }

    @media (min-width: 1024px) and (max-width: 2000px){
      .car-section{
        margin: 5rem !important;
      }
    }


     /* Navbar section */

     .navbar{
      position: fixed;
      width: 100%;
      background: #3182ce!important;
      box-shadow: 0px 1px 8px rgba(0, 0, 0, 0.25);
      transition: .6s;
      z-index: 10;
    }

    .logo-one{
      height:50px; 
      margin: 10px 50px 10px;
    }

    .navbar-nav{
      font-family: 'montserrat';
      font-weight: bold;
    }

    .nav-link{
      margin-right: 10px;
      color: #fff !important;
    }

    .nav-link:hover{
      color: #F0c540 !important;
    }

    nav.sticky{
      margin-right: 5px;
    }

    @media (max-width:768px){

      .navbar{
        z-index: 10;
      }

      .navbar-brand .logo-one{
        margin-left: 5px !important;
        height: 40px;
      }

      .icons{
        margin-bottom: 1rem;
      }

      .row{
        margin: 1rem;
        margin-top: 2rem;
      }

      h1{
        font-size: 1.8rem;
        top:8rem;
      }


    }

      /* footer section */
      footer{
        background: #3182ce;
        color: #fff;
        font-family: 'montserrat';
      }
      .logo2{
        height:50px; 
        margin:30px 20px 5px;
      }

      .content1{
        margin:20px;
        font-size: 16px;
        line-height: 25px;
        
      }

      .content2{
        margin-top: 50px;
        margin-left: 30px;
        font-weight: bold;
        border-bottom-width: 1px;
        border-bottom-style: solid;
        display: inline-block;
        font-family: 'open sans';
        font-size: 12px;
        letter-spacing: 2px;
      }

      .icons{
        margin-left: 14%;
        margin-top: 4%;
      }

      .icons .icon-link{
        color: #fff;
        margin-right: 10px;
      }

      .footer2{
        background: #333;
        color: grey;
        padding: 3px;
        text-align: center;
        font-family: 'open sans';
      }
      .navbar{
      position: absolute;
      width: 100%;
      background-color: #3182ce;
      box-shadow: 0px 1px 8px rgba(0, 0, 0, 0.25);
      transition: .4s;
      z-index: 10;
    }

    .logo-one{
      height:50px; 
      margin: 10px 50px 10px;
    }

    .navbar-nav{
      font-family: 'montserrat';
      font-weight: bold;
    }

    .nav-link{
      margin-right: 10px;
      color: #fff !important;
    }

    .nav-link:hover{
      color: #F0c540 !important;
    }

    nav.sticky{
      margin-right: 5px;
    }

    @media (max-width:768px){

      .navbar{
        z-index: 10;
      }

      .navbar-brand .logo-one{
        margin-left: 5px !important;
        height: 40px;
      }

      .icons{
        margin-bottom: 1rem;
      }

    }
    .navbar-list {
      width: 100%;
      text-align: right;
      padding-right: 2rem;
    }
    
    .navbar-list li {
      display: inline-block;
      margin: 0 1rem;
    }
    
    .navbar-list li a {
      font-size: 1rem;
      font-weight: 500;
      color: var(--black);
      text-decoration: none;
    }
    
    .profile-dropdown {

      position: relative;
      width: fit-content;
    }
    
    .profile-dropdown-btn {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-right: 1rem;
      font-size: 0.9rem;
      font-weight: 500;
      width: 150px;
      border-radius: 50px;
      color: var(--black);
      /* background-color: white;
      box-shadow: var(--shadow); */
    
      cursor: pointer;
      border: 1px solid var(--secondary);
      transition: box-shadow 0.2s ease-in, background-color 0.2s ease-in,
        border 0.3s;
    }
    
    .profile-dropdown-btn:hover {
      background-color: var(--secondary-light-2);
      box-shadow: var(--shadow);
    }
    
    .profile-img {
      position: relative;
      width: 3rem;
      height: 3rem;
      border-radius: 50%;
      background: url(./assets/profile-pic.jpg);
      background-size: cover;
    }
    
    .profile-img i {
      position: absolute;
      right: 0;
      bottom: 0.3rem;
      font-size: 0.5rem;
      color: var(--green);
    }
    
    .profile-dropdown-btn span {
      margin: 0 0.5rem;
      margin-right: 0;
    }
    
    .profile-dropdown-list {
      position: absolute;
      top: 68px;
      width: 220px;
      right: 0;
      background-color: var(--white);
      border-radius: 10px;
      max-height: 0;
      overflow: hidden;
      box-shadow: var(--shadow);
      transition: max-height 0.5s;
    }
    
    .profile-dropdown-list hr {
      border: 0.5px solid var(--green);
    }
    
    .profile-dropdown-list.active {
      max-height: 500px;
    }
    
    .profile-dropdown-list-item {
      padding: 0.5rem 0rem 0.5rem 1rem;
      transition: background-color 0.2s ease-in, padding-left 0.2s;
    }
    
    .profile-dropdown-list-item a {
      display: flex;
      align-items: center;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
      color: var(--black);
    }
    
    .profile-dropdown-list-item a i {
      margin-right: 0.8rem;
      font-size: 1.1rem;
      width: 2.3rem;
      height: 2.3rem;
      background-color: var(--secondary);
      color: var(--white);
      line-height: 2.3rem;
      text-align: center;
      margin-right: 1rem;
      border-radius: 50%;
      transition: margin-right 0.3s;
    }
    
    .profile-dropdown-list-item:hover {
      padding-left: 1.5rem;
      background-color: var(--secondary-light);
    }


  </style>
  <body>
      

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
              <span id="user-name-display">
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

    <h1 class="text-center">Choose the best here.</h1>

    <div class="row car-section">
      <div class="col-lg-4 mb-5">
        <div class="card shadow rounded">
          <img src="images/img1.png" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">Breeza</h5> <hr>
            <p class="card-text">25$/day <a href="mydashboard.php" class="button btn btn-dark">Book Now</a></p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-5">
        <div class="card shadow rounded">
          <img src="images/img2.png" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">Freestyle</h5> <hr>
            <p class="card-text">₹5000/day <a href="mydashboard.php" class="button btn btn-dark">Book Now</a></p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-5">
        <div class="card shadow rounded">
          <img src="images/img3.png" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">i20</h5> <hr>
            <p class="card-text">₹5000/day <a href="mydashboard.php" class="button btn btn-dark">Book Now</a></p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-5">
        <div class="card shadow rounded">
          <img src="images/img4.png" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">Swift</h5>
            <hr><p class="card-text">₹5000/day <a href="mydashboard.php" class="button btn btn-dark">Book Now</a></p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-5">
        <div class="card shadow rounded">
          <img src="images/img6.png" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">Mahindra Scorpio</h5>
            <hr><p class="card-text">₹5000/day <a href="mydashboard.php" class="button btn btn-dark">Book Now</a></p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-5">
        <div class="card shadow rounded">
          <img src="images/img7.png" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">Hexa</h5>
            <hr><p class="card-text">₹5000/day <a href="mydashboard.php" class="button btn btn-dark">Book Now</a></p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-5">
        <div class="card shadow rounded">
          <img src="images/img8.png" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">Tata Tiago</h5>
            <hr><p class="card-text">₹5000/day <a href="mydashboard.php" class="button btn btn-dark">Book Now</a></p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-5">
        <div class="card shadow rounded">
          <img src="images/img9.png" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">Baleno</h5>
            <hr><p class="card-text">₹5000/day <a href="mydashboard.php" class="button btn btn-dark">Book Now</a></p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-5">
        <div class="card shadow rounded">
          <img src="images/img10.png" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">Hyundai Creta</h5>
            <hr><p class="card-text">₹5000/day <a href="mydashboard.php" class="button btn btn-dark">Book Now</a></p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-5">
        <div class="card shadow rounded">
          <img src="images/img11.png" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">Tata Manza</h5>
            <hr><p class="card-text">₹5000/day <a href="mydashboard.php" class="button btn btn-dark">Book Now</a></p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-5">
        <div class="card shadow rounded">
          <img src="images/img12.png" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">Skoda Superb</h5>
            <hr><p class="card-text">₹5000/day <a href="mydashboard.php" class="button btn btn-dark">Book Now</a></p>
          </div>
        </div>
      </div>
    </div>


    <footer>
      <div class="footer-top" id="con">
        <div class="container">
          <div class="row">

            <div class="col-md-3 col-sm-6 col-xs-12">
              <a style="font-weight: 900;color:#333" href="#" class="navbar-brand">
                <span style="color: #ffff;" >CARS</span>RENT
             </a>
              <p class="content1">
                Best cars at low cost.
              </p>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
              <h6 class="content2">SOCIAL MEDIA</h6>
              <div class="icons">
                <a class="icon-link" href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a>
                <a class="icon-link" href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a>
                <a class="icon-link" href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a>
                <a class="icon-link" href="#"><i class="fa fa-linkedin" aria-hidden="true"></i></a><br>
               <a style="color: #fff;" href="https://github.com/ossama21/Cars_Rental_WebSite-Project"> <i class="fa fa-envelope" aria-hidden="true"><span style="font-family: 'montserrat';">&nbsp;&nbsp;https://github.com/ossama21/Cars_Rental_WebSite-Project</span></i></a>
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
    <script src="../dropdown.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>