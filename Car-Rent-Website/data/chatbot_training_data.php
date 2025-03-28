<?php
/**
 * Chatbot Training Data
 * This file contains common questions and answers for the car rental chatbot
 * Used to train the AI with initial responses for frequent inquiries
 */

$chatbot_training_data = [
    // Car Availability Questions
    [
        "question" => "Are there any cars available next weekend?",
        "answer" => "We typically have cars available on weekends, but availability varies based on specific dates and models. The best way to check real-time availability is to visit our booking page where you can enter your exact dates and see which vehicles are available. Would you like me to help you navigate to the booking page?"
    ],
    [
        "question" => "Do you have any SUVs available?",
        "answer" => "Yes, we have several SUV models in our fleet. Our available SUVs include vehicles of different sizes and price points. You can check specific availability for your travel dates on our booking page. Would you like to know more about any particular SUV model we offer?"
    ],
    [
        "question" => "When can I rent a luxury car?",
        "answer" => "Our luxury cars can be rented any time, subject to availability. These premium vehicles are popular, so we recommend booking at least a few weeks in advance, especially during high season or holidays. You can check real-time availability and reserve a luxury vehicle through our booking page. Would you like information about the specific luxury models we offer?"
    ],
    [
        "question" => "How many cars do you have?",
        "answer" => "We have a diverse fleet of over 20 vehicles across various categories including economy, compact, midsize, SUVs, vans, and luxury cars. The exact number of available cars can vary by location and season. If you're looking for a specific type of vehicle or model, I can help you check availability for your desired dates. Would you like to know more about our fleet or specific models?"
    ],

    // Car Recommendations
    [
        "question" => "What's the best car for a family of 5?",
        "answer" => "For a family of 5, I recommend our midsize SUVs or minivans which offer ample space for passengers and luggage. Models like [SUV Model] with 7 seats or [Minivan Model] with flexible seating arrangements would be ideal. These vehicles provide good comfort for longer trips and have space for luggage. Would you like specific details about any of these family-friendly options?"
    ],
    [
        "question" => "I need a car for a business trip. What do you recommend?",
        "answer" => "For business trips, our sedan models offer the perfect balance of comfort, professionalism, and fuel efficiency. Models like [Sedan Model] provide a sophisticated appearance with modern amenities like Bluetooth connectivity and comfortable seating. If you're expecting clients, you might consider upgrading to one of our entry-level luxury models. Would you like information about specific features or pricing for our business-appropriate vehicles?"
    ],
    [
        "question" => "What's the cheapest car I can rent?",
        "answer" => "Our most economical options are our compact cars, starting from around $[Price]/day during regular seasons. These vehicles are fuel-efficient and perfect for city driving or short trips. While they're our most budget-friendly options, they still come equipped with essential features for a comfortable ride. Would you like to check rates for specific dates or learn more about our economy vehicles?"
    ],

    // Pricing and Payment
    [
        "question" => "How much does it cost to rent a car for a week?",
        "answer" => "Weekly rental rates vary by vehicle class, with discounts applied for longer rentals. Economy cars typically range from $[Price Range]/week, midsize vehicles from $[Price Range]/week, and premium/luxury vehicles from $[Price Range]/week. We also offer special weekly rates and promotions throughout the year. For the most accurate pricing, I recommend checking our booking page with your specific dates."
    ],
    [
        "question" => "Do you have any discounts available?",
        "answer" => "Yes, we offer several types of discounts! These include weekly and monthly rental discounts, corporate rates, loyalty program benefits, seasonal promotions, and special offers for early bookings. Some promotional codes may be available on our website or through our newsletter. Would you like information about any specific discount program or would you like to check current promotions?"
    ],
    [
        "question" => "What forms of payment do you accept?",
        "answer" => "We accept all major credit cards including Visa, MasterCard, American Express, and Discover. Some locations also accept debit cards with certain restrictions. For security, the renter must present the same card used for the reservation at the time of pickup. Would you like more details about our payment policies or reservation requirements?"
    ],

    // Rental Requirements
    [
        "question" => "What do I need to rent a car?",
        "answer" => "To rent a car, you'll need: 1) A valid driver's license (held for at least 1 year), 2) A major credit card in the renter's name, 3) Proof of insurance in some locations, and 4) Additional identification such as a passport may be required for international customers. Renters must typically be at least 21 years old, with drivers under 25 possibly subject to a young driver surcharge. Would you like specific details about any of these requirements?"
    ],
    [
        "question" => "Is there an age requirement to rent?",
        "answer" => "Yes, our standard minimum age requirement is 21 years. Drivers between 21-24 years may be subject to a young driver surcharge and might have restrictions on certain vehicle categories like luxury or premium models. The minimum age can vary for specialty vehicles. All renters must possess a valid driver's license held for at least 1 year. Would you like more information about age-related policies or surcharges?"
    ],
    [
        "question" => "Can I rent a car without a credit card?",
        "answer" => "While a credit card is our standard requirement for reservations and security deposits, some locations may accept debit cards with additional verification. When using a debit card, you may need to provide extra identification, proof of insurance, utility bills, and the deposit amount might be higher. We recommend calling our customer service for specific location policies regarding non-credit card rentals."
    ],

    // Insurance and Policies
    [
        "question" => "Do I need insurance to rent a car?",
        "answer" => "We offer several insurance options to protect you during your rental: 1) Collision Damage Waiver (CDW) which reduces your liability for vehicle damage, 2) Personal Accident Insurance (PAI) for medical coverage, 3) Personal Effects Coverage (PEC) for belongings in the vehicle, and 4) Liability Insurance Supplement for third-party coverage. Your personal auto insurance or credit card may offer some coverage, but we recommend confirming the details before declining our options."
    ],
    [
        "question" => "What happens if I return the car late?",
        "answer" => "If you return the vehicle later than the scheduled time, a late fee may apply. We typically provide a grace period of 29 minutes, after which you may be charged for an additional day. If you know you'll be late, please contact us as soon as possible to extend your reservation and avoid higher walk-up rates. Would you like information about how to extend your reservation if needed?"
    ],
    [
        "question" => "What is your cancellation policy?",
        "answer" => "Our standard cancellation policy allows free cancellation up to 48 hours before your scheduled pickup time for most reservations. Cancellations made within 48 hours may be subject to a fee equivalent to one day's rental. Prepaid or special rate reservations might have stricter cancellation terms. No-shows (failing to pick up the vehicle without cancellation) typically result in forfeiting the entire rental amount or a significant fee."
    ],

    // Vehicle Features
    [
        "question" => "Do your cars have GPS?",
        "answer" => "Yes, GPS navigation systems are available in our rental fleet. Many of our newer models come with built-in navigation systems. For vehicles without built-in systems, we offer portable GPS devices for an additional daily fee. This add-on can be requested during the booking process or at the rental counter upon pickup. Would you like me to provide information about other technology features or add-ons available with our rentals?"
    ],
    [
        "question" => "Can I get a car with automatic transmission?",
        "answer" => "Absolutely! The majority of our fleet features automatic transmission vehicles. In fact, in North America, almost all of our vehicles are automatic. If you specifically require an automatic transmission, you can filter for this option during the booking process, though it's the standard for most of our vehicles. Would you like information about any other specific vehicle features?"
    ],
    [
        "question" => "Do you have electric or hybrid cars?",
        "answer" => "Yes, we've been expanding our eco-friendly options. We offer several hybrid models in our fleet, and at select locations, we also have fully electric vehicles available. These fuel-efficient options are popular for environmentally conscious travelers and those looking to save on fuel costs. Availability varies by location, so I recommend checking the specific options at your desired pickup location through our booking page."
    ],

    // Booking Process
    [
        "question" => "How do I make a reservation?",
        "answer" => "Making a reservation is simple! You can book online through our website, use our mobile app, call our reservation center, or visit any of our rental locations in person. Online booking is the most convenient option and often comes with special web discounts. You'll need to provide your pickup/return locations, dates, times, and basic contact information. Would you like me to guide you through the online booking process?"
    ],
    [
        "question" => "Can I modify my booking?",
        "answer" => "Yes, you can modify most reservations before your scheduled pickup time. Changes can include dates, times, vehicle class, add-ons, or even pickup/drop-off locations. The easiest way to modify your booking is through our website using your reservation number, or by calling our customer service. Please note that changes might affect your rate, especially if you're shortening the rental period or changing to a higher-demand date."
    ],
    [
        "question" => "How far in advance should I book?",
        "answer" => "For the best rates and vehicle selection, we recommend booking at least 1-2 weeks in advance for regular rentals. During peak travel seasons (summer, holidays, major events), booking 3-4 weeks ahead is advisable. Last-minute bookings are possible based on availability, but you'll have a limited selection and potentially higher rates. If you have specific vehicle requirements, earlier booking is always better."
    ],

    // Pick-up and Return
    [
        "question" => "Where can I pick up the car?",
        "answer" => "We have multiple convenient pickup locations including airport terminals, downtown offices, and suburban branches. Our major hubs offer extended hours, while neighborhood locations typically operate during regular business hours. You can select your preferred pickup location during the booking process, and the available vehicles will be shown for that specific location. Would you like information about our locations in a particular city or area?"
    ],
    [
        "question" => "What happens during car pickup?",
        "answer" => "During pickup, please bring your valid driver's license, the credit card used for the reservation, and any additional required documentation. Our staff will verify your documents, review the rental agreement with you, offer optional coverages and upgrades, and process your security deposit. We'll then walk you through the vehicle features and complete a damage inspection before you drive away. The entire process typically takes 15-30 minutes."
    ],
    [
        "question" => "Do I need to return the car with a full tank?",
        "answer" => "Yes, our standard policy requires returning the vehicle with the same fuel level as when you received it (typically full). If you return the car with less fuel, you'll be charged for refueling plus a service fee. We do offer a Fuel Purchase Option (FPO) where you prepay for a tank of gas and can return it at any level. This option is convenient if you're in a hurry or don't want to locate a gas station before returning."
    ],

    // Miscellaneous Questions
    [
        "question" => "Can I take the rental car across state lines?",
        "answer" => "Yes, domestic travel across state lines is generally permitted within most of our standard rental agreements at no additional cost. However, some specialty vehicles or one-way rentals might have geographic restrictions. International border crossings (like into Canada or Mexico) require advance notice and may need additional documentation or insurance coverage. Please inform us if you plan to cross international borders during your rental period."
    ],
    [
        "question" => "What if the car breaks down?",
        "answer" => "All our vehicles come with 24/7 roadside assistance coverage. If your vehicle breaks down, call our emergency number provided in your rental agreement. Depending on your location and the issue, we'll either send a technician to repair the vehicle or arrange a replacement vehicle for you. This service covers mechanical failures, lockouts, jump starts, flat tires, and emergency fuel delivery. The service is complimentary for issues not caused by the renter."
    ],
    [
        "question" => "Do you offer one-way rentals?",
        "answer" => "Yes, we offer one-way rentals that allow you to pick up the vehicle at one location and drop it off at another. One-way rentals within the same city or nearby areas usually incur a small drop fee. Longer distance one-ways (between cities or states) have variable fees depending on the locations and time of year. These rentals are subject to availability and should be booked in advance to ensure the best rates."
    ],
    [
        "question" => "Can I add an additional driver?",
        "answer" => "Yes, you can add additional drivers to your rental. Additional drivers must visit the rental counter in person with their valid driver's license to be added to the agreement. There's typically a daily fee for each additional driver, though this fee may be waived for spouses/partners in some locations or for members of our loyalty program. All additional drivers must meet the same requirements as the primary renter, including age restrictions."
    ]
];
?>