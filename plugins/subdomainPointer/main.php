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

<<<<<<< HEAD
            // Get a list of domains that we can use
            var domainList = [];
            fetch('https://panel.webworkshub.online/pluginapi/subdomainPointer/getDomains/')
            .then(response => {
                return response.json();
            })
            .then(data => {
                if(data.success && data.success == 'true'){
                    domainList = data.results;
                } else{
                    alert('An error occoured when fetching the domains.');
                }
            })


            // Add sweat alerts:
            if(!document.querySelector('script[src="https://cdn.jsdelivr.net/npm/sweetalert2@11"]')){
                const sweetalert = document.createElement('script');
                sweetalert.src = "https://cdn.jsdelivr.net/npm/sweetalert2@11";

                document.head.append(sweetalert);
            }

=======
>>>>>>> 6e8607a5f7b7c0e7b9caedb44b6f1efb98961dd4
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

                    // Check for 404 on the subdomains page
                    if
                    (
                        document.querySelector('body #app div[class*="Fade__Container-sc-"] > section:nth-child(1) > div[class*="ContentContainer-sc-"][class*="PageContentBlock___StyledContentContainer-sc-"] div[class*="ScreenBlock___StyledDiv-sc-"] div[class*="ScreenBlock___StyledDiv2-sc-"] > h2:nth-child(2)') &&
                        document.querySelector('body #app div[class*="Fade__Container-sc-"] > section:nth-child(1) > div[class*="ContentContainer-sc-"][class*="PageContentBlock___StyledContentContainer-sc-"] div[class*="ScreenBlock___StyledDiv-sc-"] div[class*="ScreenBlock___StyledDiv2-sc-"] > h2:nth-child(2)').textContent == "404" &&
                        currentUri.startsWith("/server/") && 
                        currentUri.includes('/subdomains') &&
                        document.querySelector(themeSettings.server_tabs+'#subdomains_tab') &&
                        document.querySelector(themeSettings.server_tabs)
                    ){
                        document.querySelector('body #app div[class*="Fade__Container-sc-"] > section:nth-child(1) > div[class*="ContentContainer-sc-"][class*="PageContentBlock___StyledContentContainer-sc-"] div[class*="ScreenBlock___StyledDiv-sc-"]').remove();
                        document.querySelector(themeSettings.server_tabs+'#subdomains_tab').click();
                    }

                    // Check if we already have the tab
                    if(!document.querySelector(themeSettings.server_tabs+'#subdomains_tab') && document.querySelector(themeSettings.server_tabs)){
                        // Create a new one
                        const subdomainsTab = document.createElement(themeSettings.server_tabs_type);
                        subdomainsTab.setAttribute('id', 'subdomains_tab');
                        subdomainsTab.setAttribute('href', `/server/${serverInfomation.attributes.identifier}/subdomains`);
                        subdomainsTab.textContent = 'Subdomains';

                        var currentlyInTab = false;
                        subdomainsTab.addEventListener('click', (e)=>{
                            e.preventDefault();
                            if(!currentlyInTab){
                                currentlyInTab = true;

                                // Change the tab to the subdomains page
                                const currentPage = document.querySelector(themeSettings.server_content);
                                const currentPageParent = currentPage.parentElement;
                                var trackType;
                                var trackElement;

                                // Set it to active
                                subdomainsTab.classList.add(themeSettings.server_tabs_active_class);

                                // Set the URL
                                history.pushState({},'',window.location.origin+'/server/'+serverInfomation.attributes.identifier+'/subdomains');


                                // Check to see if when the element is re-added, it's important to keep in mind order
                                if(currentPageParent.children.length > 1){
                                    // keep in trac previous sibling
                                    if(currentPage.previousSibling){
                                        trackType = 1;
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
                                if(document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]')){
                                    document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]').style['width'] = '10%';
                                } else {
                                    const progressBar = document.createElement('div');
                                    progressBar.setAttribute('class', 'ProgressBar__BarFill-sc- PROGRESS_BAR');
                                    progressBar.style['width'] = '10%';

                                    document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"]').append(progressBar);
                                }

                                networkSection((networkSectionElement)=>{
                                    if(document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]')){
                                        document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]').style['width'] = '100%';
                                        setTimeout(function(){
                                            if(document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]')){
                                                document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]').remove();
                                            }
                                        }, 700);
                                    }

                                    document.head.append(networkSectionElement[0]);

                                    if(trackType == 1){
                                        insertAfter(networkSectionElement[1], trackElement);
                                    }

                                    if(trackType == 2){
                                        currentPageParent.insertBefore(networkSectionElement[1], trackElement);
                                    }

                                    // Keep a record of the actual tab element
                                    const activeTabs = document.querySelectorAll(themeSettings.server_tabs+'.'+themeSettings.server_tabs_active_class)
                                    var currentTab = activeTabs[0];
                                    
                                    // This is required if you are picking a tab after the new tab
                                    if(currentTab == subdomainsTab){
                                        currentTab = activeTabs[1];
                                    }
                                    if(!currentTab){
                                        return;
                                    }
                                    currentTab.classList.remove(themeSettings.server_tabs_active_class);
                                    
                                    document.addEventListener('tabChanged', removetabListner)
                                    function removetabListner(e){
                                        if(e.detail.tab_name != 'Subdomains' && e.detail.tab_name != currentTab.textContent){
                                            document.querySelector(themeSettings.server_tab_whole_content).style.opacity = 0;

                                            subdomainsTab.classList.remove(themeSettings.server_tabs_active_class);
                                            currentTab.removeEventListener('click', enterTab);
                                            document.removeEventListener('tabChanged', removetabListner);

                                            // Remove old page content
                                            networkSectionElement[0].remove();
                                            networkSectionElement[1].remove();

                                            // Re-enable clicking on the tab
                                            currentlyInTab = false;
                                        }
                                    }

                                    // Keep a record if we re-enter that tab we just left
                                    currentTab.addEventListener('click', enterTab);

                                    function enterTab(e){
                                        e.preventDefault();
                                        e.stopPropagation();

                                        document.querySelector(themeSettings.server_tab_whole_content).style.opacity = 0;

                                        currentTab.classList.add(themeSettings.server_tabs_active_class);

                                        if(trackType == 1){
                                            insertAfter(currentPage, trackElement);
                                            document.querySelector(themeSettings.server_tab_whole_content).style['transition'] = 'opacity 0.2s ease';
                                            document.querySelector(themeSettings.server_tab_whole_content).style.opacity = 1;
                                            document.querySelector(themeSettings.server_tab_whole_content).style['transition'] = '';
                                        }

                                        if(trackType == 2){
                                            currentPageParent.insertBefore(currentPage, trackElement);
                                            document.querySelector(themeSettings.server_tab_whole_content).style['transition'] = 'opacity 0.2s ease';
                                            document.querySelector(themeSettings.server_tab_whole_content).style.opacity = 1;
                                            document.querySelector(themeSettings.server_tab_whole_content).style['transition'] = '';
                                        }

                                        // Set the url back to what it should be
                                        history.pushState({},'',currentTab.href);
                                        
                                        // Remove the active part
                                        subdomainsTab.classList.remove(themeSettings.server_tabs_active_class);

                                        // Remove it as a listener
                                        currentTab.removeEventListener('click', enterTab);

                                        // Remove old page content
                                        networkSectionElement[0].remove();
                                        networkSectionElement[1].remove();

                                        // Re-enable clicking on the tab
                                        currentlyInTab = false;
                                    }
                                });
                            }
                        })

                        document.querySelector(themeSettings.server_tabs).parentElement.insertBefore(subdomainsTab, document.querySelector(themeSettings.server_tabs)
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

            function networkSection(callback){
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
                @media screen and (min-width: 80em) {
                    .BASE_CONTAINER {
                        margin-left: auto;
                        margin-right: auto;
                    }
                }
                .NOTES_CONTAINER{
                    positon: relative;
                }
                .NOTES_BOX:focus {
                    --tw-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
                    box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
                    --tw-border-opacity: 1;
                    border-color: rgba(147, 197, 253, var(--tw-border-opacity));
                    --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
                    --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
                    --tw-ring-opacity: 0.5;
                    --tw-ring-color: rgba(96,165,250,var(--tw-ring-opacity));
                }
                .PROGRESS_BAR {
                    height: 100%;
                    --tw-bg-opacity: 1;
                    background-color: rgba(34, 211, 238, var(--tw-bg-opacity));
                    transition: all 250ms ease-in-out 0s;
                    box-shadow: rgb(60, 231, 225) 0px -2px 10px 2px;
                }
                .ADD_SECTION_CONTAINER{
                    margin-top: 1.5rem;
                    -webkit-box-align: center;
                    align-items: center;
                    -webkit-box-pack: end;
                    justify-content: flex-end;
                }
                @media (min-width: 640px) {
                    .ADD_SECTION_CONTAINER {
                        display: flex;
                    }
                }
                .ADD_SECTION_TEXT {
                    font-size: 0.875rem;
                    line-height: 1.25rem;
                    --tw-text-opacity: 1;
                    color: hsla(211, 13%, 65%, var(--tw-text-opacity));
                    margin-bottom: 1rem;
                }
                @media (min-width: 640px) {
                    .ADD_SECTION_TEXT {
                        margin-right: 1.5rem;
                        margin-bottom: 0px;
                    }
                }
                .ADD_SECTION_BUTTON {
                    width: 100%;
                    position: relative;
                    display: inline-block;
                    border-radius: 0.25rem;
                    text-transform: uppercase;
                    letter-spacing: 0.025em;
                    font-size: 0.875rem;
                    line-height: 1.25rem;
                    transition-property: all;
                    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                    transition-duration: 150ms;
                    --tw-bg-opacity: 1;
                    background-color: rgba(59, 130, 246, var(--tw-bg-opacity));
                    --tw-border-opacity: 1;
                    border-color: rgba(37, 99, 235, var(--tw-border-opacity));
                    border-width: 1px;
                    --tw-text-opacity: 1;
                    color: rgba(239, 246, 255, var(--tw-text-opacity));
                    padding: 0.5rem 1rem;
                    cursor: pointer;
                }
                @media (min-width: 640px) {
                    .ADD_SECTION_BUTTON {
                        width: auto;
                    }
                }
                `;
                const container = document.createElement('div');
                container.setAttribute('class', 'ContentContainer-sc- PageContentBlock___StyledContentContainer-sc- fade-appear-done fade-enter-done BASE_CONTAINER');

                document.querySelector(themeSettings.server_tab_whole_content).style.opacity = 0;
                
                // For testing we only add one network box
                createSubdomainBox((subdomainBoxArray)=>{
                    subdomainBoxArray.forEach(subdomainBoxHTML => {
                        const subdomainBox = document.createElement('div');
                        subdomainBox.setAttribute('class', 'GreyRowBox-sc- BASE_BOX flex-wrap md:flex-nowrap mt-2');

                        subdomainBox.innerHTML = subdomainBoxHTML;
                        container.append(subdomainBox);
                    });

                    // Create the "New" button
                    const addSection = document.createElement('div');
                    addSection.setAttribute('class', 'NetworkContainer___StyledDiv-sc- ADD_SECTION_CONTAINER');

                    const addSectionText = document.createElement('p');
                    addSectionText.setAttribute('class', 'NetworkContainer___StyledP-sc- ADD_SECTION_TEXT');
                    addSectionText.textContent = 'You are currently using 1 of 2 allowed subdomains for this server.';

                    addSection.append(addSectionText);

                    const addSectionButton = document.createElement('p');
                    addSectionButton.setAttribute('class', 'Button__ButtonStyle-sc- NetworkContainer___StyledButton-sc- ADD_SECTION_BUTTON');
                    addSectionButton.setAttribute('color', 'primary');
                    addSectionButton.setAttribute('style', 'text-align: center;');
                    addSectionButton.innerHTML = '<span class="Button___StyledSpan-sc-">Create Subdomain</span>';
                    addSectionButton.addEventListener('click', createSubdomain);

                    addSection.append(addSectionButton);

                    container.append(addSection);

                    document.querySelector(themeSettings.server_tab_whole_content).style['transition'] = 'opacity 0.2s ease';
                    document.querySelector(themeSettings.server_tab_whole_content).style.opacity = 1;
                    document.querySelector(themeSettings.server_tab_whole_content).style['transition'] = '';
                    callback([style, container])
                });
            }
            function createSubdomainBox(callback){                
                fetch(('https://panel.webworkshub.online/pluginapi/subdomainPointer/getsubdomains/?server='+serverInfomation.attributes.uuid))
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    if(data.success){
                        const subdomainBoxArray = [];

                        data.response.forEach(config => {
                            subdomainBoxArray.push(`
                            <div class="flex items-center w-full md:w-auto">
                                <div class="pl-4 pr-6 text-neutral-400">
                                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="network-wired" class="svg-inline--fa fa-network-wired fa-w-20 " role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M640 264v-16c0-8.84-7.16-16-16-16H344v-40h72c17.67 0 32-14.33 32-32V32c0-17.67-14.33-32-32-32H224c-17.67 0-32 14.33-32 32v128c0 17.67 14.33 32 32 32h72v40H16c-8.84 0-16 7.16-16 16v16c0 8.84 7.16 16 16 16h104v40H64c-17.67 0-32 14.33-32 32v128c0 17.67 14.33 32 32 32h160c17.67 0 32-14.33 32-32V352c0-17.67-14.33-32-32-32h-56v-40h304v40h-56c-17.67 0-32 14.33-32 32v128c0 17.67 14.33 32 32 32h160c17.67 0 32-14.33 32-32V352c0-17.67-14.33-32-32-32h-56v-40h104c8.84 0 16-7.16 16-16zM256 128V64h128v64H256zm-64 320H96v-64h96v64zm352 0h-96v-64h96v64z"></path></svg>
                                </div>
                                <div class="mr-4 flex-1 md:w-40">
                                    <code class="font-mono text-sm px-2 py-1 inline-block rounded w-40 truncate cursor-pointer bg-neutral-900 text-gray-100">${config.name}</code>
                                    <label class="AllocationRow__Label-sc- TEXT_CONTENT">Domain</label>
                                </div>
                                <div class="w-16 md:w-24 overflow-hidden"><code class="font-mono text-sm px-2 py-1 inline-block rounded bg-neutral-900 text-gray-100">${config.proxied}</code>
                                    <label class="AllocationRow__Label-sc- TEXT_CONTENT">Proxied</label>
                                </div>
                            </div>
                            <div class="mt-4 w-full md:mt-0 md:flex-1 md:w-auto">
                                <div class="InputSpinner__Container-sc- NOTES_CONTAINER">
                                    <textarea class="Input__Textarea-sc- NOTES_BOX bg-neutral-800 hover:border-neutral-600 border-transparent" oninput="updateSubdomainsNotes(event)" placeholder="Notes">${config.notes}</textarea>
                                </div>
                            </div>
                            <div class="flex justify-end space-x-4 mt-4 w-full md:mt-0 md:w-48">
                                <button type="button" aria-label="Edit subdomain"  onclick="editSubdomain(event);">
                                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="pencil-alt" class="svg-inline--fa fa-pencil-alt fa-w-16 " role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                                </button>
                                <button type="button" aria-label="Delete subdomain" onclick="deleteSubdomain(event);">
                                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="trash-alt" class="svg-inline--fa fa-trash-alt fa-w-14 " role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path></svg>
                                </button>
                            </div>
                            `);
                        });

                        callback(subdomainBoxArray);
                    }
                })
            }
            
            var updateSubdomainsNotesTimout;
            // Function for updating the notes
            function updateSubdomainsNotes(e){
                if(updateSubdomainsNotesTimout){
                    clearTimeout(updateSubdomainsNotesTimout);
                }
                updateSubdomainsNotesTimout = setTimeout(() => {
                    const data = {
                        "domains": 
                            [
                                {
                                    "name": e.target.parentElement.parentElement.parentElement.firstElementChild.children[1].firstElementChild.textContent,
                                    "server": serverInfomation.attributes.uuid,
                                    "proxied": e.target.parentElement.parentElement.parentElement.firstElementChild.children[2].firstElementChild.textContent == 'true',
                                    "notes": e.target.parentElement.parentElement.parentElement.children[1].firstElementChild.firstElementChild.value
                                }
                            ]
                    };
                    
                    fetch(('https://panel.webworkshub.online/pluginapi/subdomainPointer/updateConfig/?server='+serverInfomation.attributes.uuid+'&config='+encodeURIComponent(JSON.stringify(data)))).then(response => {
                        if(document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]')){
                            document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]').style['width'] = '100%';
                            setTimeout(function(){
                                if(document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]')){
                                    document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]').remove();
                                }
                            }, 300);
                        }
                    });
                    if(document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]')){
                        document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]').style['width'] = '10%';
                    } else {
                        const progressBar = document.createElement('div');
                        progressBar.setAttribute('class', 'ProgressBar__BarFill-sc- PROGRESS_BAR');
                        progressBar.style['width'] = '10%';

                        document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"]').append(progressBar);
                    }
                }, 700);
            }

            // Function for remove the subdomain
            function deleteSubdomain(e){
                var baseElement = event.target;
                const maxSearch = 100;

                var loop = 0;
                while(!baseElement.classList.contains('BASE_BOX') && loop < maxSearch){
                    baseElement = baseElement.parentElement;
                    loop++;
                }

                fetch(('https://panel.webworkshub.online/pluginapi/subdomainPointer/remove/?server='+serverInfomation.attributes.uuid+'&name='+encodeURIComponent(baseElement.firstElementChild.children[1].firstElementChild.textContent)))
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    if(data.success == 'true'){
                        baseElement.remove();
                    } else{
                        alert('Error: '+data.error);
                    }
                    if(document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]')){
                        document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]').style['width'] = '100%';
                        setTimeout(function(){
                            if(document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]')){
                                document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]').remove();
                            }
                        }, 300);
                    }
                });
                if(document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]')){
                    document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]').style['width'] = '10%';
                } else {
                    const progressBar = document.createElement('div');
                    progressBar.setAttribute('class', 'ProgressBar__BarFill-sc- PROGRESS_BAR');
                    progressBar.style['width'] = '10%';

                    document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"]').append(progressBar);
                }
            }

            // Function for edit the subdomain
            function editSubdomain(e){
                var baseElement = event.target;
                const maxSearch = 100;

                var loop = 0;
                while(!baseElement.classList.contains('BASE_BOX') && loop < maxSearch){
                    baseElement = baseElement.parentElement;
                    loop++;
                }
                const data = {
                    "domains": 
                        [
                            {
                                "name": baseElement.firstElementChild.children[1].firstElementChild.textContent,
                                "server": serverInfomation.attributes.uuid,
                                "proxied": baseElement.firstElementChild.children[2].firstElementChild.textContent == 'true',
                                "notes": baseElement.children[1].firstElementChild.firstElementChild.value
                            }
                        ]
                };

                if(!document.querySelector('script[src="https://cdn.jsdelivr.net/npm/sweetalert2@11"]')){
                    const sweetalert = document.createElement('script');
                    sweetalert.src = "https://cdn.jsdelivr.net/npm/sweetalert2@11";

                    document.head.append(sweetalert);
                    setTimeout(()=>{
                        showPopup();
<<<<<<< HEAD
                    }, 100)
=======
                    }, 10)
>>>>>>> 6e8607a5f7b7c0e7b9caedb44b6f1efb98961dd4
                } else{
                    showPopup();
                }

                function showPopup(){
                    var checked = '';
                    if(data.domains[0].proxied){
                        checked = 'checked'
                    }
                    Swal.fire({
                    title: 'Edit details for '+data.domains[0].name,
                    html:
                        `
                        <div class="EditScheduleModal___StyledDiv4-sc- MAIN_SELECT">
                            <div>
                                <div class="Switch___StyledDiv-sc- MAIN_SWICH">
                                <div class="Switch__ToggleContainer-sc- Switch___StyledToggleContainer-sc- CUSTOM_7 CUSTOM_1">
                                    <input id="CUSTOM_SWICH" name="isProxied" type="checkbox" class="Input-sc- CUSTOM_6" ${checked}>
                                    <label for="CUSTOM_SWICH" class="Label-sc- CUSTOM_4"></label>
                                </div>
                                <div class="Switch___StyledDiv2-sc- CUSTOM_2">
                                    <label for="CUSTOM_SWICH" class="Label-sc- Switch___StyledLabel-sc- CUSTOM_4 CUSTOM_3">Run Proxied</label>
                                    <p class="Switch___StyledP-sc- CUSTOM_5">Run Subdomain through an NGINX reverse proxy. This is usually good if you are wanting to link as a website. (Additional steps required.)</p>
                                </div>
                                </div>
                            </div>
                        </div>
                        <style>
                            .MAIN_CONTAINER{
                                --tw-bg-opacity: 1;
                                background-color: hsla(209, 20%, 25%, var(--tw-bg-opacity));
                                padding: 0.75rem;
                                border-radius: 0.25rem;
                                --tw-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
                                box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
                                overflow-y: scroll;
                                transition-property: all;
                                transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                                transition-duration: 150ms;
                            }
                            .MAIN_SELECT{
                                margin-top: 1.5rem;
                                --tw-bg-opacity: 1;
                                background-color: hsla(209, 18%, 30%, var(--tw-bg-opacity));
                                border-width: 1px;
                                --tw-border-opacity: 1;
                                border-color: hsla(209, 20%, 25%, var(--tw-border-opacity));
                                --tw-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.06);
                                box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
                                padding: 1rem;
                                border-radius: 0.25rem;
                            }
                            .MAIN_SWICH{
                                display: flex;
                                -webkit-box-align: center;
                                align-items: center;
                            }
                            .CUSTOM_1 {
                                flex: 0 0 auto;
                            }
                            .CUSTOM_2 {
                                margin-left: 1rem;
                                width: 100%;
                            }
                            .CUSTOM_3 {
                                cursor: pointer;
                                margin-bottom: 0px;
                            }
                            .CUSTOM_4 {
                                display: block;
                                font-size: 0.75rem;
                                line-height: 1rem;
                                text-transform: uppercase;
                                --tw-text-opacity: 1;
                                color: hsla(210, 16%, 82%, var(--tw-text-opacity));
                                margin-bottom: 0.25rem;
                                padding-right: 3rem;
                            }
                            .CUSTOM_5 {
                                --tw-text-opacity: 1;
                                color: hsla(211, 10%, 53%, var(--tw-text-opacity));
                                font-size: 0.875rem;
                                line-height: 1.25rem;
                                margin-top: 0.5rem;
                            }
                            .CUSTOM_6[type="checkbox"]:checked, .CUSTOM_6[type="radio"]:checked {
                                border-color: rgba(0, 0, 0, 0);
                                background-repeat: no-repeat;
                                background-position: center center;
                                background-image: url(data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M5.707 7.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l4-4a1 1 0 0 0-1.414-1.414L7 8.586 5.707 7.293z'/%3e%3c/svg%3e);
                                background-color: currentcolor;
                                background-size: 100% 100%;
                            }
                            .CUSTOM_7 > input[type="checkbox"] {
                                display: none;
                            }
                            .CUSTOM_6[type="checkbox"], .CUSTOM_6[type="radio"] {
                                --tw-bg-opacity: 1;
                                background-color: hsla(211, 12%, 43%, var(--tw-bg-opacity));
                                cursor: pointer;
                                appearance: none;
                                display: inline-block;
                                vertical-align: middle;
                                user-select: none;
                                flex-shrink: 0;
                                width: 1rem;
                                height: 1rem;
                                --tw-text-opacity: 1;
                                color: rgba(96, 165, 250, var(--tw-text-opacity));
                                border-width: 1px;
                                --tw-border-opacity: 1;
                                border-color: hsla(211, 13%, 65%, var(--tw-border-opacity));
                                border-radius: 0.125rem;
                                background-origin: border-box;
                                transition: all 75ms linear 0s, box-shadow 25ms linear 0s;
                            }
                            .CUSTOM_7 > input[type="checkbox"]:checked + label {
                                --tw-bg-opacity: 1;
                                background-color: rgba(59, 130, 246, var(--tw-bg-opacity));
                                --tw-border-opacity: 1;
                                border-color: rgba(29, 78, 216, var(--tw-border-opacity));
                                --tw-shadow: 0 0 #0000;
                                box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
                            }
                            .CUSTOM_7 > label {
                                margin-bottom: 0px;
                                display: block;
                                overflow: hidden;
                                cursor: pointer;
                                --tw-bg-opacity: 1;
                                background-color: hsla(211, 10%, 53%, var(--tw-bg-opacity));
                                border-width: 1px;
                                --tw-border-opacity: 1;
                                border-color: hsla(209, 18%, 30%, var(--tw-border-opacity));
                                border-radius: 9999px;
                                height: 1.5rem;
                                --tw-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.06);
                                box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
                                transition: all 75ms linear 0s;
                            }
                            .CUSTOM_4 {
                                display: block;
                                font-size: 0.75rem;
                                line-height: 1rem;
                                text-transform: uppercase;
                                --tw-text-opacity: 1;
                                color: hsla(210, 16%, 82%, var(--tw-text-opacity));
                                margin-bottom: 0.25rem;
                            }
                            .CUSTOM_7 {
                                position: relative;
                                user-select: none;
                                width: 3rem;
                                line-height: 1.5;
                            }
                            .CUSTOM_7 > label::before {
                                position: absolute;
                                display: block;
                                --tw-bg-opacity: 1;
                                background-color: rgba(255, 255, 255, var(--tw-bg-opacity));
                                border-width: 1px;
                                height: 1.25rem;
                                width: 1.25rem;
                                border-radius: 9999px;
                                top: 0.125rem;
                                right: calc(50% + 0.125rem);
                                content: "";
                                transition: all 75ms ease-in 0s;
                            }
                            .CUSTOM_7 > input[type="checkbox"]:checked + label::before {
                                right: 0.125rem;
                            }
                            .TITLE_TEXT{
                                color: white;
                            }
                            div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm.CONTAINER_CONFIRM{
                                background: rgba(37, 99, 235);
                                border: none;
                                outline: none;
                            }
                            div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm.CONTAINER_CONFIRM:focus{
                                background: rgba(37, 99, 235);
                                border: none;
                                outline: none;
                                box-shadow: none;
                            }
                        </style>
                        `,
                    focusConfirm: false,
                    customClass: {
                        popup: 'Modal___StyledDiv2-sc- MAIN_CONTAINER',
                        title: 'TITLE_TEXT',
                        confirmButton: 'CONTAINER_CONFIRM'
                    },
                    confirmButtonText: 'Edit Subdomain',
                        preConfirm: () => {
                            const isProxied = document.querySelector('[name="isProxied"]').checked;
                            data.domains[0].proxied = isProxied;

                            baseElement.firstElementChild.children[2].firstElementChild.textContent = isProxied.toString();

                            fetch(('https://panel.webworkshub.online/pluginapi/subdomainPointer/updateConfig/?server='+serverInfomation.attributes.uuid+'&config='+encodeURIComponent(JSON.stringify(data)))).then(response => {
                            if(document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]')){
                                document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]').style['width'] = '100%';
                                setTimeout(function(){
                                    if(document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]')){
                                        document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]').remove();
                                    }
                                }, 300);
                            }
                        });
                        if(document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]')){
                            document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"] div[class*="ProgressBar__BarFill-sc-"]').style['width'] = '10%';
                        } else {
                            const progressBar = document.createElement('div');
                            progressBar.setAttribute('class', 'ProgressBar__BarFill-sc- PROGRESS_BAR');
                            progressBar.style['width'] = '10%';

                            document.querySelector('div[class*="ProgressBar___StyledDiv-sc-"]').append(progressBar);
                        }
                        }
                    });
                }
            }

            // Function to create a new subdomain
            function createSubdomain(e){
                if(!document.querySelector('script[src="https://cdn.jsdelivr.net/npm/sweetalert2@11"]')){
                    const sweetalert = document.createElement('script');
                    sweetalert.src = "https://cdn.jsdelivr.net/npm/sweetalert2@11";

                    document.head.append(sweetalert);
                    setTimeout(()=>{
                        showPopup();
<<<<<<< HEAD
                    }, 100)
=======
                    }, 10)
>>>>>>> 6e8607a5f7b7c0e7b9caedb44b6f1efb98961dd4
                } else{
                    showPopup();
                }
                function showPopup(){
<<<<<<< HEAD
                    var domainListHTML = '';

                    for (let domain in domainList) {
                        domainListHTML += `<option value="${domainList[domain]}">.${domainList[domain]}</option>`;
                    }
=======
>>>>>>> 6e8607a5f7b7c0e7b9caedb44b6f1efb98961dd4
                    Swal.fire({
                    title: 'Create a new subdomain',
                    html:
                        `
                        <label class="Label-sc- DOMAINS_LABEL">Domain</label>
                        <input type="text" min="3" max="10" class="Input-sc- SUBDOMAIN_INPUT" name="subdomainValue" oninput="validateSubdomainCharacter(event)"></input>
                        <select class="DOMAIN_DROPDOWN" name="domainValue">
<<<<<<< HEAD
                            ${domainListHTML}
=======
                            <option value="mjdawson.net">.mjdawson.net</option>
                            <option value="public.webworkshub.online">.public.webworkshub.online</option>
>>>>>>> 6e8607a5f7b7c0e7b9caedb44b6f1efb98961dd4
                        </select>
                        <div class="EditScheduleModal___StyledDiv4-sc- MAIN_SELECT">
                            <div>
                                <div class="Switch___StyledDiv-sc- MAIN_SWICH">
                                <div class="Switch__ToggleContainer-sc- Switch___StyledToggleContainer-sc- CUSTOM_7 CUSTOM_1">
                                    <input id="CUSTOM_SWICH" name="isProxied" type="checkbox" class="Input-sc- CUSTOM_6">
                                    <label for="CUSTOM_SWICH" class="Label-sc- CUSTOM_4"></label>
                                </div>
                                <div class="Switch___StyledDiv2-sc- CUSTOM_2">
                                    <label for="CUSTOM_SWICH" class="Label-sc- Switch___StyledLabel-sc- CUSTOM_4 CUSTOM_3">Run Proxied</label>
                                    <p class="Switch___StyledP-sc- CUSTOM_5">Run Subdomain through an NGINX reverse proxy. This is usually good if you are wanting to link as a website. (Additional steps required.)</p>
                                </div>
                                </div>
                            </div>
                        </div>
                        <style>
                            @media (min-width: 640px) {
                                .DOMAINS_LABEL {
                                    margin-bottom: 0.5rem;
                                }
                            }
                            .DOMAINS_LABEL {
                                display: block;
                                font-size: 0.75rem;
                                line-height: 1rem;
                                text-transform: uppercase;
                                --tw-text-opacity: 1;
                                color: hsla(210, 16%, 82%, var(--tw-text-opacity));
                                margin-bottom: 0.25rem;
                            }
                            .MAIN_CONTAINER{
                                --tw-bg-opacity: 1;
                                background-color: hsla(209, 20%, 25%, var(--tw-bg-opacity));
                                padding: 0.75rem;
                                border-radius: 0.25rem;
                                --tw-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
                                box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
                                overflow-y: scroll;
                                transition-property: all;
                                transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                                transition-duration: 150ms;

                                width: 900px;
                            }
                            .SUBDOMAIN_INPUT:not([type="checkbox"]):not([type="radio"]) {
                                resize: none;
                                appearance: none;
                                outline: transparent solid 2px;
                                outline-offset: 2px;
                                width: clalc(50% - 1.5);
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
                            .DOMAIN_DROPDOWN:not([type="checkbox"]):not([type="radio"]) {
                                resize: none;
                                appearance: none;
                                outline: transparent solid 2px;
                                outline-offset: 2px;
                                width: clalc(50% - 1.5);
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
                            .MAIN_SELECT{
                                margin-top: 1.5rem;
                                --tw-bg-opacity: 1;
                                background-color: hsla(209, 18%, 30%, var(--tw-bg-opacity));
                                border-width: 1px;
                                --tw-border-opacity: 1;
                                border-color: hsla(209, 20%, 25%, var(--tw-border-opacity));
                                --tw-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.06);
                                box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
                                padding: 1rem;
                                border-radius: 0.25rem;
                            }
                            .MAIN_SWICH{
                                display: flex;
                                -webkit-box-align: center;
                                align-items: center;
                            }
                            .CUSTOM_1 {
                                flex: 0 0 auto;
                            }
                            .CUSTOM_2 {
                                margin-left: 1rem;
                                width: 100%;
                            }
                            .CUSTOM_3 {
                                cursor: pointer;
                                margin-bottom: 0px;
                            }
                            .CUSTOM_4 {
                                display: block;
                                font-size: 0.75rem;
                                line-height: 1rem;
                                text-transform: uppercase;
                                --tw-text-opacity: 1;
                                color: hsla(210, 16%, 82%, var(--tw-text-opacity));
                                margin-bottom: 0.25rem;
                                padding-right: 3rem;
                            }
                            .CUSTOM_5 {
                                --tw-text-opacity: 1;
                                color: hsla(211, 10%, 53%, var(--tw-text-opacity));
                                font-size: 0.875rem;
                                line-height: 1.25rem;
                                margin-top: 0.5rem;
                            }
                            .CUSTOM_6[type="checkbox"]:checked, .CUSTOM_6[type="radio"]:checked {
                                border-color: rgba(0, 0, 0, 0);
                                background-repeat: no-repeat;
                                background-position: center center;
                                background-image: url(data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M5.707 7.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l4-4a1 1 0 0 0-1.414-1.414L7 8.586 5.707 7.293z'/%3e%3c/svg%3e);
                                background-color: currentcolor;
                                background-size: 100% 100%;
                            }
                            .CUSTOM_7 > input[type="checkbox"] {
                                display: none;
                            }
                            .CUSTOM_6[type="checkbox"], .CUSTOM_6[type="radio"] {
                                --tw-bg-opacity: 1;
                                background-color: hsla(211, 12%, 43%, var(--tw-bg-opacity));
                                cursor: pointer;
                                appearance: none;
                                display: inline-block;
                                vertical-align: middle;
                                user-select: none;
                                flex-shrink: 0;
                                width: 1rem;
                                height: 1rem;
                                --tw-text-opacity: 1;
                                color: rgba(96, 165, 250, var(--tw-text-opacity));
                                border-width: 1px;
                                --tw-border-opacity: 1;
                                border-color: hsla(211, 13%, 65%, var(--tw-border-opacity));
                                border-radius: 0.125rem;
                                background-origin: border-box;
                                transition: all 75ms linear 0s, box-shadow 25ms linear 0s;
                            }
                            .CUSTOM_7 > input[type="checkbox"]:checked + label {
                                --tw-bg-opacity: 1;
                                background-color: rgba(59, 130, 246, var(--tw-bg-opacity));
                                --tw-border-opacity: 1;
                                border-color: rgba(29, 78, 216, var(--tw-border-opacity));
                                --tw-shadow: 0 0 #0000;
                                box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
                            }
                            .CUSTOM_7 > label {
                                margin-bottom: 0px;
                                display: block;
                                overflow: hidden;
                                cursor: pointer;
                                --tw-bg-opacity: 1;
                                background-color: hsla(211, 10%, 53%, var(--tw-bg-opacity));
                                border-width: 1px;
                                --tw-border-opacity: 1;
                                border-color: hsla(209, 18%, 30%, var(--tw-border-opacity));
                                border-radius: 9999px;
                                height: 1.5rem;
                                --tw-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.06);
                                box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
                                transition: all 75ms linear 0s;
                            }
                            .CUSTOM_4 {
                                display: block;
                                font-size: 0.75rem;
                                line-height: 1rem;
                                text-transform: uppercase;
                                --tw-text-opacity: 1;
                                color: hsla(210, 16%, 82%, var(--tw-text-opacity));
                                margin-bottom: 0.25rem;
                            }
                            .CUSTOM_7 {
                                position: relative;
                                user-select: none;
                                width: 3rem;
                                line-height: 1.5;
                            }
                            .CUSTOM_7 > label::before {
                                position: absolute;
                                display: block;
                                --tw-bg-opacity: 1;
                                background-color: rgba(255, 255, 255, var(--tw-bg-opacity));
                                border-width: 1px;
                                height: 1.25rem;
                                width: 1.25rem;
                                border-radius: 9999px;
                                top: 0.125rem;
                                right: calc(50% + 0.125rem);
                                content: "";
                                transition: all 75ms ease-in 0s;
                            }
                            .CUSTOM_7 > input[type="checkbox"]:checked + label::before {
                                right: 0.125rem;
                            }
                            .TITLE_TEXT{
                                color: white;
                            }
                            div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm.CONTAINER_CONFIRM{
                                background: rgba(37, 99, 235);
                                border: none;
                                outline: none;
                            }
                            div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm.CONTAINER_CONFIRM:focus{
                                background: rgba(37, 99, 235);
                                border: none;
                                outline: none;
                                box-shadow: none;
                            }
                            .ERROR_MESSAGE{
                                background-color: #00000000;
                                color: #ffffff;
                            }
                        </style>
                        `,
                    focusConfirm: false,
                    customClass: {
                        popup: 'Modal___StyledDiv2-sc- MAIN_CONTAINER',
                        title: 'TITLE_TEXT',
                        confirmButton: 'CONTAINER_CONFIRM',
                        validationMessage: 'ERROR_MESSAGE'
                    },
                    confirmButtonText: 'Validate',
                        preConfirm: async () => {
                            const subdomain = document.querySelector('input[name="subdomainValue"]').value;
                            const domain = document.querySelector('select[name="domainValue"]').value;
                            const proxied = document.querySelector('input[name="isProxied"]').checked;
                            
                            if(subdomain.length+domain.length+1 < 1 || subdomain.length+domain.length+1 > 250){
                                Swal.showValidationMessage('Please ensure that whole domain is between 1 and 250 characters.');
                                return;
                            }
                            if(subdomain.length <= 1){
                                Swal.showValidationMessage('Please ensure that subdomain is at least 1 character.');
                                return;
                            }
                            if(subdomain.includes(' ')){
                                Swal.showValidationMessage('Please ensure that the subdomain contains no spaces.');
                                return;
                            }
                            if(subdomain.includes('--')){
                                Swal.showValidationMessage('Please ensure that the subdomain contains no double hyphens.');
                                return;
                            }
                            if(subdomain.includes('..')){
                                Swal.showValidationMessage('Please ensure that the subdomain contains no double dots.');
                                return;
                            }
                            if(subdomain.includes('.-')){
                                Swal.showValidationMessage('Please ensure that the subdomain contains no dots followed by hyphens.');
                                return;
                            }
                            if(subdomain.includes('-.')){
                                Swal.showValidationMessage('Please ensure that the subdomain contains no hyphens followed by dots.');
                                return;    
                            }
                            if(subdomain.startsWith('-')){
                                Swal.showValidationMessage('Please ensure that the subdomain does not start with a hyphen.');
                                return;
                            }
                            if(subdomain.endsWith('-')){
                                Swal.showValidationMessage('Please ensure that the subdomain does not end with a hyphen.');
                                return;
                            }
                            if(subdomain.startsWith('.')){
                                Swal.showValidationMessage('Please ensure that the subdomain does not start with a dot.');
                                return;
                            }
                            if(subdomain.endsWith('.')){
                                Swal.showValidationMessage('Please ensure that the subdomain does not end with a dot.');
                                return;
                            }
                            if(/[^a-z0-9\-\.]/i.test(subdomain)){
                                Swal.showValidationMessage('Please ensure that the subdomain only contains a-z 0-9 . and - .');
                                return;
                            }
                            const splitSubdomain = subdomain.split(".");
                            splitSubdomain.forEach(subSubDomain => {
                                if(subSubDomain.length >= 63){
                                    Swal.showValidationMessage('Please ensure that each individual subdomain only contains up to 63 characters.');
                                    return;
                                }
                            });

                            const domainsData = {
                                "domains": [
                                    {
                                        "name": subdomain+'.'+domain,
                                        "server": serverInfomation.attributes.uuid,
                                        "proxied": proxied,
                                        "notes": ''
                                    }
                                ]
                            };

                            const response = await fetch(('https://panel.webworkshub.online/pluginapi/subdomainPointer/create/?server='+serverInfomation.attributes.uuid+'&config='+encodeURIComponent(JSON.stringify(domainsData))));
                            const data = await response.json();

                            if(!data.success || data.success && data.success == 'false'){
                                Swal.showValidationMessage(data.error);
                            } else if(data.success && data.success == 'true'){
                                networkSection((newContent)=>{
                                    const oldContent = document.querySelector(themeSettings.server_content);
                                    oldContent.parentElement.insertBefore(newContent[1], oldContent);

                                    oldContent.remove();
                                });
                            }
                        }
                    });
                }
            }

            // Function to validate the input
            var prevVal = '';
            function validateSubdomainCharacter(e){
                const inputBox = e.target;
                if(inputBox.value.includes(' ')){
                    inputBox.value = inputBox.value.replace(/ /g, '-')
                }
                if(/[^a-z0-9\-\.]/i.test(inputBox.value)||
                inputBox.value.length > 250 // Not >= as remember we want it not to include 250 to allow it
                ){
                    inputBox.value = prevVal;
                }
                inputBox.value = inputBox.value.toLowerCase();
                prevVal = inputBox.value.toLowerCase();
            }

            // Insert after function
            function insertAfter(newNode, referenceNode) {
                referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
            }
        </script>
        <?php
    }
}