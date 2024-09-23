<?php
session_start();
include '../data/connect.php';
// Admin check
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
// Fetch all users
$sqlUsers = "SELECT * FROM users";
$usersResult = $conn->query($sqlUsers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
   
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #0d6efd;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
        }
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        .table th {
            background-color: #0d6efd;
            color: #ffffff;
            font-weight: 500;
            text-transform: uppercase;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-action {
            padding: 5px 10px;
            font-size: 0.875rem;
            margin: 2px;
        }
        .btn-edit {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }
        .btn-delete {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-back {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .top-actions {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-users me-2"></i>Manage Users</h2>
        
        <div class="top-actions d-flex justify-content-between align-items-center">
            <a href="../admin/admin.php" class="btn btn-back"><i class="fas fa-arrow-left me-2"></i>Back to Admin Panel</a>
            <button class="btn btn-success" onclick="alert('Add user functionality not implemented')"><i class="fas fa-user-plus me-2"></i>Add New User</button>
        </div>
       
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $usersResult->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']); ?></td>
                            <td><?= htmlspecialchars($user['firstName']); ?></td>
                            <td><?= htmlspecialchars($user['lastName']); ?></td>
                            <td><?= htmlspecialchars($user['email']); ?></td>
                            <td><span class="badge bg-<?= $user['role'] === 'admin' ? 'primary' : 'secondary' ?>"><?= ucfirst(htmlspecialchars($user['role'])); ?></span></td>
                            <td>
                                <a href="edit_user.php?id=<?= $user['id']; ?>" class="btn btn-action btn-edit"><i class="fas fa-edit me-1"></i>Edit</a>
                                <a href="delete_user.php?id=<?= $user['id']; ?>" class="btn btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fas fa-trash-alt me-1"></i>Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>