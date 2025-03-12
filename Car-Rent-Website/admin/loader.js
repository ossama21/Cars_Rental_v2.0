function showLoader() {
    const loaderContainer = document.createElement('div');
    loaderContainer.className = 'loader-container';
    
    const wheel = document.createElement('div');
    wheel.className = 'wheel';
    
    const spokes = document.createElement('div');
    spokes.className = 'spokes';
    
    // Create spokes for the wheel
    for (let i = 0; i < 4; i++) {
        const spoke = document.createElement('div');
        spoke.className = 'spoke';
        spokes.appendChild(spoke);
    }
    
    const loadingText = document.createElement('div');
    loadingText.className = 'loading-text';
    loadingText.textContent = 'Starting Your Journey...';
    
    wheel.appendChild(spokes);
    loaderContainer.appendChild(wheel);
    loaderContainer.appendChild(loadingText);
    document.body.appendChild(loaderContainer);
}

function hideLoader() {
    const loader = document.querySelector('.loader-container');
    if (loader) {
        loader.remove();
    }
}

// Helper function to use the loader
function withLoader(asyncFunction) {
    return async (...args) => {
        showLoader();
        try {
            const result = await asyncFunction(...args);
            hideLoader();
            return result;
        } catch (error) {
            hideLoader();
            throw error;
        }
    };
}