<?php
session_start();
include '../data/connect.php';

if (isset($_GET['id'])) {
    $carId = $_GET['id'];
    $carQuery = "SELECT * FROM cars WHERE id=$carId";
    $carResult = $conn->query($carQuery);
    $car = $carResult->fetch_assoc();

    if (isset($_POST['updateCar'])) {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $model = $_POST['model'];
        $transmission = $_POST['transmission'];
        $interior = $_POST['interior'];
        $brand = $_POST['brand'];

        $updateQuery = "UPDATE cars SET 
            name='$name', price='$price', description='$description',
            model='$model', transmission='$transmission',
            interior='$interior', brand='$brand'
            WHERE id=$carId";

        if ($conn->query($updateQuery) === TRUE) {
            header("Location: manage_cars.php");
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            margin: 50px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            max-width: 600px;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-control, .btn {
            margin-bottom: 15px;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #007bff;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="form-container">
            <h2>Edit Car</h2>
            <form method="post" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Car Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= $car['name'] ?>" placeholder="Enter Car Name" required>
                </div>
                <div class="form-group">
                    <label for="price">Car Price ($/day)</label>
                    <input type="number" class="form-control" id="price" name="price" value="<?= $car['price'] ?>" placeholder="Enter Car Price" required>
                </div>
                <div class="form-group">
                    <label for="description">Car Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter Car Description"><?= $car['description'] ?></textarea>
                </div>
                <div class="form-group">
                    <label for="model">Car Model</label>
                    <input type="text" class="form-control" id="model" name="model" value="<?= $car['model'] ?>" placeholder="Enter Car Model" required>
                </div>
                <div class="form-group">
                    <label for="transmission">Transmission</label>
                    <input type="text" class="form-control" id="transmission" name="transmission" value="<?= $car['transmission'] ?>" placeholder="Enter Transmission Type" required>
                </div>
                <div class="form-group">
                    <label for="interior">Interior</label>
                    <input type="text" class="form-control" id="interior" name="interior" value="<?= $car['interior'] ?>" placeholder="Enter Interior Type" required>
                </div>
                <div class="form-group">
                    <label for="brand">Brand</label>
                    <input type="text" class="form-control" id="brand" name="brand" value="<?= $car['brand'] ?>" placeholder="Enter Brand Name" required>
                </div>
                <div class="form-group">
                    <label for="image">Car Image (Specify Resolution: 600x400)</label>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*" >
                </div>
                <button type="submit" name="updateCar" class="btn btn-primary btn-block">Update Car</button>
            </form>
            <a href="manage_cars.php" class="btn btn-secondary btn-block mt-3">Back to Manage Cars</a>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
