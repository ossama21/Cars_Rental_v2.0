import products from "./products.js";

// Wrap all the code to ensure it runs after the DOM is fully loaded
window.addEventListener('DOMContentLoaded', function () {
    // Extract car details from URL parameters
    const params = new URLSearchParams(window.location.search);
    const carId = params.get('product_id');

    if (carId) {
        // Retrieve the car details based on the ID
        const selectedCar = products.find(car => car.id === carId);
        
        if (selectedCar) {
            // Update the car details in the checkout page
            document.getElementById('car-name').textContent = selectedCar.name;
            document.getElementById('car-description').textContent = selectedCar.description;
            document.getElementById('car-price').textContent = `$${selectedCar.price} / Day`;
            document.getElementById('car-image').src = selectedCar.image;
            document.getElementById('car-model').textContent = selectedCar.model;
            document.getElementById('car-transmission').textContent = selectedCar.transmission;
            document.getElementById('car-interior').textContent = selectedCar.interior;
            document.getElementById('car-brand').textContent = selectedCar.brand;
        } else {
            console.error('Car not found');
        }
    } else {
        console.error('Car ID not found in URL');
    }

    // Attach form submit event listener after the DOM is loaded
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(event) {
            event.preventDefault();  // Prevent the form from submitting

            // Basic validation (you can enhance this)
            let name = document.getElementById('name').value;
            let address = document.getElementById('address').value;
            let phone = document.getElementById('phone').value;
            let visa = document.getElementById('visa').value;

            if (name === "" || address === "" || phone === "" || visa === "") {
                alert("Please fill in all fields.");
            } else {
                // Show confirmation message
                document.getElementById('confirmationMessage').style.display = 'block';
            }
        });
    }

    function showPaymentForm(formId) {
        // Hide all payment forms
        document.querySelectorAll('.payment-form').forEach(form => {
            form.style.display = 'none';
        });
    
        // Show the selected payment form
        document.getElementById(formId).style.display = 'block';
    }

    function hidePaymentForm() {
        // Hide all payment forms
        document.querySelectorAll('.payment-form').forEach(form => {
            form.style.display = 'none';
        });
    }

// Function to show popup alert after submitting
function showPopupAlert() {
    alert('Your payment information has been saved successfully. You can now reserve.');
}

    // Get all the payment method buttons
    const bankButton = document.getElementById('bank-button'); 
    const chequeButton = document.getElementById('cheque-button');
    const mastercardButton = document.getElementById('mastercard-button'); 
    const paypalButton = document.getElementById('paypal-button');

    // Add click event listeners to payment buttons
    bankButton.addEventListener('click', () => showPaymentForm('bank-form'));
    chequeButton.addEventListener('click', () => showPaymentForm('cheque-form'));
    mastercardButton.addEventListener('click', () => showPaymentForm('mastercard-form'));
    paypalButton.addEventListener('click', () => showPaymentForm('paypal-form')); 

    // Add event listeners to cancel buttons to hide the forms
    document.querySelectorAll('.cancel-btn').forEach(button => {
        button.addEventListener('click', () => {
            hidePaymentForm(); // Hide the payment form 
        });
    });

    // Add event listeners to submit buttons to show the alert message
    document.querySelectorAll('.submit-btn').forEach(button => { 
        button.addEventListener('click', (event) => {
            event.preventDefault(); // Prevent form submission and page reload
            showPopupAlert(); // Show the popup alert
            hidePaymentForm(); // Optionally hide the form after submit
        });
    });
});
