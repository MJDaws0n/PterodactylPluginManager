<?php
// Half confirms this is not an API page as we want to slow that down as little as possible
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    // Check what page we are on
    $currentUri = $_SERVER['REQUEST_URI'];
    if(explode('/', $currentUri)[1] == 'server' 
    || explode('/', $currentUri)[1] == ''
    || explode('/', $currentUri)[1] == 'account'  ){ // Confirm that we are on the page we should be
        // Check that we are on the server listing pages
        ?>
        <script>
            // The JS goes here
            var currentUri = window.location.pathname;
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
                    }

                    // Check if we already have the tab
                    if(!document.querySelector(themeSettings.server_tabs+'#subdomains_tab') && document.querySelector(themeSettings.server_tabs)){
                        // Create a new one
                        const suddomainsTab = document.createElement(themeSettings.server_tabs_type);
                        suddomainsTab.setAttribute('id', 'subdomains_tab');
                        suddomainsTab.setAttribute('href', `/server/${serverInfomation.attributes.identifier}/subdomains`);
                        suddomainsTab.textContent = 'Subdomains';
                        suddomainsTab.addEventListener('click', (e)=>{
                            e.preventDefault();

                            // Change the tab to the subdomains page
                            const currentPage = document.querySelector(themeSettings.server_content);
                            const currentPageParent = currentPage.parentElement;
                            var trackType;
                            var trackElement;

                            // Set it to active
                            suddomainsTab.classList.add(themeSettings.server_tabs_active_class);

                            // Set the URL
                            history.pushState({},'',window.location.origin+'/server/'+serverInfomation.attributes.identifier+'/subdomains');


                            // Check to see if when the element is re-added, it's important to keep in mind order
                            if(currentPageParent.children.length > 1){
                                // keep in trac previous sibling
                                if(currentPage.previousSibling){
                                    trackType = 1
                                    trackElement = currentPage.previousSibling;
                                }
                                
                                // We don't use elseif as it is easy to keep track of nextSibling than it is previous
                                if(currentPage.nextSibling){
                                    trackType = 2
                                    trackElement = currentPage.nextSibling;
                                }
                            }

                            // Remove old page content
                            currentPage.remove();

                            // Add the new page content
                            const networkSectionElement = networkSection();
                            document.head.append(networkSectionElement[0]);

                            if(trackType == 1){
                                insertAfter(networkSectionElement[1], trackElement);
                            }

                            if(trackType == 2){
                                currentPageParent.insertBefore(networkSectionElement[1], trackElement);
                            }

                            // Keep a record of the actual tab element
                            const currentTab = document.querySelector(themeSettings.server_tabs+'.'+themeSettings.server_tabs_active_class);
                            currentTab.classList.remove(themeSettings.server_tabs_active_class);

                            document.addEventListener('tabChanged', removetabListner)
                            function removetabListner(e){
                                if(e.detail.tab_name != 'Subdomains' && e.detail.tab_name != currentTab.textContent){
                                    suddomainsTab.classList.remove(themeSettings.server_tabs_active_class);
                                    currentTab.removeEventListener('click', enterTab);
                                    document.removeEventListener('tabChanged', removetabListner);

                                    // Remove old page content
                                    networkSectionElement[0].remove();
                                    networkSectionElement[1].remove();
                                }
                            }

                            // Keep a record if we re-enter that tab we just left
                            currentTab.addEventListener('click', enterTab);

                            function enterTab(e){
                                e.preventDefault();
                                e.stopPropagation();

                                currentTab.classList.add(themeSettings.server_tabs_active_class);

                                if(trackType == 1){
                                    insertAfter(currentPage, trackElement);
                                }

                                if(trackType == 2){
                                    currentPageParent.insertBefore(currentPage, trackElement);
                                }

                                // Set the url back to what it should be
                                history.pushState({},'',currentTab.href);
                                
                                // Remove the active part
                                suddomainsTab.classList.remove(themeSettings.server_tabs_active_class);

                                // Remove it as a listener
                                currentTab.removeEventListener('click', enterTab);

                                // Remove old page content
                                networkSectionElement[0].remove();
                                networkSectionElement[1].remove();
                            }
                        })

                        document.querySelector(themeSettings.server_tabs).parentElement.insertBefore(suddomainsTab, document.querySelector(themeSettings.server_tabs)
                        .nextSibling
                        .nextSibling
                        .nextSibling
                        .nextSibling
                        .nextSibling
                        .nextSibling
                        .nextSibling);
                    }
                } else{
                    serverInfomation = false;
                }
            });

            function networkSection(){
                const style = document.createElement('style');
                style.innerHTML = `
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
                .BASE_CONTAINER {
                    margin-top: 1rem;
                    margin-bottom: 1rem;

                    max-width: 1200px;
                    margin-left: 1rem;
                    margin-right: 1rem;
                }
                @media (min-width: 640px) {
                    .BASE_CONTAINER {
                        margin-top: 2.5rem;
                        margin-bottom: 2.5rem;
                    }
                }
                .NOTES_CONTAINER{
                    positon: relative;
                }
                `;
                const container = document.createElement('div');
                container.setAttribute('class', 'ContentContainer-sc- PageContentBlock___StyledContentContainer-sc- fade-appear-done fade-enter-done BASE_CONTAINER');
                
                // For testing we only add one network box
                const subdomainBox = createSubdomainBox();
                container.append(subdomainBox);
                
                
                return [style, container];
            }
            function createSubdomainBox(){
                const subdomainBox = document.createElement('div');
                subdomainBox.setAttribute('class', 'GreyRowBox-sc- BASE_BOX flex-wrap md:flex-nowrap mt-2');
                
                subdomainBox.innerHTML = `
                    <div class="flex items-center w-full md:w-auto">
                        <div class="pl-4 pr-6 text-neutral-400">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="network-wired" class="svg-inline--fa fa-network-wired fa-w-20 " role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M640 264v-16c0-8.84-7.16-16-16-16H344v-40h72c17.67 0 32-14.33 32-32V32c0-17.67-14.33-32-32-32H224c-17.67 0-32 14.33-32 32v128c0 17.67 14.33 32 32 32h72v40H16c-8.84 0-16 7.16-16 16v16c0 8.84 7.16 16 16 16h104v40H64c-17.67 0-32 14.33-32 32v128c0 17.67 14.33 32 32 32h160c17.67 0 32-14.33 32-32V352c0-17.67-14.33-32-32-32h-56v-40h304v40h-56c-17.67 0-32 14.33-32 32v128c0 17.67 14.33 32 32 32h160c17.67 0 32-14.33 32-32V352c0-17.67-14.33-32-32-32h-56v-40h104c8.84 0 16-7.16 16-16zM256 128V64h128v64H256zm-64 320H96v-64h96v64zm352 0h-96v-64h96v64z"></path></svg>
                        </div>
                        <div class="mr-4 flex-1 md:w-40">
                            <code class="font-mono text-sm px-2 py-1 inline-block rounded w-40 truncate cursor-pointer bg-neutral-900 text-gray-100">root.node.webworkshub.online</code>
                            <label class="AllocationRow__Label-sc- TEXT_CONTENT">Hostname</label>
                        </div>
                        <div class="w-16 md:w-24 overflow-hidden"><code class="font-mono text-sm px-2 py-1 inline-block rounded bg-neutral-900 text-gray-100">1026</code>
                            <label class="AllocationRow__Label-sc- TEXT_CONTENT">Port</label>
                        </div>
                    </div>
                    <div class="mt-4 w-full md:mt-0 md:flex-1 md:w-auto">
                        <div class="InputSpinner__Container-sc- NOTES_CONTAINER">
                            <textarea class="Input__Textarea-sc- NOTES_BOX bg-neutral-800 hover:border-neutral-600 border-transparent" placeholder="Notes"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 mt-4 w-full md:mt-0 md:w-48">
                        <button class="style-module_4LBM1DKx style-module_3kBDV_wo style-module_2UCZLAAp !text-gray-50 !bg-blue-600" disabled="">Primary</button>
                    </div>
                `;

                return subdomainBox;
            }

            // Insert after function
            function insertAfter(newNode, referenceNode) {
                referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
            }
        </script>
        <?php
    }
}