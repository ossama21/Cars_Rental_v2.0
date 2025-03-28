// Language selector dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const languageSelector = document.querySelector('.language-selector');
    const currentLang = document.querySelector('.current-lang');
    
    if (currentLang) {
        currentLang.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            languageSelector.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!languageSelector.contains(e.target)) {
                languageSelector.classList.remove('active');
            }
        });
    }
});