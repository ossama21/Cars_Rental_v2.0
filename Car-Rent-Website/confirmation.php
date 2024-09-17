<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .thank-you {
            font-size: 4rem;
            color: blue;
            text-align: center;
            position: relative;
            margin-bottom: 30px;
        }

        .fireworks {
            position: absolute;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            animation: fireworks 1.5s ease-in-out infinite;
        }

        .fireworks-left {
            top: -50px;
            left: -60px;
            background-color: red;
        }

        .fireworks-right {
            top: -50px;
            right: -60px;
            background-color: yellow;
        }

        @keyframes fireworks {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.5);
                opacity: 0.5;
            }
        }

        .info-container {
            background-color: #fff;
            border: 2px solid #ddd;
            padding: 20px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .info-section {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .info-section:last-child {
            border-bottom: none;
        }

        .info-header {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .info-detail {
            margin-left: 20px;
        }
    </style>
</head>
<body>

    <!-- Fireworks effect and Thank You message -->
    <div class="thank-you">
        THANK YOU
        <div class="fireworks fireworks-left"></div>
        <div class="fireworks fireworks-right"></div>
    </div>

    <!-- User information -->
    <div class="info-container">
        <div class="info-section">
            <div class="info-header">DRIVER / LESSEE:</div>
            <div class="info-detail">Name: <?php echo htmlspecialchars($_POST['name']); ?></div>
            <div class="info-detail">Address: <?php echo htmlspecialchars($_POST['address']); ?></div>
            <div class="info-detail">Telephone: <?php echo htmlspecialchars($_POST['phone']); ?></div>
            <div class="info-detail">Driving licence number: <?php echo htmlspecialchars($_POST['driving_licence']); ?></div>
            <div class="info-detail">Driving licence issue date: <?php echo htmlspecialchars($_POST['licence_issue_date']); ?></div>
            <div class="info-detail">Driving licence issue country: <?php echo htmlspecialchars($_POST['licence_country']); ?></div>
        </div>
        <div class="info-section">
            <div class="info-header">OWNER / LESSOR:</div>
            <div class="info-detail">Name: John Doe</div> <!-- Hardcoded for now -->
            <div class="info-detail">Social Security Number: 123-45-6789</div> <!-- Hardcoded for now -->
            <div class="info-detail">Telephone number: 555-555-5555</div> <!-- Hardcoded for now -->
            <div class="info-detail">Licence plate number: ABC1234</div> <!-- Hardcoded for now -->
        </div>
    </div>

</body>
</html>
