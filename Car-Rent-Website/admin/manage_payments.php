<?php
session_start();
include '../data/connect.php';

// Admin check
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Get admin info for display
$email = $_SESSION['email'];
$sql = "SELECT role, firstName, lastName FROM users WHERE email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Create a display name from firstName and lastName
$displayName = ($user) ? $user['firstName'] . ' ' . $user['lastName'] : $email;

// Filter by status if provided
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$whereClause = '';
if ($statusFilter) {
    $whereClause = " WHERE p.status = '" . $conn->real_escape_string($statusFilter) . "'";
}

// Get payment statistics
$statsQuery = "SELECT 
    COUNT(*) as total_payments,
    SUM(amount) as total_amount,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payments,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_payments,
    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments
    FROM payments";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

// Fetch all payments
$sqlPayments = "SELECT p.*, s.username as customer_name, s.email as customer_email, 
                s.start_date as booking_date, s.end_date as return_date, c.model 
                FROM payments p
                LEFT JOIN services s ON p.booking_id = s.id
                LEFT JOIN cars c ON s.car_id = c.id
                $whereClause
                ORDER BY p.date DESC";
$paymentsResult = $conn->query($sqlPayments);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - Admin Dashboard</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/modern.css">
    <link rel="stylesheet" href="style.css">

    <style>
        /* Enhanced Admin Layout */
        .admin-wrapper {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
            background-color: #f8fafc;
        }

        .admin-content {
            padding: 1.5rem;
            max-width: 100%;
            margin: 0;
        }

        /* Updated Top Bar and Profile Styling */
        .admin-topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 280px;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 0.75rem 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.01);
            display: flex;
            justify-content: flex-end;
            align-items: center;
            z-index: 999;
            height: 60px;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-collapsed .admin-topbar {
            left: 0;
        }

        .toggle-sidebar {
            margin-right: auto;
            background: none;
            border: none;
            color: #4a5568;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .toggle-sidebar:hover {
            color: #2d3748;
            transform: scale(1.05);
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: all 0.2s ease;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .admin-user:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .admin-user-info {
            text-align: right;
            margin-right: 0.75rem;
        }

        .admin-user-name {
            font-weight: 600;
            color: #2d3748;
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }

        .admin-user-role {
            font-size: 0.8rem;
            color: #718096;
        }

        .admin-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #e2e8f0;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .admin-user-avatar:hover {
            border-color: #4299e1;
            transform: scale(1.05);
        }

        .admin-user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Adjust main content padding to account for fixed topbar */
        .admin-main {
            padding-top: 60px;
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .admin-topbar {
                left: 0;
            }
        }

        @media (max-width: 768px) {
            .admin-topbar {
                padding: 0.5rem 1rem;
            }

            .admin-user-info {
                display: none;
            }

            .admin-user {
                padding: 0.25rem;
                background: transparent;
                box-shadow: none;
            }
        }

        /* Stats Cards Styling */
        .admin-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .admin-stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .admin-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .admin-stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .admin-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .admin-stat-icon.revenue {
            background-color: rgba(72, 187, 120, 0.1);
            color: #48bb78;
        }

        .admin-stat-icon.success {
            background-color: rgba(66, 153, 225, 0.1);
            color: #4299e1;
        }

        .admin-stat-icon.warning {
            background-color: rgba(237, 137, 54, 0.1);
            color: #ed8936;
        }

        .admin-stat-icon.danger {
            background-color: rgba(229, 62, 62, 0.1);
            color: #e53e3e;
        }

        .admin-stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0.5rem 0;
        }

        /* Table Card Styling */
        .admin-table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .admin-table-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .admin-table th {
            background: #f7fafc;
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: #4a5568;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }

        .admin-table td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            color: #2d3748;
        }

        .admin-table tr:hover {
            background-color: #f7fafc;
        }

        /* Status Badge Styling */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-completed {
            background-color: #dcf7e3;
            color: #166534;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-failed {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        /* Button Styling */
        .admin-btn {
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .admin-btn-primary {
            background-color: #4299e1;
            color: white;
        }

        .admin-btn-secondary {
            background-color: #e2e8f0;
            color: #4a5568;
        }

        .admin-btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-body dl.row {
            margin: 0;
            row-gap: 1rem;
        }

        .modal-body dt {
            font-weight: 600;
            color: #4a5568;
        }

        .modal-body dd {
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 991px) {
            .admin-wrapper {
                grid-template-columns: 1fr;
            }

            .admin-main {
                margin-left: 0;
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .admin-stats-grid {
                grid-template-columns: 1fr;
            }

            .admin-table-header {
                flex-direction: column;
                gap: 1rem;
            }

            .admin-table-actions {
                width: 100%;
                justify-content: flex-start;
                overflow-x: auto;
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
                    <a href="../index.php" class="sidebar-menu-link">
                        <i class="fas fa-home"></i>
                        <span>Back to Home</span>
                    </a>
                </div>

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
                    <a href="manage_payments.php" class="sidebar-menu-link active">
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

            <!-- Main Content -->
            <div class="admin-content p-4">
                <!-- Page Header -->
                <div class="admin-content-header">
                    <h1><i class="fas fa-money-bill-wave me-2"></i>Manage Payments</h1>
                    <p class="text-muted">Monitor and manage all payment transactions</p>
                </div>

                <!-- Statistics Cards -->
                <div class="admin-stats-grid">
                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Total Revenue</div>
                            <div class="admin-stat-icon revenue">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value">$<?php echo number_format($stats['total_amount'] ?? 0, 2); ?></div>
                        <div class="admin-stat-change positive">
                            <i class="fas fa-chart-line"></i> All time earnings
                        </div>
                    </div>

                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Completed Payments</div>
                            <div class="admin-stat-icon success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value"><?php echo number_format($stats['completed_payments'] ?? 0); ?></div>
                        <div class="admin-stat-change">
                            <i class="fas fa-check"></i> Successfully processed
                        </div>
                    </div>

                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Pending Payments</div>
                            <div class="admin-stat-icon warning">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value"><?php echo number_format($stats['pending_payments'] ?? 0); ?></div>
                        <div class="admin-stat-change">
                            <i class="fas fa-hourglass-half"></i> Awaiting processing
                        </div>
                    </div>

                    <div class="admin-stat-card">
                        <div class="admin-stat-header">
                            <div class="admin-stat-title">Failed Payments</div>
                            <div class="admin-stat-icon danger">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                        <div class="admin-stat-value"><?php echo number_format($stats['failed_payments'] ?? 0); ?></div>
                        <div class="admin-stat-change negative">
                            <i class="fas fa-exclamation-triangle"></i> Require attention
                        </div>
                    </div>
                </div>

                <!-- Payments Table Card -->
                <div class="admin-table-card mt-4">
                    <div class="admin-table-header">
                        <div class="admin-table-title">Payment Transactions</div>
                        <div class="admin-table-actions d-flex gap-2">
                            <div class="btn-group">
                                <a href="?status=" class="admin-btn <?php echo $statusFilter === '' ? 'admin-btn-primary' : 'admin-btn-secondary'; ?>">
                                    <i class="fas fa-list"></i> All
                                </a>
                                <a href="?status=completed" class="admin-btn <?php echo $statusFilter === 'completed' ? 'admin-btn-primary' : 'admin-btn-secondary'; ?>">
                                    <i class="fas fa-check"></i> Completed
                                </a>
                                <a href="?status=pending" class="admin-btn <?php echo $statusFilter === 'pending' ? 'admin-btn-primary' : 'admin-btn-secondary'; ?>">
                                    <i class="fas fa-clock"></i> Pending
                                </a>
                                <a href="?status=failed" class="admin-btn <?php echo $statusFilter === 'failed' ? 'admin-btn-primary' : 'admin-btn-secondary'; ?>">
                                    <i class="fas fa-times"></i> Failed
                                </a>
                            </div>
                            <button class="admin-btn admin-btn-secondary" onclick="exportToExcel()">
                                <i class="fas fa-file-excel me-1"></i> Export
                            </button>
                            <button class="admin-btn admin-btn-secondary" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Car Model</th>
                                    <th>Payment Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Booking Period</th>
                                    <th>Payment Date</th>
                                    <th class="no-print">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($paymentsResult && $paymentsResult->num_rows > 0): ?>
                                    <?php while ($payment = $paymentsResult->fetch_assoc()): ?>
                                        <tr>
                                            <td class="fw-medium">#<?= str_pad($payment['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-medium"><?= htmlspecialchars($payment['customer_name']); ?></span>
                                                    <small class="text-muted"><?= htmlspecialchars($payment['customer_email']); ?></small>
                                                </div>
                                            </td>
                                            <td class="fw-medium"><?= htmlspecialchars($payment['model']); ?></td>
                                            <td>
                                                <i class="fas <?= getPaymentIcon($payment['method']); ?> me-2"></i>
                                                <?= htmlspecialchars($payment['method']); ?>
                                            </td>
                                            <td class="fw-bold">$<?= number_format($payment['amount'], 2); ?></td>
                                            <td>
                                                <span class="status-badge status-<?= strtolower($payment['status'] ?? 'completed'); ?>">
                                                    <?= ucfirst($payment['status'] ?? 'Completed'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($payment['booking_date'] && $payment['return_date']): ?>
                                                    <div class="d-flex flex-column">
                                                        <small class="text-muted mb-1">From:</small>
                                                        <span class="fw-medium"><?= date('M d, Y', strtotime($payment['booking_date'])); ?></span>
                                                        <small class="text-muted mb-1 mt-2">To:</small>
                                                        <span class="fw-medium"><?= date('M d, Y', strtotime($payment['return_date'])); ?></span>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-medium"><?= date('M d, Y', strtotime($payment['date'])); ?></span>
                                                    <small class="text-muted"><?= date('H:i', strtotime($payment['date'])); ?></small>
                                                </div>
                                            </td>
                                            <td class="no-print">
                                                <button class="admin-btn admin-btn-secondary admin-btn-sm" onclick="viewDetails(<?= $payment['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <p class="text-muted mb-0">No payment records found</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Payment Details Modal -->
    <div class="modal fade" id="paymentDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-invoice me-2"></i>
                        Payment Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="paymentDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar functionality
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

        function formatData(data, defaultValue = 'N/A') {
            return data || defaultValue;
        }

        function viewDetails(paymentId) {
            const modal = new bootstrap.Modal(document.getElementById('paymentDetailsModal'));
            const contentDiv = document.getElementById('paymentDetailsContent');
            
            contentDiv.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>`;
            
            modal.show();
            
            fetch(`get_payment_details.php?id=${paymentId}`)
                .then(response => response.json())
                .then(data => {
                    contentDiv.innerHTML = `
                        <dl class="row">
                            <dt class="col-sm-4">Transaction ID</dt>
                            <dd class="col-sm-8">${formatData(data.transaction_id)}</dd>
                            
                            <dt class="col-sm-4">Customer Info</dt>
                            <dd class="col-sm-8">
                                <div class="mb-1">${formatData(data.customer_name)}</div>
                                <div class="small text-muted">${formatData(data.customer_email)}</div>
                                <div class="small text-muted">${formatData(data.phone)}</div>
                            </dd>
                            
                            <dt class="col-sm-4">Amount</dt>
                            <dd class="col-sm-8">$${parseFloat(data.amount).toFixed(2)}</dd>
                            
                            <dt class="col-sm-4">Payment Method</dt>
                            <dd class="col-sm-8">
                                <i class="fas ${getPaymentMethodIcon(data.method)} me-2"></i>
                                ${formatData(data.method)}
                            </dd>
                            
                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                <span class="status-badge status-${data.status || 'completed'}">
                                    ${data.status ? data.status.charAt(0).toUpperCase() + data.status.slice(1) : 'Completed'}
                                </span>
                            </dd>
                            
                            <dt class="col-sm-4">Payment Date</dt>
                            <dd class="col-sm-8">${new Date(data.date).toLocaleString()}</dd>
                            
                            <dt class="col-sm-4">Booking Period</dt>
                            <dd class="col-sm-8">
                                ${new Date(data.booking_date).toLocaleDateString()} - 
                                ${new Date(data.return_date).toLocaleDateString()}
                                <div class="small text-muted">${data.duration} days</div>
                            </dd>
                            
                            <dt class="col-sm-4">Car Details</dt>
                            <dd class="col-sm-8">${formatData(data.model)}</dd>
                            
                            ${data.payment_details ? `
                                <dt class="col-sm-4">Additional Info</dt>
                                <dd class="col-sm-8">${data.payment_details}</dd>
                            ` : ''}
                        </dl>
                    `;
                })
                .catch(error => {
                    contentDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Error loading payment details.
                        </div>`;
                });
        }

        function getPaymentMethodIcon(method) {
            const icons = {
                'Credit Card': 'fa-credit-card',
                'PayPal': 'fa-paypal',
                'Bank Transfer': 'fa-university',
                'Cash': 'fa-money-bill-wave'
            };
            return icons[method] || 'fa-money-check';
        }

        function exportToExcel() {
            window.location.href = 'export_payments.php';
        }

        // Add this at the end of your script
        <?php
        function getPaymentIcon($method) {
            $icons = [
                'Credit Card' => 'fa-credit-card',
                'PayPal' => 'fa-paypal',
                'Bank Transfer' => 'fa-university',
                'Cash' => 'fa-money-bill-wave'
            ];
            return $icons[$method] ?? 'fa-money-check';
        }
        ?>
    </script>
</body>
</html>
