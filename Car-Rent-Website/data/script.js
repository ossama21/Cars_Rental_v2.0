document.addEventListener('DOMContentLoaded', function() {
    const signUpButton = document.getElementById('signUpButton');
    const signInButton = document.getElementById('signInButton');
    const signUpContainer = document.getElementById('signup');
    const signInContainer = document.getElementById('signIn');
    
    function toggleForms() {
        // First, set both containers to opacity 0
        signUpContainer.style.opacity = '0';
        signInContainer.style.opacity = '0';
        
        // After a short delay, switch the displays
        setTimeout(() => {
            if (signUpContainer.style.display === 'none') {
                signUpContainer.style.display = 'block';
                signInContainer.style.display = 'none';
                // After switching display, fade in the signup form
                setTimeout(() => {
                    signUpContainer.style.opacity = '1';
                }, 50);
            } else {
                signInContainer.style.display = 'block';
                signUpContainer.style.display = 'none';
                // After switching display, fade in the signin form
                setTimeout(() => {
                    signInContainer.style.opacity = '1';
                }, 50);
            }
        }, 300);

        // Toggle form-active class on body for mobile styles
        document.body.classList.toggle('form-active');
    }

    // Event listeners
    signUpButton.addEventListener('click', toggleForms);
    signInButton.addEventListener('click', toggleForms);

    // Add style for smooth transitions
    const style = document.createElement('style');
    style.textContent = `
        #signup, #signIn {
            transition: opacity 0.3s ease-in-out;
        }
        
        @media (max-width: 991.98px) {
            #signup, #signIn {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 90%;
                max-width: 500px;
                z-index: 1000;
                background: white;
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            }
        }
    `;
    document.head.appendChild(style);
});