// Dark Mode Theme Toggle
document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const themeToggle = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement;
    
    // Check for saved user preference
    const savedTheme = localStorage.getItem('theme');
    
    // Function to set theme
    function setTheme(isDark) {
        if (isDark) {
            htmlElement.setAttribute('data-theme', 'dark');
            themeToggle.checked = true;
        } else {
            htmlElement.removeAttribute('data-theme');
            themeToggle.checked = false;
        }
    }
    
    // Check saved preference, then system preference
    if (savedTheme) {
        setTheme(savedTheme === 'dark');
    } else {
        // Check system preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        setTheme(prefersDark);
    }
    
    // Toggle theme when the switch is clicked
    themeToggle.addEventListener('change', function() {
        const isDark = this.checked;
        setTheme(isDark);
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });
    
    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        // Only change if user hasn't set a preference
        if (!localStorage.getItem('theme')) {
            setTheme(e.matches);
        }
    });
});