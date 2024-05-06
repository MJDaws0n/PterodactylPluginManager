<?php
// A simple plugin that changes the default pterodactyl user icon to the gravitar icon for there email

// Half confirms this is not an API page as we want to slow that down as little as possible
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    // Get the current pages URI (the part after the domain)
    $currentUri = $_SERVER['REQUEST_URI'];

    // Check that we are on a page we want to change
    if(explode('/', $currentUri)[1] == ''
    || explode('/', $currentUri)[1] == 'server'
    || explode('/', $currentUri)[1] == 'account'){
        // This guarantees that we are on the account page, server listing page, editing a server or in some cases the login page
        // We can make sure that it's not the login page inside the JavaScript

        ?>
        <!-- These are essential for the md5 function we need -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/core.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/md5.js"></script>

            <script>
                // Use a built in listner to listen for anything on the page changing, this means even if we click onto a new page without the page refreshing, the PFP will still update
                document.addEventListener('pageChanged', ()=>{
                    // We can now check if we are on the login page
                    var currentUri = window.location.pathname;
                    if (!currentUri.startsWith("/auth/login")) {                        
                        // Trying to be as specific as possible to avoid clashes
                        // Remove the old icon
                        if(document.querySelector('body #app div[class*="NavigationBar__RightNavigation-sc-"] a[href="/account"] span')){
                            document.querySelector('body #app div[class*="NavigationBar__RightNavigation-sc-"] a[href="/account"] span').remove();

                            // Add the new icon
                            const icon = document.createElement('img');

                            // Set the source URL using MD5 from CryptoJS
                            icon.src = 'http://www.gravatar.com/avatar/' + CryptoJS.MD5('d4ws70@gmail.com').toString() + '?s=20';

                            // Append the new icon
                            document.querySelector('body #app div[class*="NavigationBar__RightNavigation-sc-"] a[href="/account"]').append(icon);
                        }
                    }
                });
            </script>
            
        <?php
    }
}