<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Car Booking</title>
        <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <style>
            /* Global Styles */
            body {
                font-family: 'Poppins', sans-serif;
                background-color: #f4f4f9;
                color: #333;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                padding: 0;
                background: linear-gradient(135deg, #6E48AA, #9D50BB);
            }

            /* Card Container */
            .container {
                max-width: 600px;
                background-color: white;
                padding: 2rem;
                border-radius: 15px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                margin: 20px;
            }

            /* Title */
            h2 {
                text-align: center;
                margin-bottom: 1.5rem;
                color: #6E48AA;
                font-weight: bold;
            }

            /* Form Group Styles */
            .form-group label {
                font-size: 1.1rem;
                font-weight: 500;
                color: #333;
            }

            .form-control {
                padding: 0.8rem;
                font-size: 1rem;
                border-radius: 10px;
                border: 1px solid #ddd;
                transition: all 0.3s ease;
            }

            .form-control:focus {
                border-color: #9D50BB;
                box-shadow: 0 0 8px rgba(157, 80, 187, 0.3);
            }

            /* Button Styling */
            .btn-custom {
                width: 100%;
                padding: 0.8rem;
                font-size: 1.2rem;
                background-color: #6E48AA;
                color: white;
                border: none;
                border-radius: 10px;
                transition: background-color 0.3s ease;
                margin-top: 1rem;
            }

            .btn-custom:hover {
                background-color: #9D50BB;
            }

            /* Alert Styling */
            .alert {
                margin-top: 1.5rem;
                font-size: 1rem;
                text-align: center;
            }
        </style>
    </head>
    <body>

        <!-- Form Container -->
        <div class="container">
            <h2>Book Your Car</h2>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="name" class="form-control" id="username" placeholder="Enter your name" required>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" name="address" class="form-control" id="address" placeholder="Enter your address" required>
                </div>

                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" name="age" class="form-control" id="age" placeholder="Enter your age" required>
                </div>

                <div class="form-group">
                    <label for="days">Number of Days</label>
                    <input type="number" name="days" class="form-control" id="days" min="1" max="100" placeholder="Number of days" required>
                </div>

                <button type="submit" name="submit" class="btn btn-custom">Submit Booking</button>
            </form>

            <!-- Success or Error Message -->
            <?php
            session_start();
            if (isset($_POST['submit'])) {
                $username = $_POST['name'];
                $address = $_POST['address'];
                $age = $_POST['age'];
                $no_ofdays = $_POST['days'];
                
                $con = mysqli_connect('127.0.0.1', 'root', '', 'car_rent');
                if ($con == false) {
                    echo "<div class='alert alert-danger'>Error in database connection!!</div>";
                } else {
                    $insert = "INSERT INTO `services`(`username`, `address`, `age`, `no of days`) 
                               VALUES ('$username', '$address', '$age', '$no_ofdays')";
                    
                    $row = mysqli_query($con, $insert);
                    if ($row == true) {
                        ?>
                        <script> 
                            alert("Booking Registered Successfully");
                            window.open('details.php', '_self');
                        </script>
                        <?php
                        $_SESSION['username'] = $username;
                        $_SESSION['address'] = $address;
                        $_SESSION['days'] = $no_ofdays;
                    } else {
                        echo "<div class='alert alert-danger'>Error while inserting record</div>";
                    }
                }
            }
            ?>
        </div>

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
