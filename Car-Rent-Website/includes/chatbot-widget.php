<?php
// This file contains the chatbot widget that can be included on any page
?>
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

<!-- Include chatbot dependencies once when this widget is included -->
<?php if (!defined('CHATBOT_JS_INCLUDED')): ?>
    <?php define('CHATBOT_JS_INCLUDED', true); ?>
    <link rel="stylesheet" href="css/chatbot.css">
    <script src="js/chatbot.js"></script>
<?php endif; ?>