document.addEventListener('DOMContentLoaded', ()=>{
    changeFooter();
});

window.addEventListener('popstate', function(event) {
    console.log('here');
    changeFooter();
});
  

function changeFooter(){
    if(typeof onAdminPage !== 'undefined' && onAdminPage){
        const footer = document.querySelector('.main-footer');

        // Remove the old copright - for some reason pterodactyl does it really strangly and it's hard to edit
        footer.childNodes[2].remove();  // Remove with ID 2
        footer.childNodes[2].remove();  // Remove the new node with ID 2 - but would be 3rd
        footer.childNodes[2].remove();  // Remove the new node with ID 2 - but would be 4th

        // Update the text
        footer.innerHTML += copyrightText;
    } else{
        waitForElement('.PageContentBlock___StyledP-sc-kbxq2g-3.dcHyfd', function(element) {
            const footer = document.querySelector('.PageContentBlock___StyledP-sc-kbxq2g-3.dcHyfd');
            footer.innerHTML = copyrightText;    // Not += because we want to completely remove what's already there
        });
    }
}

{
    var i = 0;
    document.querySelector('.fade-enter-done').children[0].children.forEach((child), ()=>{
        if(i != 0){
            child.addEventListener('click', function(){
                changeFooter();
            })
        }
        i++;
    })
}

// Function to wait for an element to appear
function waitForElement(selector, callback) {
    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            // Check if the target node or its subtree contains the desired element
            if (document.querySelector(selector)) {
                // Disconnect the observer once the element is found
                observer.disconnect();
                // Call the callback function with the target element
                callback(document.querySelector(selector));
            }
        });
    });

    // Start observing the document body and its subtree for changes
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}