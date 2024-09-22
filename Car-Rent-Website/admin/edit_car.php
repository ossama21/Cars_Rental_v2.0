
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
</head>
<body>

    <h2>Edit Car</h2>
    <form method="post" action="">
        <input type="text" name="name" value="<?= $car['name'] ?>" placeholder="Car Name" required>
        <input type="number" name="price" value="<?= $car['price'] ?>" placeholder="Car Price" required>
        <textarea name="description" placeholder="Car Description"><?= $car['description'] ?></textarea>
        <input type="text" name="model" value="<?= $car['model'] ?>" placeholder="Car Model" required>
        <input type="text" name="transmission" value="<?= $car['transmission'] ?>" placeholder="Transmission" required>
        <input type="text" name="interior" value="<?= $car['interior'] ?>" placeholder="Interior" required>
        <input type="text" name="brand" value="<?= $car['brand'] ?>" placeholder="Brand" required>
        <input type="file" name="image" value="<?= $car['image'] ?>" placeholder="Image" required>
        <button type="submit" name="updateCar">Update Car</button>
    </form>

</body>
</html>
