if(typeof onAdminPage !== 'undefined' && onAdminPage){
    waitForElement('footer.main-footer',function(){
        const footer = document.querySelector('.main-footer');
    
        // Remove the old copright - for some reason pterodactyl does it really strangly and it's hard to edit
        footer.childNodes[2].remove();  // Remove with ID 2
        footer.childNodes[2].remove();  // Remove the new node with ID 2 - but would be 3rd
        footer.childNodes[2].remove();  // Remove the new node with ID 2 - but would be 4th
    
        // Update the text
        footer.innerHTML += copyrightText;
    });
} else{
    function changeFooter(observer){
        observer.disconnect();
        const footer = document.querySelector('[class^="ContentContainer-"][class*=" PageContentBlock___StyledContentContainer"] [class^="PageContentBlock___Styled"]');
        footer.innerHTML = copyrightText;    // Not += because we want to completely remove what's already there
        observer.observe(document.documentElement,{childList:true,subtree:true});
    }
    
    const observer = new MutationObserver((mutationsList) => {
        mutationsList.forEach((mutation) => {
          if (mutation.addedNodes.length) {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1 && document.querySelector('#app').contains(node.parentNode)) {
                    if(document.querySelector('[class^="ContentContainer-"][class*=" PageContentBlock___StyledContentContainer"] [class^="PageContentBlock___Styled"] a[class^="PageContentBlock___Styled"]')){
                        console.log('Change');
                        changeFooter(observer);
                    }
                }
            });
          }
        });
    });
    
    waitForElement('#app',function(){observer.observe(document.documentElement,{childList:true,subtree:true});});
}

// Function to wait for an element to appear
function waitForElement(selector, callback) {
    const observer = new MutationObserver(() => {
        const elements = document.querySelectorAll(selector);
        if (elements.length > 0) {
            observer.disconnect();
            callback(elements);
        }
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });
}