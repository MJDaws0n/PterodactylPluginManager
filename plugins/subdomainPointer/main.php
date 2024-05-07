<?php
// Half confirms this is not an API page as we want to slow that down as little as possible
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    // Check what page we are on
    $currentUri = $_SERVER['REQUEST_URI'];
    if(explode('/', $currentUri)[1] == 'server'){ // Confirm that we are on the page we should be
        // Check that we are on the server listing pages
        ?>
        <script>
            // The JS goes here
            var currentTabElement;
            var currentUri = window.location.pathname;

            // Get some infomation we will need in the future
            var serverInfomation = false;

            document.addEventListener('pageChanged', ()=>{
                currentUri = window.location.pathname;
                if (currentUri.startsWith("/server")) {
                    // Update the current server infomation
                    if(!serverInfomation){
                        serverInfomation = true;
                        fetch(('https://panel.webworkshub.online/api/client/servers/'+window.location.pathname.replace(/^\/+|\/+$/g, '').split('/').filter(part => part !== '')[1]))
                        .then(response => {
                            return response.json();
                        })
                        .then(data => {
                            serverInfomation = data;
                        })
                        .catch(error => {
                        });
                    }
                    if(!document.getElementById('subdomainTab')){
                        const navBar = document.querySelector('body #app div[class*="SubNavigation-sc-"] div');
                        if(closeSibling = document.querySelector('body #app div[class*="SubNavigation-sc-"] div > a:nth-child(8)')){
                            const newTab = document.createElement('a');
                            newTab.textContent = 'Subdomains';
                            newTab.setAttribute('id', 'subdomainTab');
                            newTab.setAttribute('href', 'subdomains');

                            // Tab click events
                            newTab.addEventListener('click', (e)=>{
                                e.preventDefault();

                                // Remove active from previous tab
                                if(document.querySelector('body #app div[class*="SubNavigation-sc-"] div > a.active')){
                                    document.querySelector('body #app div[class*="SubNavigation-sc-"] div > a.active').classList.remove('active');
                                }

                                // Set to the active tab
                                newTab.classList.add('active');

                                // Set the domain
                                if(window.location.pathname.replace(/^\/+|\/+$/g, '').split('/').filter(part => part !== '').length != 2){
                                    history.pushState({},'',location.href.slice(0, location.href.lastIndexOf('/') + 1)+'subdomains');
                                } else{
                                    history.pushState({},'',location.href + '/subdomains');
                                }

                                // Start creating the page
                                const page = document.querySelector('body #app div[class*="App___StyledDiv-sc-"] > div:nth-child(3) section div');
                                page.innerHTML = `
                                <style>
                                .BASE_BOX {
                                    display: flex;
                                    border-radius: 0.25rem;
                                    text-decoration: none;
                                    --tw-text-opacity: 1;
                                    color: hsla(210, 16%, 82%, var(--tw-text-opacity));
                                    -webkit-box-align: center;
                                    align-items: center;
                                    --tw-bg-opacity: 1;
                                    background-color: hsla(209, 18%, 30%, var(--tw-bg-opacity));
                                    padding: 1rem;
                                    border-width: 1px;
                                    border-color: rgba(0, 0, 0, 0);
                                    transition-property: background-color, border-color, color, fill, stroke;
                                    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                                    transition-duration: 150ms;
                                    overflow: hidden;
                                }

                                .NOTES_BOX	{
                                    resize: none;
                                    appearance: none;
                                    outline: transparent solid 2px;
                                    outline-offset: 2px;
                                    width: 100%;
                                    min-width: 0px;
                                    padding: 0.75rem;
                                    border-width: 2px;
                                    border-radius: 0.25rem;
                                    font-size: 0.875rem;
                                    line-height: 1.25rem;
                                    transition-property: all;
                                    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                                    transition-duration: 150ms;
                                    --tw-bg-opacity: 1;
                                    background-color: hsla(209, 14%, 37%, var(--tw-bg-opacity));
                                    --tw-border-opacity: 1;
                                    border-color: hsla(211, 12%, 43%, var(--tw-border-opacity));
                                    --tw-text-opacity: 1;
                                    color: hsla(210, 16%, 82%, var(--tw-text-opacity));
                                    --tw-shadow: 0 0 #0000;
                                    box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
                                }

                                .TEXT_CONTENT {
                                    text-transform: uppercase;
                                    font-size: 0.75rem;
                                    line-height: 1rem;
                                    margin-top: 0.25rem;
                                    --tw-text-opacity: 1;
                                    color: hsla(211, 10%, 53%, var(--tw-text-opacity));
                                    display: block;
                                    padding-left: 0.25rem;
                                    padding-right: 0.25rem;
                                    user-select: none;
                                    transition-property: background-color, border-color, color, fill, stroke;
                                    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                                    transition-duration: 150ms;
                                }
                                </style>
                                <div class="GreyRowBox-sc-1xo9c6v-0 BASE_BOX flex-wrap md:flex-nowrap mt-2">
                                    <div class="flex items-center w-full md:w-auto">
                                        <div class="pl-4 pr-6 text-neutral-400">
                                            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="network-wired" class="svg-inline--fa fa-network-wired fa-w-20 " role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M640 264v-16c0-8.84-7.16-16-16-16H344v-40h72c17.67 0 32-14.33 32-32V32c0-17.67-14.33-32-32-32H224c-17.67 0-32 14.33-32 32v128c0 17.67 14.33 32 32 32h72v40H16c-8.84 0-16 7.16-16 16v16c0 8.84 7.16 16 16 16h104v40H64c-17.67 0-32 14.33-32 32v128c0 17.67 14.33 32 32 32h160c17.67 0 32-14.33 32-32V352c0-17.67-14.33-32-32-32h-56v-40h304v40h-56c-17.67 0-32 14.33-32 32v128c0 17.67 14.33 32 32 32h160c17.67 0 32-14.33 32-32V352c0-17.67-14.33-32-32-32h-56v-40h104c8.84 0 16-7.16 16-16zM256 128V64h128v64H256zm-64 320H96v-64h96v64zm352 0h-96v-64h96v64z"></path></svg>
                                        </div>
                                        <div class="mr-4 flex-1 md:w-40">
                                            <code class="font-mono text-sm px-2 py-1 inline-block rounded w-40 truncate cursor-pointer bg-neutral-900 text-gray-100">root.node.webworkshub.online</code>
                                            <label class="AllocationRow__Label-sc-yy3pdt-0 TEXT_CONTENT">Hostname</label>
                                        </div>
                                        <div class="w-16 md:w-24 overflow-hidden"><code class="font-mono text-sm px-2 py-1 inline-block rounded bg-neutral-900 text-gray-100">1026</code>
                                            <label class="AllocationRow__Label-sc-yy3pdt-0 TEXT_CONTENT">Port</label>
                                        </div>
                                    </div>
                                    <div class="mt-4 w-full md:mt-0 md:flex-1 md:w-auto">
                                        <div class="InputSpinner__Container-sc-1ynug7t-0 loYywF"><div class="Fade__Container-sc-1p0gm8n-0 hcgQjy"></div>
                                            <textarea class="Input__Textarea-sc-19rce1w-1 NOTES_BOX bg-neutral-800 hover:border-neutral-600 border-transparent" placeholder="Notes"></textarea>
                                        </div>
                                    </div>
                                    <div class="flex justify-end space-x-4 mt-4 w-full md:mt-0 md:w-48">
                                        <button class="style-module_4LBM1DKx style-module_3kBDV_wo style-module_2UCZLAAp !text-gray-50 !bg-blue-600" disabled="">Primary</button>
                                    </div>
                                </div>
                                `;
                            });

                            currentTabElement = newTab;

                            // Remove old listners
                            document.removeEventListener('tabChanged', tabChangeHandler);

                            // Add new listners
                            document.addEventListener('tabChanged', tabChangeHandler);

                            // Inser the new tab
                            navBar.insertBefore(newTab, closeSibling);
                        }
                    }
                } else{
                    serverInfomation = false;
                }
            });
            var currentTab;

            function tabChangeHandler(event) {
                const tabName = event.detail.tab_name;

                if(tabName != 'Subdomains'){
                    currentTabElement.classList.remove('active');
                }

                if(currentTab && currentTab == tabName){
                    if(window.location.pathname.replace(/^\/+|\/+$/g, '').split('/').filter(part => part !== '').length != 2 && tabName != 'Console'){
                        window.location.href = tabName.toLowerCase();
                    } else if(tabName == 'Console'){
                        window.location.href = location.href.slice(0, location.href.lastIndexOf('/') + 1);
                    }
                }

                // Only update if not subdomains tab
                if(tabName != 'Subdomains'){
                    currentTab = tabName;
                }
            }
        </script>
        <?php
    }
}
