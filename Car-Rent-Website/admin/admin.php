<?php session_start(); include '../data/connect.php';
if (!isset($_SESSION['email'])) {
    header('Location: ../index.php');
    exit();
}
$email = $_SESSION['email'];
$sql = "SELECT role FROM users WHERE email='$email'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <header class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-800">Admin Dashboard</h1>
            <p class="text-gray-600 mt-2">Welcome back, <?php echo htmlspecialchars($email); ?></p>
        </header>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden transform transition duration-500 hover:scale-105">
                <div class="p-6 bg-blue-600">
                    <i class="fas fa-car text-white text-4xl mb-4"></i>
                    <h2 class="text-xl font-semibold text-white">Manage Cars</h2>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-4">Add, edit, or remove cars from the system.</p>
                    <a href="manage_cars.php" class="inline-block bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition duration-300">Go to Cars</a>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden transform transition duration-500 hover:scale-105">
                <div class="p-6 bg-green-600">
                    <i class="fas fa-users text-white text-4xl mb-4"></i>
                    <h2 class="text-xl font-semibold text-white">Manage Users</h2>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-4">View and manage user accounts and permissions.</p>
                    <a href="manage_users.php" class="inline-block bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition duration-300">Go to Users</a>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden transform transition duration-500 hover:scale-105">
                <div class="p-6 bg-purple-600">
                    <i class="fas fa-credit-card text-white text-4xl mb-4"></i>
                    <h2 class="text-xl font-semibold text-white">Manage Payments</h2>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-4">Review and process payment transactions.</p>
                    <a href="manage_payments.php" class="inline-block bg-purple-600 text-white py-2 px-4 rounded hover:bg-purple-700 transition duration-300">Go to Payments</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>