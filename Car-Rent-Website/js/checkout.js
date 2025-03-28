document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bookingForm');
    let selectedPaymentMethod = '';

    // Initialize date restrictions
    initializeDateRestrictions();

    // Payment method selection
    const paymentMethods = document.querySelectorAll('.payment-method');
    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            selectPaymentMethod(this.dataset.method);
        });
    });

    // Form submission handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        // Collect payment details
        const paymentData = collectPaymentData();
        document.getElementById('payment-data').value = JSON.stringify(paymentData);

        // Submit the form
        this.submit();
    });

    // Form validation
    function validateForm() {
        let isValid = true;
        const requiredFields = form.querySelectorAll('input[required]');

        // Clear previous error messages
        clearErrors();

        // Validate each required field
        requiredFields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });

        // Validate payment method selection
        if (!selectedPaymentMethod) {
            showError('Please select a payment method');
            isValid = false;
        }

        // Validate dates
        if (!validateDates()) {
            isValid = false;
        }

        return isValid;
    }

    // Individual field validation
    function validateField(field) {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            return false;
        }

        // Additional validation based on field type
        switch(field.type) {
            case 'email':
                if (!isValidEmail(field.value)) {
                    showFieldError(field, 'Please enter a valid email address');
                    return false;
                }
                break;
            case 'tel':
                if (!isValidPhone(field.value)) {
                    showFieldError(field, 'Please enter a valid phone number');
                    return false;
                }
                break;
        }

        return true;
    }

    // Date validation
    function validateDates() {
        const startDate = new Date(document.getElementById('startDate').value);
        const endDate = new Date(document.getElementById('endDate').value);
        const now = new Date();

        if (!startDate || !endDate) {
            showError('Please select both start and end dates');
            return false;
        }

        if (startDate < now) {
            showError('Start date cannot be in the past');
            return false;
        }

        if (endDate <= startDate) {
            showError('End date must be after start date');
            return false;
        }

        return true;
    }

    // Initialize date input restrictions
    function initializeDateRestrictions() {
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        const now = new Date();
        now.setHours(now.getHours() + 2); // Minimum 2 hours from now

        const minDateTime = now.toISOString().slice(0, 16);
        startDateInput.min = minDateTime;

        startDateInput.addEventListener('change', function() {
            const selectedStart = new Date(this.value);
            selectedStart.setDate(selectedStart.getDate() + 1);
            endDateInput.min = selectedStart.toISOString().slice(0, 16);
            
            if (endDateInput.value && new Date(endDateInput.value) <= new Date(this.value)) {
                endDateInput.value = '';
            }
        });
    }

    // Payment method selection handler
    function selectPaymentMethod(method) {
        selectedPaymentMethod = method;
        
        // Update UI
        document.querySelectorAll('.payment-method').forEach(el => {
            el.classList.remove('active');
        });
        document.querySelector(`[data-method="${method}"]`).classList.add('active');
        
        // Show relevant form
        document.querySelectorAll('.payment-form').forEach(form => {
            form.style.display = 'none';
        });
        document.getElementById(`${method}-form`).style.display = 'block';
        
        // Update hidden input
        document.getElementById('selected-payment-method').value = method;
    }

    // Collect payment data based on selected method
    function collectPaymentData() {
        const data = {};
        
        switch(selectedPaymentMethod) {
            case 'credit-card':
                data.cardNumber = document.getElementById('card-number').value;
                data.cardName = document.getElementById('card-name').value;
                data.cardExpiry = document.getElementById('card-expiry').value;
                data.cardCvv = document.getElementById('card-cvv').value;
                break;
            case 'paypal':
                data.email = document.getElementById('paypal-email').value;
                break;
            case 'bank':
                data.accountNumber = document.getElementById('bank-account').value;
                data.routingNumber = document.getElementById('bank-routing').value;
                break;
        }
        
        return data;
    }

    // Utility functions
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        form.insertBefore(errorDiv, form.firstChild);
    }

    function showFieldError(field, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        field.classList.add('is-invalid');
        field.parentNode.appendChild(errorDiv);
    }

    function clearErrors() {
        form.querySelectorAll('.alert-danger').forEach(alert => alert.remove());
        form.querySelectorAll('.error-message').forEach(error => error.remove());
        form.querySelectorAll('.is-invalid').forEach(field => field.classList.remove('is-invalid'));
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function isValidPhone(phone) {
        return /^\+?[\d\s-]{10,}$/.test(phone);
    }
});
