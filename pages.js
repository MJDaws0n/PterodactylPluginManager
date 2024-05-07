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
                        changeFooter(observer);
                    }
                }
            });
          }
        });
    });
    
    waitForElement('#app',function(){observer.observe(document.documentElement,{childList:true,subtree:true});});
}

const pageChangedEvent = new Event('pageChanged');
if(window.location.pathname.startsWith('/auth') || window.location.pathname.startsWith('/')){
    waitForElement('p[class^="LoginFormContainer___Styled"]',function(){
        const footer = document.querySelector('p[class^="LoginFormContainer___Styled"]');
        footer.innerHTML = copyrightText;
    });

    const observer = new MutationObserver((mutationsList) => {
        mutationsList.forEach((mutation) => {
        if (mutation.addedNodes.length) {
            document.dispatchEvent(pageChangedEvent);
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1 && document.querySelector('#app').contains(node.parentNode)) {
                    if(document.querySelector('p[class^="LoginFormContainer___Styled"] a[class^="LoginFormContainer___Styled"]')){
                        const footer = document.querySelector('p[class^="LoginFormContainer___Styled"]');
                        footer.innerHTML = copyrightText;
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

// Check for user being logged out
var currentUri = window.location.pathname;
if (!currentUri.startsWith("/auth/login")) {  
  fetch('/api/client?page=1')
    .then(response => {
    if (!response.ok) {
      if (response.status === 401) {
        document.querySelector('body #app div[class*="NavigationBar__RightNavigation-sc-"] button').click();
      } else {
        console.error('Error:', response.status);
      }
      throw new Error('Network response was not ok.');
    }
    return response.json();
  })
    .then(data => {})
    .catch(error => {
    //document.querySelector('body #app div[class*="NavigationBar__RightNavigation-sc-"] button').click();
  });
}

// Create a listner for changing tabs
document.addEventListener('pageChanged', ()=>{
    if(document.querySelector('body #app div[class*="SubNavigation-sc-"] div')){
        document.querySelectorAll('body #app div[class*="SubNavigation-sc-"] div a').forEach(element => {
            if((element.hasAttribute('data-tabChangedChecked') && element.getAttribute('data-tabChangedChecked') != 'true') || (!element.hasAttribute('data-tabChangedChecked'))){
                element.setAttribute('data-tabChangedChecked', 'true');
                element.addEventListener('click', (e) => {
                    document.dispatchEvent(new CustomEvent('tabChanged', { detail: { tab_name: element.textContent } }));
                });
            }
        });
    }
});