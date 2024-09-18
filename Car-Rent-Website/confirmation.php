<?php
// Get booking details
$name = $_POST['name'];
$phone = $_POST['phone'];
$startDate = $_POST['startDate'];
$endDate = $_POST['endDate'];
$quantity = $_POST['quantity'];

// Calculate duration of rental
$startDateObj = new DateTime($startDate);
$endDateObj = new DateTime($endDate);
$duration = $startDateObj->diff($endDateObj)->days;

// Get car details
$carName = "Hyundai i20"; // You would replace this with dynamic data

// Display confirmation
echo "<h1>Congratulations, $name!</h1>";
echo "<p>You have successfully reserved the car: $carName</p>";
echo "<p>Phone: $phone</p>";
echo "<p>Start Date: $startDate</p>";
echo "<p>End Date: $endDate</p>";
echo "<p>Duration: $duration days</p>";
echo "<p>Quantity: $quantity</p>";

    if (!$con) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    // Insert the booking information into the database
    $insertQuery = "INSERT INTO `services` (`username`, `phone`, `start_date`, `end_date`, `quantity`, `duration`) 
                    VALUES ('$username', '$phone', '$startDate', '$endDate', '$quantity', '$duration')";

    if (mysqli_query($con, $insertQuery)) {
        // Success message and redirect
        echo "<script>
                alert('Booking Registered Successfully');
              </script>";

        // Store data in session for use in other pages
        $_SESSION['username'] = $username;
        $_SESSION['phone'] = $phone;
        $_SESSION['startDate'] = $startDate;
        $_SESSION['endDate'] = $endDate;
        $_SESSION['quantity'] = $quantity;
        $_SESSION['duration'] = $duration;
    } else {
        echo "Error: " . $insertQuery . "<br>" . mysqli_error($con);
    }

    // Close the database connection
    mysqli_close($con);

?>
<style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #3182ce, #63b3ed);
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1, h3 {
            text-align: center;
            color: white;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            background-color: rgba(49, 130, 206, 0.9);
            padding: 20px;
            border-radius: 10px;
            margin: 20px auto;
            width: 80%;
            font-size: 2rem;
        }

        h3 {
            margin-top: 30px;
        }

        .content {
            text-align: center;
            font-size: 1.2rem;
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            margin: 20px auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .button {
            background: linear-gradient(45deg, #3182ce, #63b3ed);
            border: none;
            color: white;
            padding: 15px 32px;
            font-size: 24px;
            border-radius: 30px;
            text-decoration: none;
            display: inline-block;
            margin: 20px auto;
            cursor: pointer;
            transition: background 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .button:hover {
            background: linear-gradient(45deg, #63b3ed, #3182ce);
        }

        a {
            text-align: center;
            display: block;
        }
    </style>
</head>
<body>
    <h1>Thank You For Using Our Services</h1>
    
    <div class="content">
        <?php
       
        $date = date("Y-m-d");
        
        // echo "Username: " . $_SESSION['username'] . "<br>";
        // echo "Address: " . $_SESSION['address'] . "<br>";
        // echo "Starting Date: " . $date . "<br>";
        // echo "Price: $ " . $_SESSION['days'] * 25;
     echo "User Name: " . $_SESSION['username']. "<br> ";
     echo  "Phone: ". $_SESSION['phone'] ."<br>";
     echo  "Start Date: ". $_SESSION['startDate']."<br>";
     echo  "End Date: ". $_SESSION['endDate'] ."<br>";
     echo  "Quantity: ". $_SESSION['quantity']."  DAYS"."<br>";
     echo  "Duration: ". $_SESSION['duration']. "<br>";
        ?>
    </div>

    <h3>Go to home page</h3>
    <a href="index.php">
        <button class="button">Click Here</button>
    </a>
</body>
</html>
