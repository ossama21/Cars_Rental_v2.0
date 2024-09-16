import products from "./products.js";

// Wrap all the code to ensure it runs after the DOM is fully loaded
window.addEventListener('DOMContentLoaded', function () {
    // Extract car details from URL parameters
    const params = new URLSearchParams(window.location.search);
    const carId = params.get('id'); // Get the car ID from the URL

    if (carId) {
        // Now that we have the carId, retrieve the car details
        import('./products.js').then(module => {
            const cars = module.default; // Assuming you're exporting cars data as default
            const selectedCar = cars.find(car => car.id === carId);
            
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
            }
        }).catch(error => {
            console.error('Error loading car details:', error);
        });
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
});
