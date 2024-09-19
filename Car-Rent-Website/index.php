<?php
session_start();
// include('homepage.php');
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../image.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="../hello.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <title>CARrent</title>
    <style>
        * {
  box-sizing: border-box;
}

.bg-img {
  background-image: url(images/4.jpg?auto=compress&cs=tinysrgb&h=650&w=940);
  
  /* control height of img */
  min-height: 450px;
  
  /* center and scale img */
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
  background-repeat: no-repeat;
  background-attachment: fixed;
}

/* add style to container */


/* input fields */
input[type=text], input[type=password] {
  width: 100%;
  padding: 15px;
  margin: 5px 0 22px 0;
  border: none;
  background: #f1f1f1;
}

input[type=text]:focus, input[type=password]:focus {
  background-color: #ddd;
  outline: none;
}

/* style for submit btn */
.btn {
  background-color: #f30c0c;
  color: white;
  padding: 16px 20px;
  border: none;
  cursor: pointer;
  width: 100%;
  opacity: 0.9;
}

.btn:hover {
  opacity: 1;
}


.about-div{
    width:100%;
    height: auto;
    padding: 1px;
    background-color: #f0e5fc;
    display: flex;
    justify-content: space-around;
    align-items: flex-start;
    flex-wrap: wrap;
}
.blog-line{
    position: relative;
    width:150px;
    height: 8px;
    background-image: linear-gradient(-45deg,aqua,pink,aquamarine);
    border-radius: 20px;
    left:0px;
}
.blog-txt a{
    text-decoration: none;
    color:#f15;
    font-size:30px;
  font-weight:bolder;
  font-family:'Cookie', cursive;
}
.about-div img{
    width:360px;
    height: 360px;
    border-radius: 8px;
    border:8px solid rgb(8, 8, 8);
    display: inline;
    margin-top: 10px;
    box-shadow: 0 3px 10px 0 rgba(100,100,100,.7);
    transition-duration: .3s;
}

.about-div img:hover{
    transform: scale(0.8);
}

.about-txt{
    width:60%;
    height: auto;
    font-size: 22px;
    color:#333;
    
    line-height: 30px;
}
.about-txt a{
    text-decoration: none;
    font-size: 25px;
    color:rgb(8, 8, 8);
    margin-right: 20px;
}
@media screen and (max-width:768px){
  
.about-div{
    width:100%;
    height: auto;
    padding: 0px;
    background-color: #d4f8f2;
    display: flex;
    justify-content: space-around;
    align-items: flex-start;
    flex-wrap: wrap;
}
.about-div img{
   
    border-radius: 50%;
    border:8px solid rgb(12, 12, 12);
    display: inline;
    margin-top: 10px;
    box-shadow: 0 3px 10px 0 rgba(100,100,100,.7);
    transition-duration: .3s;
}

.about-div img:hover{
    transform: scale(1.1);
}

.about-txt{
    width:80%;
    height: auto;
    font-size: 22px;
    color:#333;
    font-family: cursive;
    line-height: 30px;
}
.about-txt a{
    text-decoration: none;
    font-size: 25px;
    color:rgb(17, 16, 17);
    margin-right: 20px;
} 
}
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
        margin-top: 20px;
        font-size: 16px;
        line-height: 25px;
        
      }
.container-fluid{
  /* margin-inline: 170px; */
}
      .content2{
        margin-inline: 58px;
        margin-top: 12px;
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
      .icon-box {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 50px;
    width: 50px;
    border-radius: 10%;
    background-color: #3182ce;
    color: white;
    margin-right: 20px;
    padding: 20px;
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
      <?php
    // include './data/connect.php'; 
    
    $firstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : 'User'; 
    // $sel = "SELECT * FROM users"; 
    // $query = mysqli_query($conn, $sel);
    // $resul = mysqli_fetch_assoc($query); 
    ?>
   
  </head>
  
  <nav class="navbar navbar-expand-md">
    <a style="font-weight: 900;color:#333" href="#" class="navbar-brand">
      <span style="color: #ffff;">CARS</span>RENT
    </a>
    <div class="collapse navbar-collapse" id="navbarCollapse">
      <div class="navbar-nav ml-auto">
        <li class="nav-item">
          <a href="index.html" class="nav-item nav-link" style="color: white;">Home</a>
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
        <li class="nav-item dropdown">
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
      </div>
    </div>
  </nav>
    <div id="carouselExampleInterval" class="carousel slide" data-ride="carousel" data-interval="3000">
        <div class="carousel-inner">
          <div class="carousel-item active" >
            <img src="../wallpaperflare.com_wallpaper 1.jpg" class="d-block w-100" alt="...">
          </div>
          <div class="carousel-item" >
            <img src="../wallpaperflare.com_wallpaper 2.jpg" class="d-block w-100" alt="...">
          </div>
          <div class="carousel-item">
            <img src="../wallpaperflare.com_wallpaper 3.jpg" class="d-block w-100" alt="...">
          </div>
          <div class="carousel-item">
              <img src="../8732441.jpg" class="d-block w-100" alt="...">
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
                  src="../hicham_project.jpeg"
                  alt="HICHAM"
                  class="avatar"
                />
                <div class="avatar-info">
                  <p class="avatar-name">HICHAM</p>
                  <p class="avatar-title">CEO, Company meta</p>
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
                  src="../khalid_project.jpeg"
                  alt="KHALID"
                  class="avatar"
                />
                <div class="avatar-info">
                  <p class="avatar-name">KHALID</p>
                  <p class="avatar-title">Manager, Company steam</p>
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
                  src="../zakaria_project.jpeg"
                  alt="ZAKARIA"
                  class="avatar"
                />
                <div class="avatar-info">
                  <p class="avatar-name">ZAKARIA</p>
                  <p class="avatar-title">Director, Company apple</p>
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
          <h6 class="content2">SOCIAL MEDIA</h6>
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
 <script src="../dropdown.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>

   
    
  </body>
</html>