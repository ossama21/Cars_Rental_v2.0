<?php 
session_start(); 
include '../data/connect.php';
include '../data/auth.php';

// Language selection handling
$availableLangs = ['en', 'fr', 'ar'];
$lang_code = isset($_SESSION['lang']) && in_array($_SESSION['lang'], $availableLangs) ? $_SESSION['lang'] : 'en';

// Set html direction for Arabic
$dir = $lang_code === 'ar' ? 'rtl' : 'ltr';

// Include the selected language file
include_once "../languages/{$lang_code}.php";

checkAdminAccess();

$email = $_SESSION['email'];
// Get user info
$sql = "SELECT role, firstName, lastName FROM users WHERE email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Create a display name from firstName and lastName
$displayName = ($user) ? $user['firstName'] . ' ' . $user['lastName'] : $email;

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Items per page
$offset = ($page - 1) * $limit;

// Get bookings from services table
$sqlBookings = "SELECT s.id, 
                  s.username as user_name, 
                  c.name as car_name, 
                  c.image as car_image,
                  s.car_id,
                  s.start_date, 
                  s.end_date,
                  s.amount,
                  s.created_at,
                  'completed' as status
              FROM services s 
              JOIN cars c ON s.car_id = c.id 
              ORDER BY s.created_at DESC
              LIMIT ?, ?";

$stmtBookings = $conn->prepare($sqlBookings);
$stmtBookings->bind_param("ii", $offset, $limit);
$stmtBookings->execute();
$bookings = $stmtBookings->get_result();

// Get total bookings count for pagination
$sqlCount = "SELECT COUNT(*) as total FROM services";
$totalBookings = $conn->query($sqlCount)->fetch_assoc()['total'];
$totalPages = ceil($totalBookings / $limit);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Bookings - Admin Dashboard</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../css/modern.css">
    <link rel="stylesheet" href="../css/language-selector.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-wrapper" id="adminWrapper">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <a href="admin.php" class="admin-logo">
                    <i class="fas fa-car"></i>
                    <span>CarRental</span> Admin
                </a>
            </div>
            
            <nav class="admin-sidebar-menu">
                <div class="sidebar-menu-item">
                    <a href="admin.php" class="sidebar-menu-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="manage_cars.php" class="sidebar-menu-link">
                        <i class="fas fa-car"></i>
                        <span>Manage Cars</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="manage_users.php" class="sidebar-menu-link">
                        <i class="fas fa-users"></i>
                        <span>Manage Users</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="manage_payments.php" class="sidebar-menu-link">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Manage Payments</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="#" class="sidebar-menu-link has-submenu active" id="bookingsMenu">
                        <i class="fas fa-calendar-check"></i>
                        <span>Bookings</span>
                    </a>
                    <div class="sidebar-submenu active" id="bookingsSubmenu">
                        <div class="submenu-item">
                            <a href="bookings.php" class="active">All Bookings</a>
                        </div>
                        <div class="submenu-item">
                            <a href="pending_bookings.php">Pending Bookings</a>
                        </div>
                        <div class="submenu-item">
                            <a href="confirmed_bookings.php">Confirmed Bookings</a>
                        </div>
                        <div class="submenu-item">
                            <a href="completed_bookings.php">Completed Bookings</a>
                        </div>
                        <div class="submenu-item">
                            <a href="cancelled_bookings.php">Cancelled Bookings</a>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="#" class="sidebar-menu-link has-submenu" id="reportsMenu">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports</span>
                    </a>
                    <div class="sidebar-submenu" id="reportsSubmenu">
                        <div class="submenu-item">
                            <a href="#">Revenue Reports</a>
                        </div>
                        <div class="submenu-item">
                            <a href="#">Booking Reports</a>
                        </div>
                        <div class="submenu-item">
                            <a href="#">User Reports</a>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-menu-item">
                    <a href="#" class="sidebar-menu-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item mt-4">
                    <a href="../data/logout.php" class="sidebar-menu-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
                
                <div class="sidebar-menu-item mt-2">
                    <a href="../index.php" class="sidebar-menu-link">
                        <i class="fas fa-home"></i>
                        <span>Back to Home</span>
                    </a>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Top Bar -->
            <div class="admin-topbar">
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="d-flex align-items-center">
                    <!-- Language Selector -->
                    <div class="language-selector me-3">
                        <a href="#" class="current-lang">
                            <?php if($lang_code == 'en'): ?>
                                <i class="fas fa-flag flag-icon-uk"></i> English
                            <?php elseif($lang_code == 'fr'): ?>
                                <i class="fas fa-flag flag-icon-france"></i> Français
                            <?php elseif($lang_code == 'ar'): ?>
                                <i class="fas fa-flag flag-icon-morocco"></i> العربية
                            <?php endif; ?>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="language-dropdown">
                            <a href="../data/change-language.php?lang=en&redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>" class="language-option <?php echo $lang_code == 'en' ? 'active' : ''; ?>">
                                <i class="fas fa-flag flag-icon-uk"></i> English
                            </a>
                            <a href="../data/change-language.php?lang=fr&redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>" class="language-option <?php echo $lang_code == 'fr' ? 'active' : ''; ?>">
                                <i class="fas fa-flag flag-icon-france"></i> Français
                            </a>
                            <a href="../data/change-language.php?lang=ar&redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>" class="language-option <?php echo $lang_code == 'ar' ? 'active' : ''; ?>">
                                <i class="fas fa-flag flag-icon-morocco"></i> العربية
                            </a>
                        </div>
                    </div>
                    
                    <div class="admin-user">
                        <div class="admin-user-info">
                            <div class="admin-user-name"><?php echo htmlspecialchars($displayName); ?></div>
                            <div class="admin-user-role"><?php echo htmlspecialchars($user['role']); ?></div>
                        </div>
                        <div class="admin-user-avatar">
                            <img src="../images/profile-pic.png" alt="Admin">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bookings Management -->
            <div class="admin-dashboard">
                <h1>All Bookings</h1>
                <p class="text-muted mb-4">Manage all car rental bookings from this dashboard.</p>
                
                <!-- Booking Filters -->
                <div class="admin-card mb-4">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="admin-form-label">Search Bookings</label>
                            <input type="text" id="searchBookings" class="admin-form-input" placeholder="Search by user, car name, etc.">
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="admin-form-label">Date Range</label>
                            <input type="date" id="dateFrom" class="admin-form-input">
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="admin-form-label">To</label>
                            <input type="date" id="dateTo" class="admin-form-input">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="admin-btn admin-btn-primary w-100">Filter</button>
                        </div>
                    </div>
                </div>
                
                <!-- Bookings Table -->
                <div class="admin-table-card">
                    <div class="admin-table-header">
                        <div class="admin-table-title">Booking Records</div>
                        <div class="admin-table-actions">
                            <a href="#" class="admin-btn admin-btn-primary admin-btn-sm" onclick="exportBookings()">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Car</th>
                                    <th>User</th>
                                    <th>Pickup Date</th>
                                    <th>Return Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($bookings && $bookings->num_rows > 0): ?>
                                    <?php while($booking = $bookings->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['id']); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo htmlspecialchars($booking['car_image']); ?>" alt="<?php echo htmlspecialchars($booking['car_name']); ?>" style="width: 40px; height: 30px; object-fit: cover; margin-right: 10px;">
                                                    <?php echo htmlspecialchars($booking['car_name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($booking['start_date'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($booking['end_date'])); ?></td>
                                            <td>$<?php echo number_format($booking['amount'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-success">Completed</span>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="admin-btn admin-btn-secondary admin-btn-sm me-1" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="generate_invoice.php?id=<?php echo $booking['id']; ?>" class="admin-btn admin-btn-primary admin-btn-sm me-1" title="Generate Invoice">
                                                        <i class="fas fa-file-invoice"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">No bookings found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if($totalPages > 1): ?>
                        <div class="admin-table-pagination">
                            <div class="pagination-info">
                                Showing <?php echo min($limit, $bookings->num_rows); ?> out of <?php echo $totalBookings; ?> bookings
                            </div>
                            <div class="pagination-controls">
                                <?php if($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>" class="pagination-button">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php else: ?>
                                    <button class="pagination-button disabled">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <?php
                                // Show limited page numbers with current page in the middle
                                $startPage = max($page - 2, 1);
                                $endPage = min($startPage + 4, $totalPages);
                                
                                for($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <a href="?page=<?php echo $i; ?>" class="pagination-button <?php echo $i == $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if($page < $totalPages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>" class="pagination-button">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php else: ?>
                                    <button class="pagination-button disabled">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="js/admin_sidebar.js"></script>
    <script>
        // Language selector dropdown functionality
        const languageSelector = document.querySelector('.language-selector');
        const currentLang = document.querySelector('.current-lang');
        
        if (currentLang) {
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
        
        // Export bookings function (placeholder)
        function exportBookings() {
            alert('Export functionality will be implemented here');
            // Actual implementation would involve generating a CSV/Excel file
        }
    </script>
</body>
</html>