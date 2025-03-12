<?php
session_start();
include '../data/connect.php';
include '../data/auth.php';

// Use the auth function to check admin access
checkAdminAccess();

$email = $_SESSION['email'];
// Get admin info for display
$sql = "SELECT role, firstName, lastName FROM users WHERE email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Create a display name from firstName and lastName
$displayName = ($user) ? $user['firstName'] . ' ' . $user['lastName'] : $email;

// Handle search and filtering
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';

// Build the query with filters
$sqlUsers = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sqlUsers .= " AND (firstName LIKE ? OR lastName LIKE ? OR email LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

if (!empty($role)) {
    $sqlUsers .= " AND role = ?";
    $params[] = $role;
    $types .= "s";
}

// Add sorting
$sqlUsers .= " ORDER BY id DESC";

$stmt = $conn->prepare($sqlUsers);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$usersResult = $stmt->get_result();

// Get user statistics
$sqlTotal = "SELECT COUNT(*) as total FROM users";
$totalUsers = $conn->query($sqlTotal)->fetch_assoc()['total'];

$sqlAdmins = "SELECT COUNT(*) as total FROM users WHERE role = 'admin'";
$totalAdmins = $conn->query($sqlAdmins)->fetch_assoc()['total'];

$sqlActiveUsers = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$totalActiveUsers = $conn->query($sqlActiveUsers)->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../css/modern.css">
    
    <style>
        /* Using the same styles as manage_cars.php */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 250px;
            background-color: #2d3748;
            color: #fff;
            transition: all 0.3s;
            position: fixed;
            height: 100%;
            z-index: 1000;
        }
        
        .admin-sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .admin-logo {
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu-item {
            margin-bottom: 5px;
        }
        
        .sidebar-menu-link {
            padding: 10px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }
        
        .sidebar-menu-link:hover, .sidebar-menu-link.active {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
        }
        
        .admin-main {
            flex: 1;
            margin-left: 250px;
            transition: all 0.3s;
        }
        
        .admin-content {
            padding: 30px;
        }
        
        .admin-table-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .admin-table-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-stat-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .admin-stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .admin-stat-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 1.5rem;
        }
        
        .users-icon {
            background-color: rgba(66, 153, 225, 0.1);
            color: #4299e1;
        }
        
        .admins-icon {
            background-color: rgba(237, 100, 100, 0.1);
            color: #e53e3e;
        }
        
        .active-users-icon {
            background-color: rgba(72, 187, 120, 0.1);
            color: #48bb78;
        }
        
        .admin-btn {
            padding: 8px 16px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
        }
        
        .admin-btn-primary {
            background-color: #4299e1;
            color: white;
        }
        
        .admin-btn-primary:hover {
            background-color: #3182ce;
            color: white;
        }
        
        .admin-btn-secondary {
            background-color: #e2e8f0;
            color: #4a5568;
        }
        
        .admin-btn-secondary:hover {
            background-color: #cbd5e0;
            color: #2d3748;
        }
        
        .admin-btn-danger {
            background-color: #f56565;
            color: white;
        }
        
        .admin-btn-danger:hover {
            background-color: #e53e3e;
            color: white;
        }
        
        .admin-btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        
        @media (max-width: 992px) {
            .admin-sidebar {
                left: -250px;
            }
            
            .admin-sidebar.show {
                left: 0;
            }
            
            .admin-main {
                margin-left: 0;
            }
        }
    </style>
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
                    <a href="manage_users.php" class="sidebar-menu-link active">
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
                    <a href="#" class="sidebar-menu-link has-submenu" id="bookingsMenu">
                        <i class="fas fa-calendar-check"></i>
                        <span>Bookings</span>
                    </a>
                    <div class="sidebar-submenu" id="bookingsSubmenu">
                        <div class="submenu-item">
                            <a href="#">All Bookings</a>
                        </div>
                        <div class="submenu-item">
                            <a href="#">Pending Bookings</a>
                        </div>
                        <div class="submenu-item">
                            <a href="#">Confirmed Bookings</a>
                        </div>
                        <div class="submenu-item">
                            <a href="#">Completed Bookings</a>
                        </div>
                        <div class="submenu-item">
                            <a href="#">Cancelled Bookings</a>
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
            </nav>
        </aside>
        
        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Top Bar -->
            <div class="admin-topbar">
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                
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
            
            <div class="admin-content">
                <!-- Page Header -->
                <div class="admin-content-header">
                    <h1><i class="fas fa-users me-2"></i>Manage Users</h1>
                    <p class="text-muted">Manage user accounts and permissions</p>
                </div>
                
                <!-- Users Summary Cards -->
                <div class="admin-stats-grid">
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Total Users</div>
                            <div class="admin-stat-icon users-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value"><?php echo number_format($totalUsers); ?></div>
                        <div class="admin-stat-change">
                            <i class="fas fa-user-check"></i> Registered accounts
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Admin Users</div>
                            <div class="admin-stat-icon admins-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value"><?php echo number_format($totalAdmins); ?></div>
                        <div class="admin-stat-change">
                            <i class="fas fa-lock"></i> With admin privileges
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Regular Users</div>
                            <div class="admin-stat-icon active-users-icon">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value"><?php echo number_format($totalActiveUsers); ?></div>
                        <div class="admin-stat-change">
                            <i class="fas fa-users"></i> Standard accounts
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Quick Actions</div>
                            <div class="admin-stat-icon" style="background-color: rgba(66, 153, 225, 0.1); color: #4299e1;">
                                <i class="fas fa-bolt"></i>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-2">
                            <a href="add_user.php" class="admin-btn admin-btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Add New User
                            </a>
                            <a href="#" class="admin-btn admin-btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                                <i class="fas fa-file-import me-2"></i>Import Users
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- User Management Table -->
                <div class="admin-table-card">
                    <div class="admin-table-header">
                        <div class="admin-table-title">User Accounts</div>
                        
                        <div class="admin-table-actions d-flex gap-2">
                            <div class="dropdown">
                                <button class="admin-btn admin-btn-secondary admin-btn-sm dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-file-export me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                                </ul>
                            </div>
                            
                            <a href="add_user.php" class="admin-btn admin-btn-primary admin-btn-sm">
                                <i class="fas fa-user-plus me-1"></i> Add New User
                            </a>
                        </div>
                    </div>
                    
                    <!-- Filter and Search Row -->
                    <div class="filter-row mb-4">
                        <form action="manage_users.php" method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search users..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select name="role" class="form-select">
                                    <option value="">All Roles</option>
                                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>Regular User</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="admin-btn admin-btn-primary w-100">Filter</button>
                            </div>
                            <?php if(!empty($search) || !empty($role)): ?>
                                <div class="col-12">
                                    <a href="manage_users.php" class="btn btn-link text-decoration-none">
                                        <i class="fas fa-times-circle"></i> Clear filters
                                    </a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th width="50">Avatar</th>
                                    <th>User Details</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($usersResult->num_rows > 0): ?>
                                    <?php while($user = $usersResult->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div style="width: 40px; height: 40px; background-color: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #4a5568;">
                                                    <?php echo strtoupper(substr($user['firstName'], 0, 1) . substr($user['lastName'], 0, 1)); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></div>
                                                <div class="text-muted small">ID: <?php echo htmlspecialchars($user['id']); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'success'; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Active</span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="admin-btn admin-btn-secondary admin-btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="admin-btn admin-btn-primary admin-btn-sm" title="View Details" data-bs-toggle="modal" data-bs-target="#userModal<?php echo $user['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="admin-btn admin-btn-danger admin-btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this user?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- User Details Modal -->
                                        <div class="modal fade" id="userModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="userModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="userModalLabel<?php echo $user['id']; ?>">User Details</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-4 text-center">
                                                                <div style="width: 120px; height: 120px; background-color: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 600; color: #4a5568; margin: 0 auto;">
                                                                    <?php echo strtoupper(substr($user['firstName'], 0, 1) . substr($user['lastName'], 0, 1)); ?>
                                                                </div>
                                                                <h4 class="mt-3"><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h4>
                                                                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'success'; ?> mb-3">
                                                                    <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                                                </span>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <div class="row g-3">
                                                                    <div class="col-md-6">
                                                                        <label class="fw-bold mb-1">First Name</label>
                                                                        <p><?php echo htmlspecialchars($user['firstName']); ?></p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="fw-bold mb-1">Last Name</label>
                                                                        <p><?php echo htmlspecialchars($user['lastName']); ?></p>
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <label class="fw-bold mb-1">Email Address</label>
                                                                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="fw-bold mb-1">Account Status</label>
                                                                        <p><span class="badge bg-success">Active</span></p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="fw-bold mb-1">Account Created</label>
                                                                        <p><?php echo isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A'; ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="admin-btn admin-btn-primary">
                                                            <i class="fas fa-edit me-1"></i> Edit User
                                                        </a>
                                                        <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                                <h5>No Users Found</h5>
                                                <p class="text-muted">No users match your search criteria.</p>
                                                <a href="add_user.php" class="admin-btn admin-btn-primary mt-2">
                                                    <i class="fas fa-user-plus me-2"></i>Add New User
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="admin-table-pagination">
                        <div class="pagination-info">
                            Showing <?php echo $usersResult->num_rows; ?> out of <?php echo $totalUsers; ?> users
                        </div>
                        <div class="pagination-controls">
                            <!-- Simplified pagination for now -->
                            <button class="pagination-button" disabled><i class="fas fa-angle-double-left"></i></button>
                            <button class="pagination-button" disabled><i class="fas fa-angle-left"></i></button>
                            <button class="pagination-button active">1</button>
                            <button class="pagination-button" disabled><i class="fas fa-angle-right"></i></button>
                            <button class="pagination-button" disabled><i class="fas fa-angle-double-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Import Users Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Users</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="#" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="importFile" class="form-label">Upload CSV or Excel file</label>
                            <input class="form-control" type="file" id="importFile" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                            <div class="form-text">File should contain all required user information in the correct format.</div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="headerRow" checked>
                            <label class="form-check-label" for="headerRow">File contains header row</label>
                        </div>
                        <button type="button" class="admin-btn admin-btn-primary">
                            <i class="fas fa-upload me-2"></i>Upload and Import
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Toggle sidebar
        const toggleSidebar = document.getElementById('toggleSidebar');
        const adminWrapper = document.getElementById('adminWrapper');
        const adminSidebar = document.getElementById('adminSidebar');
        
        toggleSidebar.addEventListener('click', function() {
            adminWrapper.classList.toggle('sidebar-collapsed');
            if (window.innerWidth < 992) {
                adminSidebar.classList.toggle('show');
            }
        });
        
        // Handle submenu toggles
        const submenuToggles = document.querySelectorAll('.has-submenu');
        
        submenuToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
                
                const targetId = this.getAttribute('id');
                const submenu = document.getElementById(targetId.replace('Menu', 'Submenu'));
                
                if (submenu) {
                    submenu.classList.toggle('active');
                }
            });
        });
    </script>
</body>
</html>