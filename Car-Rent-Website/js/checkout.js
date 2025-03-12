document.addEventListener('DOMContentLoaded', function() {
    // Get car details from URL
    const params = new URLSearchParams(window.location.search);
    const carId = params.get('car_id');
    let carPrice = 0;
    let currentStep = 1;
    const totalSteps = 3;
    
    // Initialize date inputs
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    if (!carId) {
        showAlert('No car selected', 'error');
        return;
    }

    console.log("Car ID from URL:", carId);

    // Initialize car data
    fetch('checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `car_id=${carId}`
    })
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.car) {
                const car = data.car;
                console.log("Car data received:", car);
                updateCarDetails(car);
                carPrice = parseFloat(car.price);
                document.getElementById('car-price').textContent = `$${carPrice.toFixed(2)}`;
                updateRentalSummary(); // Initial update
            } else {
                console.error("Failed to load car details:", data);
                showAlert('Failed to load car details', 'error');
            }
        } catch (error) {
            console.error('Error parsing response:', text, error);
            showAlert('Failed to load car details', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to load car details', 'error');
    });

    function updateCarDetails(car) {
        document.getElementById('car-name').textContent = car.name;
        document.getElementById('car-description')?.textContent = car.description;
        document.getElementById('car-price').textContent = `$${car.price}`;
        document.getElementById('car-image').src = car.image;
        document.getElementById('car-model').textContent = car.model;
        document.getElementById('car-transmission').textContent = car.transmission;
        document.getElementById('car-interior').textContent = car.interior || 'Standard';
        document.getElementById('car-brand').textContent = car.brand;
        document.getElementById('car_id').value = car.id;
    }

    // Progress steps handling
    const steps = document.querySelectorAll('.progress-step');

    function updateProgressSteps() {
        steps.forEach(step => {
            const stepNumber = step.querySelector('.step-number');
            const stepNum = parseInt(stepNumber.dataset.step);
            
            if (stepNum < currentStep) {
                stepNumber.classList.add('completed');
                stepNumber.classList.remove('active');
            } else if (stepNum === currentStep) {
                stepNumber.classList.add('active');
                stepNumber.classList.remove('completed');
            } else {
                stepNumber.classList.remove('active', 'completed');
            }
        });
    }

    function validateStep(step) {
        const stepElement = document.querySelector(`.progress-step:nth-child(${step})`);
        const requiredFields = stepElement.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('invalid');
                field.addEventListener('input', () => field.classList.remove('invalid'));
            } else {
                field.classList.remove('invalid');
            }
        });

        if (!isValid) {
            showAlert('Please fill in all required fields', 'error');
        }

        return isValid;
    }

    // Date handling and price calculation
    function updateRentalSummary() {
        const startDate = new Date(document.getElementById('startDate')?.value || "");
        const endDate = new Date(document.getElementById('endDate')?.value || "");
        const isPreorder = document.querySelector('input[name="is_preorder"]').value === '1';
        
        console.log("Update summary called - Start date:", startDate, "End date:", endDate, "Car price:", carPrice);

        if (startDate && endDate && !isNaN(startDate.getTime()) && !isNaN(endDate.getTime()) && carPrice > 0) {
            // Calculate difference in milliseconds and convert to days
            const differenceInTime = endDate.getTime() - startDate.getTime();
            const differenceInDays = Math.ceil(differenceInTime / (1000 * 3600 * 24));
            
            // Ensure minimum rental duration is 1 day
            const duration = Math.max(1, differenceInDays);
            
            if (duration <= 0) {
                showAlert('Return date must be after pick-up date', 'error');
                return;
            }

            // Validate minimum duration for preorders
            if (isPreorder && duration < 3) {
                showAlert('Pre-orders require a minimum rental duration of 3 days', 'error');
                document.getElementById('endDate').classList.add('invalid');
                return;
            }
            
            console.log("Duration calculated:", duration, "days");

            const basePrice = duration * carPrice;
            const insuranceFee = 25; // Fixed insurance fee
            const preorderFee = isPreorder ? 15 : 0; // Updated to $15
            const totalPrice = basePrice + insuranceFee + preorderFee;
            
            document.getElementById('rental-duration').textContent = `${duration} days`;
            document.getElementById('total-price').textContent = `$${totalPrice.toFixed(2)}`;

            if (isPreorder) {
                // Show preorder fee in summary
                let preorderRow = document.querySelector('.summary-row.preorder-fee');
                if (!preorderRow) {
                    preorderRow = document.createElement('div');
                    preorderRow.className = 'summary-row preorder-fee';
                    preorderRow.innerHTML = '<span>Pre-order Fee</span><span>$15.00</span>';
                    document.querySelector('.rental-summary .total-row').insertAdjacentElement('beforebegin', preorderRow);
                }
            }
            
            console.log("Total price calculated:", totalPrice, "Total Duration:", duration);
        } else {
            console.log("Invalid dates or car price for calculation");
            // Set default values when calculation isn't possible
            document.getElementById('rental-duration').textContent = '0 days';
            document.getElementById('total-price').textContent = '$0.00';
        }
    }

    // Initialize date inputs with minimums
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset() + 120); // Add 2 hours minimum
    const minDateTime = now.toISOString().slice(0, 16);
    
    if (startDateInput && endDateInput) {
        startDateInput.setAttribute('min', minDateTime);
        endDateInput.setAttribute('min', minDateTime);

        // Add event listeners for immediate updates
        startDateInput.addEventListener('input', function() {
            console.log('Start date changed:', this.value);
            if (this.value) {
                endDateInput.setAttribute('min', this.value);
                if (endDateInput.value && new Date(endDateInput.value) < new Date(this.value)) {
                    endDateInput.value = this.value;
                }
                updateRentalSummary();
            }
        });

        endDateInput.addEventListener('input', function() {
            console.log('End date changed:', this.value);
            if (this.value) {
                updateRentalSummary();
            }
        });

        // Also add change event listeners for browsers where input might not fire
        startDateInput.addEventListener('change', updateRentalSummary);
        endDateInput.addEventListener('change', updateRentalSummary);
    }

    // Helper function to show alerts
    function showAlert(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            ${message}
        `;
        
        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            setTimeout(() => alertDiv.remove(), 300);
        }, 4700);
    }

    // Credit card input formatting
    const cardNumberInput = document.getElementById('card-number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = value;
        });
    }

    // CVV input formatting
    const cvvInput = document.getElementById('card-cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 3);
        });
    }
    
    // Direct handling of payment methods with vanilla JS
    const paymentMethods = document.querySelectorAll('.payment-method');
    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            // Remove active class from all payment methods
            paymentMethods.forEach(m => m.classList.remove('active'));
            
            // Add active class to this method
            this.classList.add('active');
            
            // Hide all payment forms
            document.querySelectorAll('.payment-form').forEach(form => {
                form.style.display = 'none';
            });
            
            // Show the selected payment form
            const methodName = this.getAttribute('data-method');
            const formToShow = document.getElementById(`${methodName}-form`);
            if (formToShow) {
                formToShow.style.display = 'block';
            }
            
            // Update the hidden input
            document.getElementById('selected-payment-method').value = methodName;
        });
    });

    // Form submission
    const form = document.getElementById('bookingForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate required fields
            let isValid = true;
            this.querySelectorAll('[required]').forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('invalid');
                    field.addEventListener('input', () => field.classList.remove('invalid'));
                }
            });

            if (!isValid) {
                showAlert('Please fill in all required fields', 'error');
                return false;
            }

            // Check if payment method is selected
            const selectedMethod = document.querySelector('.payment-method.active');
            if (!selectedMethod) {
                showAlert('Please select a payment method', 'error');
                return false;
            }

            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            // Create form data object
            const formData = new URLSearchParams();
            formData.append('submit_booking', 'true');
            formData.append('payment_method', selectedMethod.getAttribute('data-method'));
            formData.append('car_id', carId);
            formData.append('name', `${document.getElementById('firstName').value} ${document.getElementById('lastName').value}`);
            formData.append('phone', document.getElementById('phone').value);
            formData.append('email', document.getElementById('email').value);
            formData.append('start_date', document.getElementById('startDate').value);
            formData.append('end_date', document.getElementById('endDate').value);

            // Add payment details
            let paymentData = {};
            const method = selectedMethod.getAttribute('data-method');
            switch(method) {
                case 'credit-card':
                    paymentData = {
                        cardNumber: document.getElementById('card-number').value,
                        cardName: document.getElementById('card-name').value,
                        cardExpiry: document.getElementById('card-expiry').value,
                        cardCvv: document.getElementById('card-cvv').value
                    };
                    break;
                case 'paypal':
                    paymentData = {
                        email: document.getElementById('paypal-email').value
                    };
                    break;
                case 'bank':
                    paymentData = {
                        accountNumber: document.getElementById('bank-account').value,
                        routingNumber: document.getElementById('bank-routing').value
                    };
                    break;
            }
            formData.append('payment_data', JSON.stringify(paymentData));

            // Send request
            fetch('checkout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData.toString()
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(text => {
                console.log('Response text:', text);
                try {
                    const response = JSON.parse(text);
                    if (response.success) {
                        // Success! Redirect to confirmation page
                        console.log('Booking successful, redirecting...');
                        window.location.href = 'confirmation.php';
                    } else {
                        throw new Error(response.message || 'Unknown error occurred');
                    }
                } catch (error) {
                    console.error('Error parsing JSON:', error);
                    throw new Error('Invalid server response');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert(error.message || 'An error occurred. Please try again.', 'error');
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        });
    }

    // Helper function to show alerts
    function showAlert(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
        
        const form = document.getElementById('bookingForm');
        form.insertBefore(alertDiv, form.firstChild);
        
        setTimeout(() => alertDiv.remove(), 5000);
    }

    // Track progress steps
    const stepNumbers = document.querySelectorAll('.step-number');
    const progressSteps = document.querySelectorAll('.progress-step');

    // Function to update steps
    function updateSteps(newStep) {
        stepNumbers.forEach((step, index) => {
            const stepNum = index + 1;
            if (stepNum < newStep) {
                step.classList.remove('active');
                step.classList.add('completed');
            } else if (stepNum === newStep) {
                step.classList.add('active');
                step.classList.remove('completed');
            } else {
                step.classList.remove('active', 'completed');
            }
        });

        // Update visibility of step content
        progressSteps.forEach((step, index) => {
            const content = step.querySelector('.step-content');
            if (index + 1 === newStep) {
                content.style.display = 'block';
                step.classList.add('active');
            } else {
                content.style.display = 'none';
                step.classList.remove('active');
            }
        });

        // Scroll to active step
        const activeStep = document.querySelector(`.progress-step:nth-child(${newStep})`);
        activeStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Function to validate step
    function validateStep(step) {
        let isValid = true;
        const requiredFields = progressSteps[step - 1].querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value) {
                isValid = false;
                field.classList.add('invalid');
                showError(field, 'This field is required');
            } else {
                field.classList.remove('invalid');
                removeError(field);
            }
        });

        // Additional validation for specific steps
        if (step === 2) {
            const startDate = new Date(document.getElementById('startDate').value);
            const endDate = new Date(document.getElementById('endDate').value);
            const minEndDate = new Date(startDate);
            minEndDate.setDate(startDate.getDate() + 1);

            if (endDate < minEndDate) {
                isValid = false;
                showError(document.getElementById('endDate'), 'Return date must be at least 1 day after pick-up');
            }
        } else if (step === 3) {
            const selectedMethod = document.getElementById('selected-payment-method').value;
            if (!selectedMethod) {
                isValid = false;
                showAlert('Please select a payment method');
            }
        }

        return isValid;
    }

    // Error handling functions
    function showError(field, message) {
        const existingError = field.nextElementSibling;
        if (existingError && existingError.classList.contains('error-message')) {
            existingError.textContent = message;
        } else {
            const error = document.createElement('div');
            error.className = 'error-message';
            error.textContent = message;
            field.parentNode.insertBefore(error, field.nextSibling);
        }
    }

    function removeError(field) {
        const error = field.nextElementSibling;
        if (error && error.classList.contains('error-message')) {
            error.remove();
        }
    }

    function showAlert(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger';
        alert.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        
        const form = document.getElementById('bookingForm');
        form.insertBefore(alert, form.firstChild);
        
        setTimeout(() => alert.remove(), 5000);
    }

    // Add navigation buttons to each step
    progressSteps.forEach((step, index) => {
        const stepNum = index + 1;
        const buttonContainer = document.createElement('div');
        buttonContainer.className = 'step-buttons';
        
        if (stepNum > 1) {
            const prevButton = document.createElement('button');
            prevButton.type = 'button';
            prevButton.className = 'btn btn-outline-secondary';
            prevButton.textContent = 'Previous';
            prevButton.onclick = () => {
                currentStep = stepNum - 1;
                updateSteps(currentStep);
            };
            buttonContainer.appendChild(prevButton);
        }
        
        if (stepNum < totalSteps) {
            const nextButton = document.createElement('button');
            nextButton.type = 'button';
            nextButton.className = 'btn btn-primary';
            nextButton.textContent = 'Next';
            nextButton.onclick = () => {
                if (validateStep(stepNum)) {
                    currentStep = stepNum + 1;
                    updateSteps(currentStep);
                }
            };
            buttonContainer.appendChild(nextButton);
        }
        
        step.querySelector('.step-content').appendChild(buttonContainer);
    });

    // Initial setup
    updateSteps(currentStep);

    // Form validation before submission
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        for (let step = 1; step <= totalSteps; step++) {
            if (!validateStep(step)) {
                isValid = false;
                currentStep = step;
                updateSteps(step);
                break;
            }
        }
        
        if (isValid) {
            this.submit();
        }
    });

    // Real-time validation for date fields

    startDateInput.addEventListener('change', function() {
        const startDate = new Date(this.value);
        const minEndDate = new Date(startDate);
        minEndDate.setDate(startDate.getDate() + 1);
        endDateInput.min = minEndDate.toISOString().slice(0, 16);
        
        if (endDateInput.value && new Date(endDateInput.value) < minEndDate) {
            endDateInput.value = minEndDate.toISOString().slice(0, 16);
        }
    });
});
