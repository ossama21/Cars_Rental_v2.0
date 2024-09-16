<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details of Users</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
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
        session_start();
        $date = date("Y-m-d");
        
        echo "Username: " . $_SESSION['username'] . "<br>";
        echo "Address: " . $_SESSION['address'] . "<br>";
        echo "Starting Date: " . $date . "<br>";
        echo "Price: $ " . $_SESSION['days'] * 25;
        ?>
    </div>

    <h3>Go to home page</h3>
    <a href="index.html">
        <button class="button">Click Here</button>
    </a>
</body>
</html>
