<html>
    <head>
        <title>My Dashboard</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="images/icons/favicon.ico"/>
        <link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
        <link rel="stylesheet" type="text/css" href="fonts/iconic/css/material-design-iconic-font.min.css">
        <link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
        <link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
        <link rel="stylesheet" type="text/css" href="vendor/animsition/css/animsition.min.css">
        <link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
        <link rel="stylesheet" type="text/css" href="vendor/daterangepicker/daterangepicker.css">
        <!-- <link rel="stylesheet" type="text/css" href="css/util.css">
        <link rel="stylesheet" type="text/css" href="css/main.css"> -->

        <style>
            body {
                font-family: 'Poppins', sans-serif;
                /* background-color: #f4f4f9; */
                color: #333;
            }

            .limiter {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                background: linear-gradient(135deg, #3182ce, #63b3ed); /*Changed to shades of blue*/
            }

            .container-login100 {
                width: 100%;
                max-width: 1200px;
                background-color: #4f9edf;
                /* box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); */
                border-radius: 10px;
                overflow: hidden;
            }

           

            .wrap-login100 {
                width: 50%;
                padding: 5rem;
            }

            .login100-form-title {
                font-size: 2rem;
                color: #333;
                text-align: center;
                margin-bottom: 2rem;
            }

            .label-input100 {
                font-size: 1.2rem;
                color: #666;
                margin-bottom: 0.5rem;
            }

            .input100 {
                width: 100%;
                padding: 0.8rem;
                margin-bottom: 1.5rem;
                border-radius: 5px;
                border: 1px solid #ddd;
                background-color: #f9f9f9;
                transition: all 0.3s ease;
            }

            .input100:focus {
                border-color: #3182ce;
                background-color: #fff;
            }

            .login100-form-btn {
                width: 100%;
                padding: 1rem;
                background-color: #3182ce;
                color: white;
                border: none;
                border-radius: 5px;
                transition: all 0.3s ease;
                cursor: pointer;
            }

            .login100-form-btn:hover {
                background-color: #63b3ed;
            }

            h1 {
                text-align: center;
                color: white;
                background-color: #3182ce;
                padding: 1rem;
                border-radius: 10px 10px 0 0;
                font-size: 2rem;
            }
        </style>
    </head>
    <body>
        <!-- session fetching username -->
        <h1>Welcome 
            <?php
            session_start();
            if (isset($_SESSION['name'])) {
                echo $_SESSION['name'];
            } else {
                echo "Guest";
            }
            ?>
        </h1>

        <div class="limiter">
            <div class="container-login100">
                <div class="login100-more"></div>
                
                <div class="wrap-login100 p-l-50 p-r-50 p-t-72 p-b-50">
                    <form class="login100-form validate-form" action="connect_dash.php" method="post">
                        <span class="login100-form-title p-b-59">
                            Book Your Car
                        </span>
                        
                        <div class="wrap-input100 validate-input" data-validate="Name is required">
                            <span class="label-input100">Username</span>
                            <input class="input100" type="text" name="name" placeholder="Name...">
                            <span class="focus-input100"></span>
                        </div>

                        <div class="wrap-input100 validate-input" data-validate="Address is required">
                            <span class="label-input100">Address</span>
                            <input class="input100" type="text" name="address" placeholder="User address..">
                            <span class="focus-input100"></span>
                        </div>

                        <div class="wrap-input100 validate-input" data-validate="Age is required">
                            <span class="label-input100">Age</span>
                            <input class="input100" type="text" name="age" placeholder="Your age...">
                            <span class="focus-input100"></span>
                        </div>

                        <div class="wrap-input100 validate-input" data-validate="No. of Days required">
                            <span class="label-input100">No. of days</span>
                            <input class="input100" type="number" min="1" max="100" name="days">
                            <span class="focus-input100"></span>
                        </div>

                        <div class="container-login100-form-btn">
                            <div class="wrap-login100-form-btn">
                                <div class="login100-form-bgbtn"></div>
                                <button class="login100-form-btn" name="submit" type="submit">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </form>  
                </div>
            </div>
        </div>
<!-- 
        <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
        <script src="vendor/animsition/js/animsition.min.js"></script>
        <script src="vendor/bootstrap/js/popper.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
        <script src="vendor/select2/select2.min.js"></script>
        <script src="vendor/daterangepicker/moment.min.js"></script>
        <script src="vendor/daterangepicker/daterangepicker.js"></script>
        <script src="vendor/countdowntime/countdowntime.js"></script>
        <script src="js/main.js"></script> -->
    </body>
</html>
