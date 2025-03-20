document.addEventListener('DOMContentLoaded', function() {
    // Get car details from URL
    const params = new URLSearchParams(window.location.search);
    const carId = params.get('car_id');
    let carPrice = 0;
    let discountedPrice = 0;
    let discountType = null;
    let discountValue = 0;
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

    // Check if discount information is available
    // We need to initialize these variables with PHP values
    if (document.getElementById('discounted-price')) {
        const originalPriceText = document.querySelector('.original-price .text-muted').textContent.trim();
        const discountedPriceText = document.getElementById('discounted-price').textContent.trim();
        
        // Extract price values
        carPrice = parseFloat(originalPriceText.replace('$', '').replace(',', ''));
        discountedPrice = parseFloat(discountedPriceText.replace('$', '').replace(',', ''));
        
        // Determine discount type and value
        const discountBadge = document.querySelector('.discount-badge');
        if (discountBadge) {
            const badgeText = discountBadge.textContent.trim();
            if (badgeText.includes('%')) {
                discountType = 'percentage';
                discountValue = parseFloat(badgeText.replace('% OFF', ''));
            } else {
                discountType = 'fixed';
                discountValue = parseFloat(badgeText.replace('$ OFF', '').replace('$', ''));
            }
        }
    } else {
        // No discount, get regular price
        const carPriceElement = document.getElementById('car-price');
        if (carPriceElement) {
            carPrice = parseFloat(carPriceElement.textContent.replace('$', '').replace(',', ''));
            discountedPrice = carPrice; // Same as original price when no discount
        }
    }

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

            // Calculate base price (without discount)
            const basePrice = duration * carPrice;
            
            // Calculate discount if available
            let discountAmount = 0;
            if (discountType) {
                if (discountType === 'percentage') {
                    discountAmount = basePrice * (discountValue / 100);
                } else {
                    discountAmount = discountValue * duration; // Fixed discount per day
                }
                
                // Update discount amount display
                const discountElement = document.getElementById('discount-amount');
                if (discountElement) {
                    discountElement.textContent = `-$${discountAmount.toFixed(2)}`;
                    discountElement.setAttribute('data-value', discountAmount.toFixed(2));
                }
            }

            // Calculate coupon discount if any
            let couponDiscount = 0;
            const couponRow = document.getElementById('coupon-row');
            if (couponRow && couponRow.style.display !== 'none') {
                const couponDiscountElement = document.getElementById('coupon-discount');
                if (couponDiscountElement) {
                    const couponValue = parseFloat(couponDiscountElement.getAttribute('data-value') || '0');
                    couponDiscount = (basePrice - discountAmount) * (couponValue / 100);
                    couponDiscountElement.textContent = `-$${couponDiscount.toFixed(2)}`;
                }
            }
            
            const insuranceFee = 25; // Fixed insurance fee
            const preorderFee = isPreorder ? 15 : 0; // Preorder fee

            // Calculate total after discounts
            const discountedTotal = basePrice - discountAmount - couponDiscount;
            const totalPrice = discountedTotal + insuranceFee + preorderFee;
            
            // Update UI
            document.getElementById('rental-duration').textContent = `${duration} days`;
            
            // Update original price and total savings if discount exists
            if (discountType) {
                const originalPriceElement = document.getElementById('original-price');
                const totalSavingsElement = document.getElementById('total-savings');
                
                if (originalPriceElement && totalSavingsElement) {
                    originalPriceElement.textContent = `$${basePrice.toFixed(2)}`;
                    const totalSavings = discountAmount + couponDiscount;
                    totalSavingsElement.textContent = `-$${totalSavings.toFixed(2)}`;
                }
            }
            
            // Update total price
            document.getElementById('total-price').textContent = `$${totalPrice.toFixed(2)}`;

            // Ensure preorder fee is displayed if applicable
            if (isPreorder) {
                let preorderRow = document.querySelector('.summary-row.preorder-fee');
                if (!preorderRow) {
                    preorderRow = document.createElement('div');
                    preorderRow.className = 'summary-row preorder-fee';
                    preorderRow.innerHTML = '<span>Pre-order Fee</span><span>$15.00</span>';
                    
                    // Insert before total row
                    const totalRow = document.querySelector('.total-row');
                    if (totalRow) {
                        totalRow.parentNode.insertBefore(preorderRow, totalRow);
                    }
                }
            }
            
            console.log("Price calculations: Base:", basePrice, "Discount:", discountAmount, "Coupon:", couponDiscount, "Total:", totalPrice);
        } else {
            console.log("Invalid dates or car price for calculation");
            // Set default values when calculation isn't possible
            document.getElementById('rental-duration').textContent = '0 days';
            document.getElementById('total-price').textContent = '$0.00';
            
            if (document.getElementById('original-price')) {
                document.getElementById('original-price').textContent = '$0.00';
            }
            
            if (document.getElementById('total-savings')) {
                document.getElementById('total-savings').textContent = '-$0.00';
            }
            
            if (document.getElementById('discount-amount')) {
                document.getElementById('discount-amount').textContent = '-$0.00';
            }
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
                const start = new Date(this.value);
                const minEnd = new Date(start);
                minEnd.setDate(minEnd.getDate() + 1); // Minimum 1 day rental
                endDateInput.setAttribute('min', minEnd.toISOString().slice(0, 16));
                
                if (endDateInput.value && new Date(endDateInput.value) < minEnd) {
                    endDateInput.value = minEnd.toISOString().slice(0, 16);
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

    // Call initial update
    updateRentalSummary();

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

            document.querySelector('.payment-methods').classList.remove('invalid-section');
        });
    });

    // Form submission
    const form = document.getElementById('bookingForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // First check if payment method is selected
            const selectedMethod = document.querySelector('.payment-method.active');
            if (!selectedMethod) {
                showAlert('Please select a payment method', 'error');
                // Scroll to payment methods section
                document.querySelector('.payment-methods').scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Add visual indication that payment method selection is required
                document.querySelector('.payment-methods').classList.add('invalid-section');
                setTimeout(() => {
                    document.querySelector('.payment-methods').classList.remove('invalid-section');
                }, 3000);
                return false;
            }

            // Then validate required fields
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

            const paymentMethod = selectedMethod.getAttribute('data-method');
            switch(paymentMethod) {
                case 'credit-card':
                    const cardNumber = document.getElementById('card-number')?.value;
                    const cardName = document.getElementById('card-name')?.value;
                    const cardExpiry = document.getElementById('card-expiry')?.value;
                    const cardCvv = document.getElementById('card-cvv')?.value;
                    
                    if (!cardNumber || !cardName || !cardExpiry || !cardCvv) {
                        showAlert('Please fill in all credit card details', 'error');
                        return false;
                    }
                    break;
                case 'paypal':
                    const paypalEmail = document.getElementById('paypal-email')?.value;
                    if (!paypalEmail) {
                        showAlert('Please enter your PayPal email', 'error');
                        return false;
                    }
                    break;
                case 'bank':
                    const accountNumber = document.getElementById('bank-account')?.value;
                    const routingNumber = document.getElementById('bank-routing')?.value;
                    if (!accountNumber || !routingNumber) {
                        showAlert('Please fill in all bank transfer details', 'error');
                        return false;
                    }
                    break;
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
            let paymentDetails = {};
            const selectedPaymentMethod = selectedMethod.getAttribute('data-method');
            switch(selectedPaymentMethod) {
                case 'credit-card':
                    paymentDetails = {
                        cardNumber: document.getElementById('card-number').value,
                        cardName: document.getElementById('card-name').value,
                        cardExpiry: document.getElementById('card-expiry').value,
                        cardCvv: document.getElementById('card-cvv').value
                    };
                    break;
                case 'paypal':
                    paymentDetails = {
                        email: document.getElementById('paypal-email').value
                    };
                    break;
                case 'bank':
                    paymentDetails = {
                        accountNumber: document.getElementById('bank-account').value,
                        routingNumber: document.getElementById('bank-routing').value
                    };
                    break;
            }
            formData.append('payment_data', JSON.stringify(paymentDetails));

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

    // Initialize coupon application functionality
    const applyButton = document.getElementById('apply-coupon');
    if (applyButton) {
        applyButton.addEventListener('click', function() {
            const couponCode = document.getElementById('coupon-code').value.trim();
            if (!couponCode) {
                showCouponMessage('Please enter a coupon code', 'error');
                return;
            }
            
            // Get the rental duration
            const startDate = new Date(document.getElementById('startDate')?.value || "");
            const endDate = new Date(document.getElementById('endDate')?.value || "");
            if (!startDate || !endDate || isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                showCouponMessage('Please select rental dates before applying a coupon', 'error');
                return;
            }
            
            const differenceInTime = endDate.getTime() - startDate.getTime();
            const duration = Math.max(1, Math.ceil(differenceInTime / (1000 * 3600 * 24)));
            
            // Disable the button and show loading state
            applyButton.disabled = true;
            applyButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            // Send request to validate coupon
            fetch('data/validate_coupon.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    code: couponCode,
                    rental_days: duration
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showCouponMessage(data.message || 'Coupon applied successfully!', 'success');
                    
                    // Show coupon row in summary
                    const couponRow = document.getElementById('coupon-row');
                    couponRow.style.display = 'flex';
                    
                    // Set coupon discount value
                    const couponDiscountElement = document.getElementById('coupon-discount');
                    const couponDiscount = data.coupon.discount || 0;
                    couponDiscountElement.setAttribute('data-value', couponDiscount);
                    
                    // Update summary calculations
                    updateRentalSummary();
                } else {
                    // Show error message
                    showCouponMessage(data.error || 'Invalid coupon code', 'error');
                }
            })
            .catch(error => {
                console.error('Error validating coupon:', error);
                showCouponMessage('Error validating coupon. Please try again.', 'error');
            })
            .finally(() => {
                // Re-enable the button
                applyButton.disabled = false;
                applyButton.textContent = 'Apply';
            });
        });
    }

    function showCouponMessage(message, type) {
        const successAlert = document.getElementById('coupon-success');
        const errorAlert = document.getElementById('coupon-error');
        
        if (type === 'success') {
            successAlert.querySelector('#coupon-message').textContent = message;
            successAlert.style.display = 'block';
            errorAlert.style.display = 'none';
        } else {
            errorAlert.querySelector('#error-message').textContent = message;
            errorAlert.style.display = 'block';
            successAlert.style.display = 'none';
        }
        
        // Hide message after 5 seconds
        setTimeout(() => {
            if (type === 'success') {
                successAlert.style.display = 'none';
            } else {
                errorAlert.style.display = 'none';
            }
        }, 5000);
    }
});
