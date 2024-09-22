// Wrap all the code to ensure it runs after the DOM is fully loaded
window.addEventListener('DOMContentLoaded', function () {
    // Extract car details from URL parameters
    const params = new URLSearchParams(window.location.search);
    const carId = params.get('product_id');

    if (carId) {
        // Send AJAX request to retrieve car details from the server
        fetch('checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `car_id=${carId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.car) {
                const selectedCar = data.car;

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
        })
        .catch(error => {
            console.error('Error fetching car details:', error);
        });
    } else {
        console.error('Car ID not found in URL');
    }

    // Payment method selection variable
    let selectedPaymentMethod = null;

    // Get all the payment method buttons
    const bankButton = document.getElementById('bank-button');
    const chequeButton = document.getElementById('cheque-button');
    const mastercardButton = document.getElementById('mastercard-button');
    const paypalButton = document.getElementById('paypal-button');

    // Reserve button
    const reserveButton = document.querySelector('input[type="submit"]');
    reserveButton.disabled = true;  // Disable the reserve button by default

    // Function to show payment forms
    function showPaymentForm(formId, paymentMethod) {
        // Hide all payment forms
        document.querySelectorAll('.payment-form').forEach(form => {
            form.style.display = 'none';
        });

        // Show the selected payment form
        document.getElementById(formId).style.display = 'block';

        // Store the selected payment method
        selectedPaymentMethod = paymentMethod;
    }

    function hidePaymentForm() {
        // Hide all payment forms
        document.querySelectorAll('.payment-form').forEach(form => {
            form.style.display = 'none';
        });
    }

    // Function to check if all fields are filled in the selected payment method
    function checkPaymentFieldsFilled() {
        if (selectedPaymentMethod) {
            const form = document.getElementById(`${selectedPaymentMethod}-form`);
            const inputs = form.querySelectorAll('input');
            return Array.from(inputs).every(input => input.value.trim() !== '');
        }
        return false;
    }

    // Add click event listeners to payment buttons
    bankButton.addEventListener('click', () => showPaymentForm('bank-form', 'bank'));
    chequeButton.addEventListener('click', () => showPaymentForm('cheque-form', 'cheque'));
    mastercardButton.addEventListener('click', () => showPaymentForm('mastercard-form', 'mastercard'));
    paypalButton.addEventListener('click', () => showPaymentForm('paypal-form', 'paypal'));

    // Add event listeners to cancel buttons to hide the forms
    document.querySelectorAll('.cancel-btn').forEach(button => {
        button.addEventListener('click', () => {
            hidePaymentForm(); // Hide the payment form
        });
    });

    // Add event listeners to submit buttons to enable the reserve button
    document.querySelectorAll('.submit-btn').forEach(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault();  // Prevent form submission and page reload
            if (checkPaymentFieldsFilled()) {
                reserveButton.disabled = false;
                alert('Your payment information has been saved. You can now reserve.');
            } else {
                alert('Please fill in all required fields for the selected payment method.');
            }
            hidePaymentForm(); // Optionally hide the form after submit
        });
    });

    // Attach form submit event listener to booking form
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(event) {
            if (!checkPaymentFieldsFilled()) {
                event.preventDefault();  // Prevent the form from submitting
                alert("You need to fill in all fields for the selected payment method before reserving.");
            }
        });
    }
});
