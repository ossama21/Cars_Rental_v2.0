<?php
session_start();
$firstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : '';
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Language selection handling
$availableLangs = ['en', 'fr', 'ar'];
$lang_code = isset($_SESSION['lang']) && in_array($_SESSION['lang'], $availableLangs) ? $_SESSION['lang'] : 'en';

// Set html direction for Arabic
$dir = $lang_code === 'ar' ? 'rtl' : 'ltr';

// Include the selected language file
include_once "languages/{$lang_code}.php";

// Database connection
$host="localhost";
$user="root";
$pass="";
$db="car_rent";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!doctype html>
<html lang="<?php echo $lang_code; ?>" dir="<?php echo $dir; ?>">
  <head>
    <!-- Required meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="./images/image.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <!-- Loading animation CSS -->
    <link rel="stylesheet" href="./loading/loading.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/index2.css">
    <link rel="stylesheet" href="./css/index1.css">
    <link rel="stylesheet" href="./css/modern.css">
    <!-- Dark Mode CSS -->
    <link rel="stylesheet" href="./css/dark-mode.css">
    <!-- Language selector CSS -->
    <link rel="stylesheet" href="./css/language-selector.css">
    <!-- Mobile-specific CSS (loaded conditionally) -->
    <link rel="stylesheet" href="./css/mobile.css">

    <title><?php echo $lang['title']; ?></title>
    <style>
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
        transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
      }
      
      .loading-container {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
      }

      #loading-overlay.hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
      }

      body {
        overflow-y: auto !important;
        height: auto !important;
      }

      body.loading-active {
        overflow: hidden;
      }
      
      /* Flag Icons Styling */
      .flag-icon {
        margin-right: 5px;
        font-size: 1em;
      }
      
      html[dir="rtl"] .flag-icon {
        margin-right: 0;
        margin-left: 5px;
      }
    </style>
  </head>
  <body class="loading-active">
    <!-- Loading Animation Overlay -->
    <div id="loading-overlay">
      <div class="loading-container">
        <?php include('./loading/loading.php'); ?>
      </div>
    </div>

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
                  <a href="index.php" class="nav-link active"><?php echo $lang['home']; ?></a>
                </li>
                <li class="nav-item">
                  <a href="about.php" class="nav-link"><?php echo $lang['about']; ?></a>
                </li>
                <li class="nav-item">
                  <a href="book.php" class="nav-link"><?php echo $lang['cars']; ?></a>
                </li>
                <li class="nav-item">
                  <a href="#contact" class="nav-link"><?php echo $lang['contact']; ?></a>
                </li>
              </ul>
            </div>
          </div>

          <!-- Language Selector -->
          <div class="language-selector">
            <div class="current-lang">
              <span>
                <?php if($lang_code == 'en'): ?>
                  <i class="flag-icon fas fa-flag flag-icon-uk"></i> EN
                <?php elseif($lang_code == 'fr'): ?>
                  <i class="flag-icon fas fa-flag flag-icon-france"></i> FR
                <?php elseif($lang_code == 'ar'): ?>
                  <i class="flag-icon fas fa-flag flag-icon-morocco"></i> AR
                <?php endif; ?>
              </span>
              <i class="fas fa-chevron-down"></i>
            </div>
            <div class="language-dropdown">
              <a href="languages/change_language.php?lang=en" class="language-option">
                <i class="flag-icon fas fa-flag flag-icon-uk"></i> English
              </a>
              <a href="languages/change_language.php?lang=fr" class="language-option">
                <i class="flag-icon fas fa-flag flag-icon-france"></i> Français
              </a>
              <a href="languages/change_language.php?lang=ar" class="language-option">
                <i class="flag-icon fas fa-flag flag-icon-morocco"></i> العربية
              </a>
            </div>
          </div>

          <!-- Dark Mode Toggle -->
          <div class="theme-switch-wrapper">
            <span class="theme-switch-label"><i class="fas fa-sun"></i></span>
            <label class="theme-switch">
              <input type="checkbox" id="theme-toggle">
              <span class="slider round">
                <span class="icon sun-icon"><i class="fas fa-sun"></i></span>
                <span class="icon moon-icon"><i class="fas fa-moon"></i></span>
              </span>
            </label>
          </div>

          <!-- Authentication buttons/profile dropdown -->
          <div class="nav-buttons desktop-auth">
            <?php if (isset($_SESSION['firstName'])): ?>
              <div class="profile-dropdown">
                <button type="button" class="profile-toggle">
                  <div class="profile-avatar">
                    <img src="./images/profile-pic.png" alt="Profile">
                  </div>
                  <span class="profile-name"><?= htmlspecialchars($_SESSION['firstName']); ?></span>
                  <i class="fas fa-chevron-down"></i>
                </button>
                <div class="profile-menu">
                  <a href="./data/my_account.php" class="profile-menu-item">
                    <i class="fas fa-user"></i><?php echo $lang['my_account']; ?>
                  </a>
                  <?php if ($isAdmin): ?>
                  <a href="./admin/admin.php" class="profile-menu-item">
                    <i class="fas fa-cog"></i><?php echo $lang['admin_dashboard']; ?>
                  </a>
                  <?php endif; ?>
                  <a href="./data/logout.php" class="profile-menu-item">
                    <i class="fas fa-sign-out-alt"></i><?php echo $lang['logout']; ?>
                  </a>
                </div>
              </div>
            <?php else: ?>
              <div class="auth-buttons">
                <a href="data/authentication.php?action=login" class="nav-btn login-btn"><?php echo $lang['login']; ?></a>
                <a href="data/authentication.php?action=register" class="nav-btn signup-btn"><?php echo $lang['signup']; ?></a>
              </div>
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

    <!-- Mobile Nav Menu (displayed when menu toggle is clicked) -->
    <div class="mobile-nav">
      <ul class="mobile-nav-list">
        <li class="mobile-nav-item">
          <a href="index.php" class="mobile-nav-link active"><?php echo $lang['home']; ?></a>
        </li>
        <li class="mobile-nav-item">
          <a href="about.php" class="mobile-nav-link"><?php echo $lang['about']; ?></a>
        </li>
        <li class="mobile-nav-item">
          <a href="book.php" class="mobile-nav-link"><?php echo $lang['cars']; ?></a>
        </li>
        <li class="mobile-nav-item">
          <a href="#contact" class="mobile-nav-link"><?php echo $lang['contact']; ?></a>
        </li>
      </ul>
      
      <?php if (isset($_SESSION['firstName'])): ?>
      <!-- Mobile profile section (only when user is logged in) -->
      <div class="mobile-profile">
        <div class="mobile-profile-header">
          <div class="mobile-profile-avatar">
            <img src="./images/profile-pic.png" alt="Profile">
          </div>
          <div class="mobile-profile-info">
            <span class="mobile-profile-name"><?= htmlspecialchars($_SESSION['firstName']); ?></span>
            <?php if ($isAdmin): ?>
            <span class="mobile-profile-role">Admin</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="mobile-profile-menu">
          <a href="./data/my_account.php" class="mobile-profile-menu-item">
            <i class="fas fa-user"></i><?php echo $lang['my_account']; ?>
          </a>
          <?php if ($isAdmin): ?>
          <a href="./admin/admin.php" class="mobile-profile-menu-item">
            <i class="fas fa-cog"></i><?php echo $lang['admin_dashboard']; ?>
          </a>
          <?php endif; ?>
          <a href="./data/logout.php" class="mobile-profile-menu-item">
            <i class="fas fa-sign-out-alt"></i><?php echo $lang['logout']; ?>
          </a>
        </div>
      </div>
      <?php else: ?>
      <!-- Mobile auth buttons (only shown in mobile menu) -->
      <div class="mobile-auth">
        <a href="data/authentication.php?action=login" class="nav-btn login-btn"><?php echo $lang['login']; ?></a>
        <a href="data/authentication.php?action=register" class="nav-btn signup-btn"><?php echo $lang['signup']; ?></a>
      </div>
      <?php endif; ?>
      
      <!-- Mobile language selector -->
      <div class="mobile-language-selector">
        <div class="language-options">
          <a href="languages/change_language.php?lang=en" class="language-option">
            <i class="flag-icon fas fa-flag flag-icon-uk"></i> English
          </a>
          <a href="languages/change_language.php?lang=fr" class="language-option">
            <i class="flag-icon fas fa-flag flag-icon-france"></i> Français
          </a>
          <a href="languages/change_language.php?lang=ar" class="language-option">
            <i class="flag-icon fas fa-flag flag-icon-morocco"></i> العربية
          </a>
        </div>
      </div>
    </div>

    <!-- Hero Section with Carousel -->
    <section class="hero-section">
      <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="./images/wallpaperslide1.jpg" class="d-block w-100" alt="Luxury Car">
            <div class="carousel-caption" data-aos="fade-up" data-aos-delay="200">
              <h1><?php echo $lang['premium_cars']; ?></h1>
              <p><?php echo $lang['experience_driving']; ?></p>
              <a href="./book.php" class="btn btn-primary btn-lg"><?php echo $lang['book_now']; ?></a>
            </div>
          </div>
          <div class="carousel-item">
            <img src="./images/wallpaperslide2.jpg" class="d-block w-100" alt="SUV">
            <div class="carousel-caption" data-aos="fade-up" data-aos-delay="200">
              <h1><?php echo $lang['find_perfect_ride']; ?></h1>
              <p><?php echo $lang['wide_selection']; ?></p>
              <a href="./book.php" class="btn btn-primary btn-lg"><?php echo $lang['book_now']; ?></a>
            </div>
          </div>
          <div class="carousel-item">
            <img src="./images/wallpaperslide3.jpg" class="d-block w-100" alt="Sports Car">
            <div class="carousel-caption" data-aos="fade-up" data-aos-delay="200">
              <h1><?php echo $lang['drive_in_style']; ?></h1>
              <p><?php echo $lang['luxury_comfort']; ?></p>
              <a href="./book.php" class="btn btn-primary btn-lg"><?php echo $lang['book_now']; ?></a>
            </div>
          </div>
          <div class="carousel-item">
            <img src="./images/wallpaperslide4.jpg" class="d-block w-100" alt="Family Car">
            <div class="carousel-caption" data-aos="fade-up" data-aos-delay="200">
              <h1><?php echo $lang['hassle_free']; ?></h1>
              <p><?php echo $lang['quick_booking']; ?></p>
              <a href="./book.php" class="btn btn-primary btn-lg"><?php echo $lang['book_now']; ?></a>
            </div>
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>
    </section>

    <!-- Search Bar Section -->
    <section class="search-section">
      <div class="container">
        <div class="search-container" data-aos="fade-up">
          <h3><?php echo $lang['find_ideal_car']; ?></h3>
          <form class="search-form" action="book.php" method="GET">
            <div class="row g-3">
              <div class="col-md-3">
                <select class="form-select" name="type" required>
                  <option value=""><?php echo $lang['select_car_type']; ?></option>
                  <option value="sedan"><?php echo $lang['sedan']; ?></option>
                  <option value="suv"><?php echo $lang['suv']; ?></option>
                  <option value="luxury"><?php echo $lang['luxury']; ?></option>
                  <option value="sports"><?php echo $lang['sports']; ?></option>
                </select>
              </div>
              <div class="col-md-3">
                <input type="date" class="form-control" name="pickup_date" placeholder="<?php echo $lang['pickup_date']; ?>" required min="<?php echo date('Y-m-d'); ?>">
              </div>
              <div class="col-md-3">
                <input type="date" class="form-control" name="return_date" placeholder="<?php echo $lang['return_date']; ?>" required min="<?php echo date('Y-m-d'); ?>">
              </div>
              <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><?php echo $lang['search_cars']; ?></button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- Featured Cars Section -->
    <section class="featured-cars">
      <div class="container">
        <h2 class="section-title" data-aos="fade-up"><?php echo $lang['featured_vehicles']; ?></h2>
        <p class="section-description" data-aos="fade-up"><?php echo $lang['discover_selection']; ?></p>
        
        <div class="swiper car-slider">
          <div class="swiper-wrapper">
            <?php
            // Get featured cars from database
            $sql = "SELECT c.*, 
                    CASE 
                        WHEN d.discount_type = 'percentage' THEN c.price * (1 - d.discount_value/100)
                        WHEN d.discount_type = 'fixed' THEN c.price - d.discount_value
                        ELSE c.price 
                    END as discounted_price,
                    d.discount_type,
                    d.discount_value,
                    ci.image_path as primary_image,
                    CONCAT('../Car-Rent-Website/images/cars/index_cars', c.id, '.jpg') as index_image
                    FROM cars c 
                    LEFT JOIN car_discounts d ON c.id = d.car_id 
                        AND CURRENT_TIMESTAMP BETWEEN d.start_date AND d.end_date 
                    LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_primary = 1
                    ORDER BY RAND() 
                    LIMIT 4";
            
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($car = $result->fetch_assoc()) {
                    // Check for index-specific image first
                    if (file_exists($car['index_image'])) {
                        $imagePath = $car['index_image'];
                    } 
                    // Fall back to primary image if exists
                    else if (!empty($car['primary_image'])) {
                        $imagePath = $car['primary_image'];
                    }
                    // Fall back to legacy image if exists
                    else if (!empty($car['image'])) {
                        $imagePath = $car['image'];
                    }
                    // Finally fall back to placeholder
                    else {
                        $imagePath = 'images/car-placeholder.png';
                    }
                    $price = isset($car['discounted_price']) ? $car['discounted_price'] : $car['price'];
            ?>
            <div class="swiper-slide">
              <div class="car-card" data-aos="fade-up">
                <div class="car-image">
                  <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>">
                  <?php if(isset($car['discount_value'])): ?>
                    <div class="car-tag"><?php echo $lang['special_offer']; ?></div>
                  <?php endif; ?>
                </div>
                <div class="car-info">
                  <h3><?php echo htmlspecialchars($car['name']); ?></h3>
                  <div class="car-specs">
                    <span><i class="fas fa-cog"></i> <?php echo htmlspecialchars($car['transmission']); ?></span>
                    <span><i class="fas fa-gas-pump"></i> <?php echo htmlspecialchars($car['fuel_type'] ?? 'Petrol'); ?></span>
                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($car['seating_capacity'] ?? '5'); ?> <?php echo $lang['seats']; ?></span>
                  </div>
                  <div class="car-price">
                    <h4>$<?php echo number_format($price, 2); ?> <span><?php echo $lang['day']; ?></span></h4>
                    <?php if (isset($_SESSION['firstName'])): ?>
                      <a href="checkout.php?car_id=<?php echo $car['id']; ?>" class="btn btn-sm btn-primary"><?php echo $lang['rent_now']; ?></a>
                    <?php else: ?>
                      <a href="data/authentication.php?action=login" class="btn btn-sm btn-primary"><?php echo $lang['login_to_rent']; ?></a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            <?php
                }
            }
            ?>
          </div>
          <div class="swiper-pagination"></div>
          <div class="swiper-button-next"></div>
          <div class="swiper-button-prev"></div>
        </div>
      </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
      <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
          <span class="section-subtitle"><?php echo $lang['our_advantages']; ?></span>
          <h2 class="section-title"><?php echo $lang['why_choose']; ?> <span class="highlight">CARSRENT</span></h2>
          <p class="section-description"><?php echo $lang['experience_blend']; ?></p>
        </div>

        <div class="features">
          <div class="feature" data-aos="fade-up">
            <div class="feature-wrapper">
              <div class="icon-box">
                <div class="icon">
                  <i class="fas fa-car-side"></i>
                </div>
              </div>
              <div class="feature-content">
                <h3 class="feature-title"><?php echo $lang['premium_fleet']; ?></h3>
                <p class="feature-description"><?php echo $lang['premium_fleet_desc']; ?></p>
                <ul class="feature-list">
                  <li><i class="fas fa-check"></i> <?php echo $lang['latest_models']; ?></li>
                  <li><i class="fas fa-check"></i> <?php echo $lang['regular_maintenance']; ?></li>
                  <li><i class="fas fa-check"></i> <?php echo $lang['variety_brands']; ?></li>
                </ul>
              </div>
            </div>
          </div>

          <div class="feature" data-aos="fade-up" data-aos-delay="100">
            <div class="feature-wrapper">
              <div class="icon-box">
                <div class="icon">
                  <i class="fas fa-hand-holding-dollar"></i>
                </div>
              </div>
              <div class="feature-content">
                <h3 class="feature-title"><?php echo $lang['best_value']; ?></h3>
                <p class="feature-description"><?php echo $lang['best_value_desc']; ?></p>
                <ul class="feature-list">
                  <li><i class="fas fa-check"></i> <?php echo $lang['transparent_pricing']; ?></li>
                  <li><i class="fas fa-check"></i> <?php echo $lang['member_discounts']; ?></li>
                  <li><i class="fas fa-check"></i> <?php echo $lang['flexible_plans']; ?></li>
                </ul>
              </div>
            </div>
          </div>

          <div class="feature" data-aos="fade-up" data-aos-delay="200">
            <div class="feature-wrapper">
              <div class="icon-box">
                <div class="icon">
                  <i class="fas fa-headset"></i>
                </div>
              </div>
              <div class="feature-content">
                <h3 class="feature-title"><?php echo $lang['support_24_7']; ?></h3>
                <p class="feature-description"><?php echo $lang['support_desc']; ?></p>
                <ul class="feature-list">
                  <li><i class="fas fa-check"></i> <?php echo $lang['assistance_24_7']; ?></li>
                  <li><i class="fas fa-check"></i> <?php echo $lang['roadside_support']; ?></li>
                  <li><i class="fas fa-check"></i> <?php echo $lang['quick_response']; ?></li>
                </ul>
              </div>
            </div>
          </div>

          <div class="feature" data-aos="fade-up" data-aos-delay="300">
            <div class="feature-wrapper">
              <div class="icon-box">
                <div class="icon">
                  <i class="fas fa-shield-heart"></i>
                </div>
              </div>
              <div class="feature-content">
                <h3 class="feature-title"><?php echo $lang['safety_first']; ?></h3>
                <p class="feature-description"><?php echo $lang['safety_desc']; ?></p>
                <ul class="feature-list">
                  <li><i class="fas fa-check"></i> <?php echo $lang['full_insurance']; ?></li>
                  <li><i class="fas fa-check"></i> <?php echo $lang['sanitized_vehicles']; ?></li>
                  <li><i class="fas fa-check"></i> <?php echo $lang['safety_protocols']; ?></li>
                </ul>
              </div>
            </div>
          </div>

          <div class="feature" data-aos="fade-up" data-aos-delay="400">
            <div class="feature-wrapper">
              <div class="icon-box">
                <div class="icon">
                  <i class="fas fa-bolt"></i>
                </div>
              </div>
              <div class="feature-content">
                <h3 class="feature-title"><?php echo $lang['express_service']; ?></h3>
                <p class="feature-description"><?php echo $lang['express_desc']; ?></p>
                <ul class="feature-list">
                  <li><i class="fas fa-check"></i> <?php echo $lang['fast_booking']; ?></li>
                  <li><i class="fas fa-check"></i> <?php echo $lang['express_pickup']; ?></li>
                  <li><i class="fas fa-check"></i> <?php echo $lang['digital_contracts']; ?></li>
                </ul>
              </div>
            </div>
          </div>

          <div class="feature" data-aos="fade-up" data-aos-delay="500">
            <div class="feature-wrapper">
              <div class="icon-box">
                <div class="icon">
                  <i class="fas fa-calendar-check"></i>
                </div>
              </div>
              <div class="feature-content">
                <h3 class="feature-title"><?php echo $lang['flexible_rentals']; ?></h3>
                <p class="feature-description"><?php echo $lang['flexible_desc']; ?></p>
                <ul class="feature-list">
                  <li><i class="fas fa-check"></i> <?php echo $lang['custom_duration']; ?></li>
                  <li><i class="fas fa-check"></i> <?php echo $lang['free_cancellation']; ?></li>
                  <li><i class="fas fa-check"></i> <?php echo $lang['easy_extension']; ?></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-6 mb-4 mb-md-0">
            <div class="stats-content" data-aos="fade-right">
              <span class="section-subtitle"><?php echo $lang['our_numbers']; ?></span>
              <h2 class="section-title"><?php echo $lang['trusted_by']; ?></h2>
              <p class="section-description"><?php echo $lang['we_take_pride']; ?></p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="stats-wrapper">
              <div class="stat-item" data-aos="fade-up">
                <div class="stat-icon-wrapper">
                  <i class="fas fa-car"></i>
                  <div class="stat-highlight"></div>
                </div>
                <div class="stat-details">
                  <div class="stat-number" data-target="150">0</div>
                  <div class="stat-label"><?php echo $lang['premium_vehicles']; ?></div>
                  <div class="stat-progress">
                    <div class="progress-bar"></div>
                  </div>
                </div>
              </div>

              <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-icon-wrapper">
                  <i class="fas fa-users"></i>
                  <div class="stat-highlight"></div>
                </div>
                <div class="stat-details">
                  <div class="stat-number" data-target="5000">0</div>
                  <div class="stat-label"><?php echo $lang['happy_clients']; ?></div>
                  <div class="stat-progress">
                    <div class="progress-bar"></div>
                  </div>
                </div>
              </div>

              <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-icon-wrapper">
                  <i class="fas fa-map-marker-alt"></i>
                  <div class="stat-highlight"></div>
                </div>
                <div class="stat-details">
                  <div class="stat-number" data-target="25">0</div>
                  <div class="stat-label"><?php echo $lang['locations']; ?></div>
                  <div class="stat-progress">
                    <div class="progress-bar"></div>
                  </div>
                </div>
              </div>

              <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-icon-wrapper">
                  <i class="fas fa-thumbs-up"></i>
                  <div class="stat-highlight"></div>
                </div>
                <div class="stat-details">
                  <div class="stat-number" data-target="99">0</div>
                  <div class="stat-label"><?php echo $lang['satisfaction_rate']; ?></div>
                  <div class="stat-progress">
                    <div class="progress-bar"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
      <div class="container">
        <h2 class="section-title" data-aos="fade-up"><?php echo $lang['client_saying']; ?></h2>
        <p class="section-description" data-aos="fade-up"><?php echo $lang['hear_directly']; ?></p>
        
        <div class="swiper testimonials-slider" data-aos="fade-up">
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <div class="testimonial">
                <div class="testimonial-content">
                  <div class="rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                  </div>
                  <h3 class="testimonial-heading"><?php echo $lang['exceptional_service']; ?></h3>
                  <p class="testimonial-text"><?php echo $lang['testimonial1']; ?></p>
                </div>
                <div class="testimonial-avatar">
                  <img src="./images/person_1.jpg" alt="HICHAM" class="avatar"/>
                  <div class="avatar-info">
                    <p class="avatar-name">HICHAM</p>
                    <p class="avatar-title"><?php echo $lang['ceo']; ?>, Company data</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="swiper-slide">
              <div class="testimonial">
                <div class="testimonial-content">
                  <div class="rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                  </div>
                  <h3 class="testimonial-heading"><?php echo $lang['highly_recommended']; ?></h3>
                  <p class="testimonial-text"><?php echo $lang['testimonial2']; ?></p>
                </div>
                <div class="testimonial-avatar">
                  <img src="./images/person_3.jpg" alt="KHALID" class="avatar"/>
                  <div class="avatar-info">
                    <p class="avatar-name">KHALID</p>
                    <p class="avatar-title"><?php echo $lang['manager']; ?>, Company streaming</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="swiper-slide">
              <div class="testimonial">
                <div class="testimonial-content">
                  <div class="rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                  </div>
                  <h3 class="testimonial-heading"><?php echo $lang['reliable_choice']; ?></h3>
                  <p class="testimonial-text"><?php echo $lang['testimonial3']; ?></p>
                </div>
                <div class="testimonial-avatar">
                  <img src="./images/person_4.jpg" alt="HAMZA" class="avatar"/>
                  <div class="avatar-info">
                    <p class="avatar-name">HAMZA</p>
                    <p class="avatar-title"><?php echo $lang['supervisor']; ?>, Hotel company</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-container" data-aos="fade-up">
          <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
              <div class="cta-content">
                <span class="cta-subtitle"><?php echo $lang['start_journey']; ?></span>
                <h2><?php echo $lang['ready_experience']; ?></h2>
                <p><?php echo $lang['join_thousands']; ?></p>
                <div class="cta-features">
                  <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $lang['instant_booking']; ?></span>
                  </div>
                  <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $lang['support_24_7']; ?></span>
                  </div>
                  <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $lang['free_cancellation']; ?></span>
                  </div>
                </div>
                <div class="cta-buttons">
                  <a href="./book.php" class="btn btn-primary btn-lg"><?php echo $lang['book_now']; ?></a>
                  <a href="about.php" class="btn btn-outline-light btn-lg"><?php echo $lang['learn_more']; ?></a>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="cta-image">
                <img src="./images/img21.png" alt="Luxury Car" class="floating-image">
                <div class="cta-shapes">
                  <div class="shape shape-1"></div>
                  <div class="shape shape-2"></div>
                  <div class="shape shape-3"></div>
                </div>
              </div>
            </div>
          </div>
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
              <p class="mt-3"><?php echo $lang['providing_quality']; ?></p>
              <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
              </div>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
              <h5><?php echo $lang['quick_links']; ?></h5>
              <ul class="footer-links">
                <li><a href="index.php"><?php echo $lang['home']; ?></a></li>
                <li><a href="./book.php"><?php echo $lang['book_now']; ?></a></li>
                <li><a href="about.php"><?php echo $lang['about']; ?></a></li>
                <li><a href="#contact"><?php echo $lang['contact']; ?></a></li>
              </ul>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
              <h5><?php echo $lang['contact_info']; ?></h5>
              <ul class="contact-info">
                <li><i class="fas fa-map-marker-alt"></i> 99 Ahadaf Street, Meknes, Morocco</li>
                <li><i class="fas fa-phone"></i> +212 630352250</li>
                <li><i class="fas fa-envelope"></i> ossamahattan@gmail.com</li>
              </ul>
            </div>
            
            <div class="col-lg-3 col-md-6">
              <h5><?php echo $lang['team']; ?></h5>
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
              <p class="mb-0">&copy;2024 <span>CARS</span>RENT - <?php echo $lang['all_rights_reserved']; ?></p>
            </div>
            <div class="col-md-6 text-md-end">
              <p class="mb-0"><?php echo $lang['made_by']; ?></p>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <!-- Chatbot Widget -->
    <?php include 'includes/chatbot-widget.php'; ?>

    <!-- Optional JavaScript -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    
    <!-- GSAP Animation Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/2.1.3/TweenMax.min.js"></script>
    <script src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/16327/MorphSVGPlugin.min.js"></script>
    
    <!-- Loading Animation Script -->
    <script src="./loading/loading.js"></script>
    
    <!-- Bootstrap 5 Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>
    <!-- Mobile-specific JS -->
    <script src="js/mobile.js"></script>
    <!-- Dark Mode JS -->
    <script src="js/theme.js"></script>
    <script>
      // Language Selector
      document.addEventListener('DOMContentLoaded', function() {
        const languageSelector = document.querySelector('.language-selector');
        const currentLang = document.querySelector('.current-lang');
        
        if (languageSelector && currentLang) {
          currentLang.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            languageSelector.classList.toggle('active');
          });
          
          // Close dropdown when clicking outside
          document.addEventListener('click', function(e) {
            if (!languageSelector.contains(e.target)) {
              languageSelector.classList.remove('active');
            }
          });
        }
      });
      
      window.addEventListener('load', function() {
        const loadingOverlay = document.getElementById('loading-overlay');
        const body = document.body;
        
        if (loadingOverlay) {
          setTimeout(function() {
            loadingOverlay.classList.add('hidden');
            body.classList.remove('loading-active');
          }, 1500);
        }
      });
      
      // Initialize AOS
      AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        mirror: false
      });
      
      // Navbar color change on scroll
      $(window).scroll(function() {
        if ($(window).scrollTop() > 50) {
          $('.navbar').addClass('scrolled');
        } else {
          $('.navbar').removeClass('scrolled');
        }
      });
      
      // Initialize Swiper for car slider
      const carSwiper = new Swiper('.car-slider', {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,
        autoplay: {
          delay: 3000,
          disableOnInteraction: false,
        },
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
        breakpoints: {
          640: {
            slidesPerView: 2,
          },
          1024: {
            slidesPerView: 3,
          },
        }
      });
      
      // Initialize Swiper for testimonials
      const testimonialSwiper = new Swiper('.testimonials-slider', {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        autoplay: {
          delay: 5000,
          disableOnInteraction: false,
        },
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
        breakpoints: {
          768: {
            slidesPerView: 2,
          },
          1024: {
            slidesPerView: 3,
          },
        }
      });
      
      // Mobile menu toggle
      document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const mobileNav = document.querySelector('.mobile-nav'); // Changed to target the correct element
        const body = document.body;
        
        if (menuToggle && mobileNav) {
          menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            menuToggle.classList.toggle('active');
            mobileNav.classList.toggle('active'); // Now targeting the mobile-nav element
            body.classList.toggle('menu-open');
          });
          
          // Close menu when clicking outside
          document.addEventListener('click', function(e) {
            if (!menuToggle.contains(e.target) && !mobileNav.contains(e.target)) {
              menuToggle.classList.remove('active');
              mobileNav.classList.remove('active');
              body.classList.remove('menu-open');
            }
          });
          
          // Add touch support for mobile devices
          if ('ontouchstart' in window) {
            menuToggle.addEventListener('touchstart', function(e) {
              e.stopPropagation();
            });
          }
        }
      });
      
      // Profile dropdown toggle
      document.addEventListener('DOMContentLoaded', function() {
        const profileToggle = document.querySelector('.profile-toggle');
        const profileMenu = document.querySelector('.profile-menu');
        
        if (profileToggle && profileMenu) {
          profileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            profileMenu.classList.toggle('active');
          });
          
          // Close dropdown when clicking outside
          document.addEventListener('click', function(e) {
            if (!profileToggle.contains(e.target) && !profileMenu.contains(e.target)) {
              profileMenu.classList.remove('active');
            }
          });
          
          // Support for touch devices
          if ('ontouchstart' in window) {
            profileToggle.addEventListener('touchstart', function(e) {
              e.stopPropagation();
            });
          }
        }
      });
      
      // Smooth scroll for anchor links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          const targetId = this.getAttribute('href');
          if (targetId === '#') return;
          
          e.preventDefault();
          const targetElement = document.querySelector(targetId);
          
          if (targetElement) {
            const headerOffset = 100;
            const elementPosition = targetElement.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            
            window.scrollTo({
              top: offsetPosition,
              behavior: 'smooth'
            });
            
            // Close mobile menu if open
            const menuToggle = document.querySelector('.menu-toggle');
            const navMenu = document.querySelector('.nav-menu');
            if (menuToggle && navMenu) {
              menuToggle.classList.remove('active');
              navMenu.classList.remove('active');
              document.body.classList.remove('menu-open');
            }
          }
        });
      });
      
      // Stats Counter Animation
      function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-target'));
        const duration = 2000; // 2 seconds
        const step = target / (duration / 16); // 60fps
        let current = 0;

        const timer = setInterval(() => {
          current += step;
          if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
          } else {
            element.textContent = Math.floor(current);
          }
        }, 16);
      }

      // Create and animate floating particles
      function createParticles() {
        const particlesContainer = document.querySelector('.floating-particles');
        const particleCount = 50;

        for (let i = 0; i < particleCount; i++) {
          const particle = document.createElement('div');
          particle.className = 'particle';
          particle.style.setProperty('--size', Math.random() * 3 + 1 + 'px');
          particle.style.setProperty('--left', Math.random() * 100 + '%');
          particle.style.setProperty('--delay', Math.random() * 5 + 's');
          particle.style.setProperty('--duration', Math.random() * 10 + 10 + 's');
          particlesContainer.appendChild(particle);
        }
      }

      // Initialize stats animations when they come into view
      const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            // Start counter animations
            entry.target.querySelectorAll('.stat-number').forEach(counter => {
              animateCounter(counter);
            });
            // Create particles
            createParticles();
            statsObserver.unobserve(entry.target);
          }
        });
      }, { threshold: 0.2 });

      // Observe the stats section
      const statsSection = document.querySelector('.stats-section');
      if (statsSection) {
        statsObserver.observe(statsSection);
      }
    </script>
  </body>
</html>
