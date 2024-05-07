<?php
// A simple plugin that allows you to use a subdomain with a server

// Half confirms this is not an API page as we want to slow that down as little as possible
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    // Check what page we are on
    $currentUri = $_SERVER['REQUEST_URI'];
    if(explode('/', $currentUri)[1] == ''
    || explode('/', $currentUri)[1] == 'server'
    || explode('/', $currentUri)[1] == 'account'){ // Confirm that we are not on an API page

        // Check that we are on the server listing pages
        ?>
	    <script>
	        // The JS goes here
	        document.addEventListener('pageChanged', ()=>{
	    	   var currentUri = window.location.pathname;
	    	    if (currentUri.startsWith("/server")) {
			if(!document.getElementById('subdomain')){
			    const navBar = document.querySelector('body #app div[class*="SubNavigation-sc-"] div');
			    if(closeSibling = document.querySelector('body #app div[class*="SubNavigation-sc-"] div > a:nth-child(8)')){
			    	const newTab = document.createElement('a');
			    	newTab.textContent = 'Subdomains';
			    	newTab.setAttribute('id', 'subdomain');
				newTab.setAttribute('href', '#');

			    	newTab.addEventListener('click', (e)=>{
				    e.preventDefault();
				    document.querySelector('body #app div[class*="SubNavigation-sc-"] div > a[href*="/network"][href^="/server/"]').click();
				    document.querySelector('body #app div[class*="SubNavigation-sc-"] div > a[href*="/network"][href^="/server/"]').classList.remove('active');
				    newTab.classList.add('active');
				    history.pushState({},'',location.href.slice(0,-7)+'subdomains');
				})
						
			    	navBar.insertBefore(newTab, closeSibling);
			    }
			}
		    }
		});
	    </script>
        <?php
    }
}