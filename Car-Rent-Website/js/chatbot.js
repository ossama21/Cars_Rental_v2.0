/**
 * Car Rental Chatbot with Gemini API
 * This script handles the chatbot interactions, processes user queries,
 * and integrates with Google's Gemini AI API
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const chatForm = document.getElementById('chatForm');
    const userMessageInput = document.getElementById('userMessage');
    const chatMessages = document.getElementById('chatMessages');
    const typingIndicator = document.getElementById('typingIndicator');
    const chatbotToggle = document.getElementById('chatbotToggle');
    const chatbotContainer = document.getElementById('chatbotContainer');
    const chatbotMinimize = document.getElementById('chatbotMinimize');
    const chatbotClose = document.getElementById('chatbotClose');
    
    // Gemini API Key
    const API_KEY = "AIzaSyCxIMgE1JXj8ZN70DeNVzuFyAdFBp9CypY";
    // Updated API endpoint with correct model name and version
    const API_URL = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent";
    
    // Connection retry settings
    const MAX_RETRIES = 2;
    let retryCount = 0;
    
    // Available cars in the system (will be populated from API)
    let availableCars = [];
    
    // Car rental context for the AI - this helps guide the model
    const SYSTEM_CONTEXT = `
        You are a helpful car rental assistant for CARSRENT website. Your name is ILYAS you are a car rental assistant for CARSRENT.
        
        RULES:
        1. ONLY answer questions related to car rentals, vehicles, booking procedures, and our services.
        2. If a question is not related to car rentals or our business, politely decline to answer.
        3. Be friendly, concise, and helpful with car rental related inquiries.
        4. For vehicle recommendations, ask about the customer's needs (family size, luggage, budget, etc).
        5. For availability questions, mention that real-time availability should be checked on the booking page.
        6. Never provide information about topics unrelated to car rentals (politics, general knowledge, etc).
        7. Never generate content that violates legal or ethical standards.
        8. Always suggest contacting customer service for complex issues or booking confirmation.
        
        Example car rental scenarios you can help with:
        - Car availability dates
        - Vehicle recommendations based on needs
        - Rental policies and requirements
        - Pricing estimates (be general, suggest checking the actual prices on site)
        - Car features and specifications
        - Comparison between different car models we offer
        - Rental locations and hours
        - Required documents for renting
        - Insurance options
    `;
    
    // Initialize chat and fetch car data
    initChat();
    fetchCarData();
    initChatbotWidget();
    
    // Initialize the chatbot widget controls
    function initChatbotWidget() {
        // Show chatbot when toggle button is clicked
        chatbotToggle.addEventListener('click', function() {
            chatbotContainer.classList.toggle('active');
            // Save state in local storage
            localStorage.setItem('chatbotOpen', chatbotContainer.classList.contains('active'));
            
            // Scroll chat to bottom when opened
            if (chatbotContainer.classList.contains('active')) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
                // Focus the input field
                userMessageInput.focus();
            }
        });
        
        // Minimize button just closes the chatbot
        chatbotMinimize.addEventListener('click', function() {
            chatbotContainer.classList.remove('active');
            localStorage.setItem('chatbotOpen', 'false');
        });
        
        // Close button does the same as minimize for now
        chatbotClose.addEventListener('click', function() {
            chatbotContainer.classList.remove('active');
            localStorage.setItem('chatbotOpen', 'false');
        });
        
        // Check if the chatbot was previously open
        if (localStorage.getItem('chatbotOpen') === 'true') {
            chatbotContainer.classList.add('active');
        }
    }
    
    // Initialize the chat functionality
    function initChat() {
        // Form submission event listener
        chatForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const userMessage = userMessageInput.value.trim();
            
            if (userMessage) {
                // Add user message to chat
                addMessage(userMessage, 'user');
                userMessageInput.value = '';
                
                // Show typing indicator
                typingIndicator.classList.add('active');
                
                // Reset retry count for new messages
                retryCount = 0;
                
                // Process the message and get response from AI
                processMessage(userMessage);
            }
        });
    }
    
    // Fetch car data from API
    async function fetchCarData() {
        try {
            const response = await fetch('./data/chatbot_api.php?action=get_all_cars');
            const data = await response.json();
            
            if (data.status === 'success') {
                availableCars = data.data;
                console.log('Car data loaded:', availableCars.length, 'cars found');
            } else {
                console.error('Error loading car data:', data.message);
            }
        } catch (error) {
            console.error('Error fetching car data:', error);
            // We'll continue even if car data fails to load, the chatbot will just have less context
        }
    }
    
    // Add a message to the chat
    function addMessage(message, sender) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message');
        messageElement.classList.add(sender + '-message');
        
        const messageContent = document.createElement('div');
        messageContent.classList.add('message-content');
        
        // If message contains bullet points (starts with * or -)
        if (message.includes('\n* ') || message.includes('\n- ') || message.includes('\n1. ')) {
            const paragraphs = message.split('\n');
            let currentList = null;
            let isOrdered = false;
            
            paragraphs.forEach(paragraph => {
                paragraph = paragraph.trim();
                if (!paragraph) return;
                
                // Check if this is a list item
                if (paragraph.startsWith('* ') || paragraph.startsWith('- ')) {
                    // If we don't have a list yet, create one
                    if (!currentList) {
                        currentList = document.createElement('ul');
                        messageContent.appendChild(currentList);
                    }
                    
                    const listItem = document.createElement('li');
                    listItem.textContent = paragraph.substring(2);
                    currentList.appendChild(listItem);
                    
                } else if (paragraph.match(/^\d+\.\s/)) {
                    // If we don't have an ordered list yet, create one
                    if (!currentList || !isOrdered) {
                        currentList = document.createElement('ol');
                        isOrdered = true;
                        messageContent.appendChild(currentList);
                    }
                    
                    const listItem = document.createElement('li');
                    listItem.textContent = paragraph.substring(paragraph.indexOf('.')+1).trim();
                    currentList.appendChild(listItem);
                    
                } else {
                    // Reset list
                    currentList = null;
                    isOrdered = false;
                    
                    // Regular paragraph
                    const p = document.createElement('p');
                    p.textContent = paragraph;
                    messageContent.appendChild(p);
                }
            });
            
        } else {
            // Regular message without formatting
            const p = document.createElement('p');
            p.textContent = message;
            messageContent.appendChild(p);
        }
        
        messageElement.appendChild(messageContent);
        chatMessages.appendChild(messageElement);
        
        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Store first-time greeting state
    let hasGreeted = false;

    function processMessage(userMessage) {
        try {
            // Only show introduction for first message
            if (!hasGreeted) {
                addMessage("Hi there! I'm Ilyas, your CARSRENT assistant. How can I help you today?", 'bot');
                hasGreeted = true;
            }
            
            // First detect intent
            const intent = detectIntent(userMessage);
            
            // Check if we need to handle the intent with specific API data
            if (intent) {
                handleSpecialIntent(intent, userMessage)
                    .then(response => {
                        if (response) {
                            typingIndicator.classList.remove('active');
                            addMessage(response, 'bot');
                        } else {
                            // Fall back to Gemini API
                            processWithGeminiAPI(userMessage);
                        }
                    })
                    .catch(error => {
                        console.error("Error handling intent:", error);
                        processWithGeminiAPI(userMessage);
                    });
            } else {
                processWithGeminiAPI(userMessage);
            }
        } catch (error) {
            console.error("Error in processMessage:", error);
            handleAPIError(userMessage);
        }
    }

    // Function to detect intent from user message
    function detectIntent(message) {
        message = message.toLowerCase();
        
        const intentPatterns = {
            carAvailability: /\b(available|availability|when can i (book|rent)|when.*available)\b.*\b(car|vehicle)\b/i,
            carRecommendation: /\b(recommend|suggest|looking for|need|want)\b.*\b(car|vehicle)\b/i,
            carDetails: /\b(features|specs|details|tell me about|information|price)\b.*\b([a-z\s]+ car|vehicle|specific car)\b/i,
            carCount: /\b(how many|number of|total|count)\b.*\b(cars?|vehicles?)\b/i,
            carTypes: /\b(what|which|list|show)\b.*\b(types?|kinds?)\b.*\b(cars?|vehicles?)\b/i,
            specificCar: /\b(hyundai|skoda|tata|mahindra|suzuki|ford|seat|baleno|i20|rapid|nexon|scorpio|tiago|city|creta|mustang)\b/i
        };
        
        for (const [intent, pattern] of Object.entries(intentPatterns)) {
            if (pattern.test(message)) {
                return intent;
            }
        }
        return null;
    }

    // Handle special intents with API data
    async function handleSpecialIntent(intent, message) {
        try {
            switch (intent) {
                case 'carCount':
                    const carsResponse = await fetch('./data/chatbot_api.php?action=get_all_cars');
                    const carsData = await carsResponse.json();
                    
                    if (carsData.status === 'success' && carsData.data) {
                        const cars = carsData.data;
                        // Group cars by brand
                        const carsByBrand = {};
                        cars.forEach(car => {
                            if (!carsByBrand[car.brand]) {
                                carsByBrand[car.brand] = [];
                            }
                            carsByBrand[car.brand].push(car.name);
                        });
                        
                        let response = `We currently have ${cars.length} cars in our fleet. Here's the breakdown by brand:\n\n`;
                        for (const [brand, models] of Object.entries(carsByBrand)) {
                            response += `${brand}: ${models.length} car${models.length > 1 ? 's' : ''} (${models.join(', ')})\n`;
                        }
                        return response;
                    }
                    return "I'm sorry, I couldn't retrieve the car count at the moment. Please try again later.";
                    
                case 'specificCar':
                    const carName = extractCarName(message);
                    if (carName) {
                        const searchResponse = await fetch(`./data/chatbot_api.php?action=search_cars&name=${encodeURIComponent(carName)}&brand=${encodeURIComponent(carName)}`);
                        const searchData = await searchResponse.json();
                        
                        if (searchData.status === 'success' && searchData.data && searchData.data.length > 0) {
                            const car = searchData.data[0];
                            return `Here are the details for the ${car.brand} ${car.name}:\n\n` +
                                   `- Model: ${car.model}\n` +
                                   `- Price: $${car.price} per day\n` +
                                   `- Transmission: ${car.transmission}\n` +
                                   `- Interior: ${car.interior}\n\n` +
                                   `${car.description}\n\n` +
                                   `Would you like to book this car or know more about its availability?`;
                        }
                    }
                    return null;

                case 'carTypes':
                    const typesResponse = await fetch('./data/chatbot_api.php?action=get_all_cars');
                    const typesData = await typesResponse.json();
                    
                    if (typesData.status === 'success' && typesData.data) {
                        const cars = typesData.data;
                        // Group by transmission and interior types
                        const transmissions = [...new Set(cars.map(car => car.transmission))];
                        const interiors = [...new Set(cars.map(car => car.interior))];
                        
                        let response = "Here are the types of cars we offer:\n\n";
                        response += "Available Transmissions:\n";
                        transmissions.forEach(trans => {
                            if (trans) response += `- ${trans}\n`;
                        });
                        
                        response += "\nInterior Options:\n";
                        interiors.forEach(int => {
                            if (int) response += `- ${int}\n`;
                        });
                        
                        response += "\nWould you like to know more about any specific type or see available cars?";
                        return response;
                    }
                    return null;

                case 'carAvailability':
                    const carMentioned = extractCarMention(message);
                    if (carMentioned) {
                        try {
                            const carResponse = await fetch(`./data/chatbot_api.php?action=search_cars&name=${encodeURIComponent(carMentioned)}&brand=${encodeURIComponent(carMentioned)}`);
                            const carData = await carResponse.json();
                            
                            if (carData.status === 'success' && carData.data.length > 0) {
                                const car = carData.data[0];
                                return `The ${car.brand} ${car.name} is generally available in our fleet. For specific dates, I recommend checking the booking page or contacting our customer service. The daily rate for this ${car.type} starts at $${car.price} per day.`;
                            }
                        } catch (error) {
                            console.error("Error getting car availability:", error);
                            return "I'm having trouble checking car availability information right now. You can see our available vehicles directly on our booking page or contact customer service for assistance.";
                        }
                    }
                    return null;

                // ...existing cases...
            }
        } catch (error) {
            console.error("Error handling special intent:", error);
            return null;
        }
    }

    function extractCarName(message) {
        const carBrands = ['hyundai', 'skoda', 'tata', 'mahindra', 'suzuki', 'ford', 'seat'];
        const carModels = ['i20', 'rapid', 'nexon', 'scorpio', 'baleno', 'tiago', 'city', 'creta', 'mustang'];
        
        message = message.toLowerCase();
        
        for (const brand of carBrands) {
            if (message.includes(brand)) return brand;
        }
        
        for (const model of carModels) {
            if (message.includes(model)) return model;
        }
        
        return null;
    }
    
    // Process user message and get AI response
    async function processWithGeminiAPI(userMessage) {
        try {
            // First detect intent to see if we should handle this with a special API call
            const intent = detectIntent(userMessage);
            
            // Check if the message is related to car rentals using a simple keyword check
            // This is a basic first-level filter before sending to the API
            const isCarRelated = isCarRentalQuery(userMessage);
            
            if (!isCarRelated) {
                // If clearly not car-related, respond immediately without calling API
                typingIndicator.classList.remove('active');
                addMessage("I'm sorry, but I can only assist with car rental related questions. Please ask me about our vehicles, rental options, or booking procedures.", 'bot');
                return;
            }
            
            // Check if we need to handle the intent with specific API data
            if (intent) {
                try {
                    const specialResponse = await handleSpecialIntent(intent, userMessage);
                    if (specialResponse) {
                        typingIndicator.classList.remove('active');
                        addMessage(specialResponse, 'bot');
                        return;
                    }
                } catch (intentError) {
                    console.error("Error handling intent:", intentError);
                    // Continue with Gemini API if intent handling fails
                }
            }
            
            // If no special handling, or if special handling didn't produce a response,
            // fall back to Gemini API
            
            // Enhance context with car data if available
            let enhancedContext = SYSTEM_CONTEXT;
            if (availableCars && availableCars.length > 0) {
                // Add a simplified car list to the context (limit to 5 cars to avoid token limits)
                const carSample = availableCars.slice(0, 5).map(car => 
                    `${car.brand} ${car.name} (${car.type}, ${car.seats} seats, $${car.price}/day)`
                ).join('\n- ');
                
                enhancedContext += `\n\nWe have ${availableCars.length} cars available, including:\n- ${carSample}\n\nAnd many more. You can recommend these cars when appropriate.`;
            }
            
            // Prepare request payload for Gemini API
            const requestBody = {
                contents: [
                    {
                        role: "user",
                        parts: [
                            { text: enhancedContext },
                            { text: "Remember your instructions above. Now respond to this user query about car rentals: " + userMessage }
                        ]
                    }
                ],
                generationConfig: {
                    temperature: 0.7,
                    topK: 40,
                    topP: 0.95,
                    maxOutputTokens: 800,
                }
            };
            
            try {
                // Call Gemini API with the request
                const response = await fetch(`${API_URL}?key=${API_KEY}`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(requestBody),
                    timeout: 10000 // 10 second timeout
                });
                
                if (!response.ok) {
                    throw new Error(`API error: ${response.status}`);
                }
                
                const responseData = await response.json();
                
                // Process the AI response
                if (responseData.candidates && responseData.candidates.length > 0) {
                    let aiResponse = responseData.candidates[0].content.parts[0].text;
                    
                    // Second filter to ensure the AI didn't go off-topic
                    if (containsOffTopicContent(aiResponse)) {
                        aiResponse = "I'm sorry, I can only provide information related to our car rental services. If you have any questions about our vehicles, rental policies, or booking procedures, I'd be happy to assist!";
                    }
                    
                    // Add AI response to chat
                    typingIndicator.classList.remove('active');
                    addMessage(aiResponse, 'bot');
                } else {
                    throw new Error("No response from AI");
                }
            } catch (apiError) {
                console.error("API error:", apiError);
                handleAPIError(userMessage);
            }
            
        } catch (error) {
            console.error("Error processing message:", error);
            handleAPIError(userMessage);
        }
    }
    
    // Handle API errors with retry logic and fallback responses
    function handleAPIError(userMessage) {
        if (retryCount < MAX_RETRIES) {
            // Retry the API call
            retryCount++;
            console.log(`Retrying API call (${retryCount}/${MAX_RETRIES})...`);
            setTimeout(() => {
                processMessage(userMessage);
            }, 1000 * retryCount); // Exponential backoff - wait longer for each retry
        } else {
            // Max retries reached, show error message
            typingIndicator.classList.remove('active');
            
            // Try to provide a helpful response based on the query
            const intent = detectIntent(userMessage);
            let errorMessage;
            
            if (intent === 'carAvailability') {
                errorMessage = "I'm having trouble connecting to check car availability right now. You can see our available vehicles directly on our booking page at book.php, or contact our customer service at +212 630352250 for immediate assistance.";
            } else if (intent === 'carRecommendation') {
                errorMessage = "I'm having trouble connecting to provide personalized recommendations right now. Please browse our selection at book.php or contact our customer service team who can help you find the perfect car for your needs.";
            } else if (intent === 'pricing') {
                errorMessage = "I'm having trouble retrieving pricing information right now. You can see our current rates on our booking page, or call our customer service at +212 630352250 for the most up-to-date pricing.";
            } else {
                errorMessage = "I'm having trouble connecting right now. Please try again later or contact our customer service team at +212 630352250 for immediate assistance with your car rental needs.";
            }
            
            addMessage(errorMessage, 'bot');
            
            // Reset retry counter
            retryCount = 0;
        }
    }
    
    // Detect intent from user message
    function detectIntent(message) {
        message = message.toLowerCase();
        
        // Define intent patterns
        const intentPatterns = {
            carAvailability: /\b(available|availability|when can i book|when can i rent)\b.*\b(car|vehicle)\b|\b(car|vehicle)\b.*\b(available|availability)\b/i,
            carRecommendation: /\b(recommend|suggestion|what car|which car|what vehicle|suggest)\b|\b(family|trip|people|luggage|budget)\b/i,
            carDetails: /\b(features|specifications|specs|details|tell me about)\b.*\b(car|vehicle|model)\b/i,
            carComparison: /\b(compare|comparison|difference|better)\b.*\b(car|vehicle|model)\b/i,
            rentalProcess: /\b(how to|process|steps|procedure)\b.*\b(rent|book|reservation)\b/i,
            rentalRequirements: /\b(require|requirements|documents|need to bring|license)\b/i,
            pricing: /\b(cost|price|pricing|how much|fee|discount|offer)\b/i,
            carCount: /\b(how many|number of|total|count)\b.*\b(cars?|vehicles?)\b/i
        };
        
        // Check which intent matches
        for (const [intent, pattern] of Object.entries(intentPatterns)) {
            if (pattern.test(message)) {
                return intent;
            }
        }
        
        return null; // No specific intent detected
    }
    
    // Handle special intents with API data
    async function handleSpecialIntent(intent, message) {
        try {
            switch (intent) {
                case 'carAvailability':
                    // Extract car name/brand if mentioned
                    const carMentioned = extractCarMention(message);
                    if (carMentioned) {
                        try {
                            // Search for the car in our database
                            const carResponse = await fetch(`./data/chatbot_api.php?action=search_cars&name=${encodeURIComponent(carMentioned)}&brand=${encodeURIComponent(carMentioned)}`);
                            const carData = await carResponse.json();
                            
                            if (carData.status === 'success' && carData.data.length > 0) {
                                const car = carData.data[0];
                                // Use a generic response about availability
                                return `The ${car.brand} ${car.name} is generally available in our fleet. For specific dates, I recommend checking the booking page or contacting our customer service. The daily rate for this ${car.type} starts at $${car.price} per day.`;
                            }
                        } catch (error) {
                            console.error("Error getting car availability:", error);
                            return "I'm having trouble checking car availability information right now. You can see our available vehicles directly on our booking page or contact customer service for assistance.";
                        }
                    }
                    return null;
                    
                case 'carRecommendation':
                    // Extract requirements from message
                    const familySize = extractNumberOfPeople(message);
                    const tripType = extractTripType(message);
                    const budget = extractBudget(message);
                    
                    // Only proceed if we have at least one requirement
                    if (familySize || tripType || budget) {
                        try {
                            const reqParams = [];
                            if (familySize) reqParams.push(`family_size=${encodeURIComponent(familySize)}`);
                            if (tripType) reqParams.push(`trip_type=${encodeURIComponent(tripType)}`);
                            if (budget) reqParams.push(`budget=${encodeURIComponent(budget)}`);
                            
                            const recResponse = await fetch(`../data/chatbot_api.php?action=get_recommendations&${reqParams.join('&')}`);
                            const recData = await recResponse.json();
                            
                            if (recData.status === 'success' && recData.data.length > 0) {
                                // Format recommendations
                                const recs = recData.data.slice(0, 3); // Limit to top 3 recommendations
                                let response = "Based on your needs, I recommend considering these options:\n\n";
                                
                                recs.forEach((car, index) => {
                                    response += `${index + 1}. **${car.brand} ${car.name}** - ${car.type}, ${car.seats} seats, $${car.price}/day\n`;
                                    if (car.description) {
                                        response += `   ${car.description}\n`;
                                    }
                                    response += "\n";
                                });
                                
                                response += "Would you like more details about any of these vehicles?";
                                return response;
                            }
                        } catch (error) {
                            console.error("Error getting car recommendations:", error);
                            return "I'm having trouble providing personalized car recommendations right now. Please visit our booking page to see our full selection of vehicles, or contact our customer service for assistance.";
                        }
                    }
                    return null;
                
                case 'pricing':
                    // Fallback for pricing questions when we can't connect to the API
                    if (availableCars && availableCars.length > 0) {
                        // Calculate price ranges
                        const prices = availableCars.map(car => parseFloat(car.price));
                        const minPrice = Math.min(...prices);
                        const maxPrice = Math.max(...prices);
                        
                        return `Our rental prices range from $${minPrice} to $${maxPrice} per day, depending on the vehicle type and model. Economy cars start around $${minPrice}-$75/day, mid-range vehicles are typically $75-$150/day, and premium or luxury vehicles are $150+/day. For the most accurate pricing for specific dates, please check our booking page or contact customer service.`;
                    }
                    return null;

                case 'carCount':
                    if (availableCars && availableCars.length > 0) {
                        // Group cars by type for a more detailed response
                        const carTypes = {};
                        availableCars.forEach(car => {
                            if (!carTypes[car.type]) {
                                carTypes[car.type] = 0;
                            }
                            carTypes[car.type]++;
                        });

                        let response = `We currently have ${availableCars.length} cars in our fleet. Here's the breakdown:\n\n`;
                        for (const [type, count] of Object.entries(carTypes)) {
                            response += `- ${count} ${type}${count > 1 ? 's' : ''}\n`;
                        }
                        response += "\nWould you like to know more about any specific type of car?";
                        return response;
                    }
                    return "I'm checking our inventory system... It seems we're having trouble accessing the exact count right now. Please check our booking page to see all available vehicles, or ask me about specific types of cars you're interested in.";
                    
                default:
                    return null;
            }
        } catch (error) {
            console.error("Error handling special intent:", error);
            throw error; // Let the caller handle this
        }
    }
    
    // Extract car name/brand from message
    function extractCarMention(message) {
        message = message.toLowerCase();
        
        // Check if any car brands or models are mentioned
        const carBrands = ['toyota', 'honda', 'ford', 'bmw', 'audi', 'mercedes', 'volkswagen', 'hyundai', 
                          'nissan', 'mazda', 'lexus', 'chevrolet', 'kia', 'subaru', 'jeep'];
        
        const carModels = ['corolla', 'camry', 'civic', 'accord', 'focus', 'mustang', '3 series', '5 series', 
                          'a4', 'a6', 'c class', 'e class', 'golf', 'passat', 'elantra', 'sonata'];
        
        // Check for car brands
        for (const brand of carBrands) {
            if (message.includes(brand)) {
                return brand;
            }
        }
        
        // Check for car models
        for (const model of carModels) {
            if (message.includes(model)) {
                return model;
            }
        }
        
        // Check for common car types
        const carTypes = ['sedan', 'suv', 'truck', 'van', 'convertible', 'coupe', 'hatchback', 'luxury'];
        for (const type of carTypes) {
            if (message.includes(type)) {
                return type;
            }
        }
        
        return null;
    }
    
    // Extract number of people from message
    function extractNumberOfPeople(message) {
        const peoplePattern = /\b(\d+)\s*(person|people|passenger|passengers|family|members|travelers|adults|kids|children)\b/i;
        const familyPattern = /\b(family|group)\s*of\s*(\d+)\b/i;
        
        let match = message.match(peoplePattern);
        if (match) {
            return match[1];
        }
        
        match = message.match(familyPattern);
        if (match) {
            return match[2];
        }
        
        if (message.includes('family') || message.includes('families')) {
            return 4; // Assume average family size
        }
        
        return null;
    }
    
    // Extract trip type from message
    function extractTripType(message) {
        message = message.toLowerCase();
        
        if (message.includes('city') || message.includes('urban') || message.includes('downtown')) {
            return 'city';
        } else if (message.includes('highway') || message.includes('long distance') || message.includes('road trip')) {
            return 'highway';
        } else if (message.includes('off-road') || message.includes('offroad') || message.includes('mountain')) {
            return 'off-road';
        }
        
        return null;
    }
    
    // Extract budget information from message
    function extractBudget(message) {
        message = message.toLowerCase();
        
        if (message.includes('cheap') || message.includes('budget') || message.includes('affordable') || message.includes('low cost')) {
            return 'low';
        } else if (message.includes('luxury') || message.includes('premium') || message.includes('high-end') || message.includes('expensive')) {
            return 'high';
        } else if (message.includes('mid') || message.includes('moderate') || message.includes('average')) {
            return 'mid';
        }
        
        // Try to extract a dollar amount
        const dollarPattern = /\$(\d+)/;
        const match = message.match(dollarPattern);
        if (match) {
            const amount = parseInt(match[1]);
            if (amount <= 75) return 'low';
            else if (amount <= 150) return 'mid';
            else return 'high';
        }
        
        return null;
    }
    
    // Function to check if query is car rental related (basic keywords check)
    function isCarRentalQuery(query) {
        query = query.toLowerCase();
        
        // Car rental related keywords
        const carRentalKeywords = [
            'car', 'rent', 'rental', 'vehicle', 'book', 'booking', 'reservation',
            'suv', 'sedan', 'convertible', 'minivan', 'luxury', 'economy', 
            'compact', 'availability', 'available', 'price', 'cost', 'fee',
            'insurance', 'driver', 'driving', 'license', 'deposit', 'fuel',
            'gas', 'mileage', 'pick-up', 'dropoff', 'location', 'airport',
            'model', 'brand', 'audi', 'bmw', 'toyota', 'honda', 'mercedes',
            'family', 'trip', 'business', 'weekend', 'vacation', 'holiday'
        ];
        
        // Check if query contains any car rental related keywords
        return carRentalKeywords.some(keyword => query.includes(keyword));
    }
    
    // Function to check if AI response contains off-topic content
    function containsOffTopicContent(response) {
        response = response.toLowerCase();
        
        // List of off-topic indicators
        const offTopicIndicators = [
            "i can't provide", "i cannot provide", "i'm not able to",
            "outside the scope", "beyond my capabilities", "not related to car rental",
            "i don't have information about", "i don't have personal opinions",
            "unable to assist with", "i don't provide", "i'm designed to help with car rental",
            "i'm not designed to", "I apologize, but", "I cannot assist with that"
        ];
        
        return offTopicIndicators.some(indicator => response.includes(indicator));
    }
});