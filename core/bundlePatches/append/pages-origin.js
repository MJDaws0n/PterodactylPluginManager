// Page changing listener
const pageChangedEvent = new Event('pageChanged'); // Create a custom event 'pageChanged'

// Check if the current URL path starts with '/auth' or '/'
if (window.location.pathname.startsWith('/auth') || window.location.pathname.startsWith('/')) {
    
    // Wait for the footer element to appear and update its content
    waitForElement('p[class^="LoginFormContainer___Styled"]', function() {
        const footer = document.querySelector('p[class^="LoginFormContainer___Styled"]');
        footer.innerHTML = copyrightText; // Set the footer content
    });

    // Create a MutationObserver to detect changes in the DOM
    const observer = new MutationObserver((mutationsList) => {
        mutationsList.forEach((mutation) => {
            // Check if nodes are added to the DOM
            if (mutation.addedNodes.length) {
                document.dispatchEvent(pageChangedEvent); // Dispatch the 'pageChanged' event
                mutation.addedNodes.forEach((node) => {
                    // Ensure the added node is an element and is part of the #app element
                    if (node.nodeType === 1 && document.querySelector('#app').contains(node.parentNode)) {
                        // Check for a specific footer element and update its content
                        if (document.querySelector('p[class^="LoginFormContainer___Styled"] a[class^="LoginFormContainer___Styled"]')) {
                            const footer = document.querySelector('p[class^="LoginFormContainer___Styled"]');
                            footer.innerHTML = copyrightText; // Update footer content again
                        }
                    }
                });
            }
        });
    });

    // Wait for the #app element to appear and start observing DOM changes
    waitForElement('#app', function() {
        observer.observe(document.documentElement, { childList: true, subtree: true });
    });
}

// Function to wait for an element to appear in the DOM
function waitForElement(selector, callback) {
    const observer = new MutationObserver(() => {
        const elements = document.querySelectorAll(selector); // Select elements by the given selector
        if (elements.length > 0) { // If the element exists
            observer.disconnect(); // Stop observing once the element is found
            callback(elements); // Execute the callback function
        }
    });
    observer.observe(document.documentElement, { childList: true, subtree: true }); // Observe the entire document for changes
}

// Create a listener for 'pageChanged' event to handle tab changes
document.addEventListener('pageChanged', () => {
    const subNavigation = document.querySelector('body #app div[class*="SubNavigation-sc-"] div');
    if (subNavigation) {
        document.querySelectorAll('body #app div[class*="SubNavigation-sc-"] div a').forEach(element => {
            // Check if the element has been processed or not
            if ((element.hasAttribute('data-tabChangedChecked') && element.getAttribute('data-tabChangedChecked') != 'true') || (!element.hasAttribute('data-tabChangedChecked'))) {
                element.setAttribute('data-tabChangedChecked', 'true'); // Mark element as processed
                // Add a click event listener to dispatch a 'tabChanged' event when a tab is clicked
                element.addEventListener('click', (e) => {
                    document.dispatchEvent(new CustomEvent('tabChanged', { detail: { tab_name: element.textContent } }));
                });
            }
        });
    }
});
(function retryScriptLoad() {
    let retryCount = 0;
    const maxRetries = 3; // You can adjust this as needed
    const retryDelay = 5000; // 5 seconds delay

    // Function to reload scripts
    function reloadScript() {
        // Select all existing script tags
        const scripts = document.querySelectorAll('script');

        scripts.forEach(script => {
            const newScript = document.createElement('script');
            newScript.src = `${script.src}?cacheBust=${new Date().getTime()}`;
            newScript.async = true;

            newScript.onload = function() {
                console.log('Script reloaded successfully');
            };

            newScript.onerror = function() {
                console.error('Error reloading script');
                retryLoad();
            };

            // Replace old script
            script.parentNode.replaceChild(newScript, script);
        });
    }

    // Retry function
    function retryLoad() {
        retryCount++;
        if (retryCount <= maxRetries) {
            console.log(`Retrying in ${retryDelay / 1000} seconds...`);
            setTimeout(reloadScript, retryDelay);
        } else {
            console.error('Max retry limit reached. Failed to load scripts.');
        }
    }

    // Error handler
    window.onerror = function() {
        console.error('Script error detected, attempting to reload scripts.');
        reloadScript();
        return true; // Prevent default error handling
    };
})();
