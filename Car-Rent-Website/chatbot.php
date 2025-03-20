<?php
// Include database connection
require_once 'data/connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Car Rental Assistant";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental Assistant - CARSRENT</title>
    
    <!-- Include your existing CSS files -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/util.css">
    <link rel="stylesheet" href="css/mobile.css">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Add chatbot specific CSS -->
    <link rel="stylesheet" href="css/chatbot.css">
</head>

<body>
    <!-- Include navigation/header -->
    <?php include 'includes/header.php'; ?>

    <!-- Floating Chatbot Widget -->
    <div class="chatbot-widget" id="chatbotWidget">
        <!-- Chatbot toggle button -->
        <button class="chatbot-toggle" id="chatbotToggle">
            <i class="fas fa-comment"></i>
        </button>

        <!-- Chatbot container -->
        <div class="chatbot-container" id="chatbotContainer">
            <div class="chatbot-header">
                <h3><i class="fas fa-robot"></i> Car Rental Assistant</h3>
                <div class="chatbot-controls">
                    <button id="chatbotMinimize"><i class="fas fa-minus"></i></button>
                    <button id="chatbotClose"><i class="fas fa-times"></i></button>
                </div>
            </div>
            
            <div class="chatbot-messages" id="chatMessages">
                <div class="message bot-message">
                    <div class="message-content">
                        <p>Hello! I'm your CARSRENT assistant. How can I help you today?</p>
                        <p>You can ask me about:</p>
                        <ul>
                            <li>Car availability</li>
                            <li>Car recommendations based on your needs</li>
                            <li>Rental policies</li>
                            <li>Vehicle features and specifications</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="chatbot-input">
                <form id="chatForm">
                    <input type="text" id="userMessage" placeholder="Type your question here..." autocomplete="off">
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
                <div class="typing-indicator" id="typingIndicator">
                    <div class="dot"></div>
                    <div class="dot"></div>
                    <div class="dot"></div>
                </div>
            </div>
            
            <div class="chatbot-footer">
                <p>Powered by CARSRENT AI</p>
            </div>
        </div>
    </div>

    <!-- Include footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Include chatbot dependencies -->
    <script src="js/chatbot.js"></script>

    <script>
        // Initialize chat context
        const chatContext = {
            hasGreeted: false,
            lastQuery: null,
            carPreferences: {}
        };
    </script>
</body>
</html>