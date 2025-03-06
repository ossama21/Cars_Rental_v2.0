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
    }

    signUpButton.addEventListener('click', toggleForms);
    signInButton.addEventListener('click', toggleForms);

    // Add style for smooth transitions
    const style = document.createElement('style');
    style.textContent = `
        #signup, #signIn {
            transition: opacity 0.3s ease-in-out;
        }
    `;
    document.head.appendChild(style);
});