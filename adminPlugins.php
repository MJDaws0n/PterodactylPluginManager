<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['custom:plugin'])){
    $selectedPlugin = $_POST['custom:plugin'];

    $jsonFile = dirname(__FILE__) . '/adminSettings.json';
    if (file_exists($jsonFile)) {
        $jsonContent = file_get_contents($jsonFile);
        $settingsObject = json_decode($jsonContent, true);
    }

    $found = false;
    foreach ($settingsObject['plugins'] as $key => $plugin) {
        if ($plugin == $selectedPlugin) {
            unset($settingsObject['plugins'][$key]);
            $found = true;
        }
    }

    if(!$found){
        array_push($settingsObject['plugins'], $selectedPlugin);
    }
    
    chmod($file, 0666);
    file_put_contents($jsonFile, json_encode($settingsObject));
    header('location: /admin/custom/plugins');
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'GET'){
    // Code for replace
    $replacement = " ";
    if(explode('/', $currentUrl)[1] == 'admin'){
        $file_path = dirname(__FILE__).'/admin.js';
        if (file_exists($file_path)) {
            $file_content = file_get_contents($file_path);
            $base64_content = base64_encode($file_content);
            $mime_type = mime_content_type($file_path);
            $data_url = 'data:' . $mime_type . ';base64,' . $base64_content;
            $replacement = "<script src=\"$data_url\"></script>";
        } else {
            echo "Error: Admin file missing. $file_path";
        }
    }

    // Check if we are on the admin overview page
    if(explode('/', $currentUrl)[1] == 'admin'){
        global $config;
        echo "<script>const addonVersion = 'v{$config['version']}';</script>";
    }

    // Get the current domain of the website
    $currentDomain = $_SERVER['HTTP_HOST'];

    // Set the URL to ping
    $url = "https://$currentDomain/admin/settings?get=true";

    // Get all cookies that the current user has
    $cookies = "";
    foreach ($_COOKIE as $name => $value) {
        $cookies .= $name . '=' . $value . '; ';
    }

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, $cookies);

    // Execute cURL request
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        echo 'Error: ' . curl_error($ch);
    } else {
        // Save the response to a variable
        $pingResponse = $response;
        
        // Output the response
        echo str_replace($replacement, '', $pingResponse);
    }

    curl_close($ch);

    $jsonFile = dirname(__FILE__) . '/adminSettings.json';

    // Check if the file exists
    if (file_exists($jsonFile)) {
        $jsonContent = file_get_contents($jsonFile);
        $settingsObject = json_decode($jsonContent, true);
    } else {
        echo "File not found: $jsonFile";
    }
    ?>

    <script>
        document.addEventListener('sideBarLoaded', function(event) {
            var spanElements = document.querySelectorAll('span');

            spanElements.forEach(function(spanElement) {
            if (spanElement.textContent.trim() == "Plugins") {
                spanElement.parentElement.parentElement.classList.add('active');
                spanElement.parentElement.parentElement.classList.remove('1');
            }
            if (spanElement.textContent.trim() == "Settings") {
                spanElement.parentElement.parentElement.classList.add('1');
                spanElement.parentElement.parentElement.classList.remove('active');
            }
        });
        
        var title = document.querySelector('.content-wrapper .content-header h1');
        title.innerHTML = `Theme<small>Pick your favourite theme for pterodactyl</small>`;
        
        var directory = document.querySelector('.content-wrapper .breadcrumb li');
        directory.innerHTML = `<a href="/admin">Admin  >  Custom</a>`;
        var directory2 = document.querySelector('.content-wrapper .breadcrumb li.active');
        directory2.textContent = `Plugins`;
        
        // Tabs
        var tabs = document.querySelector('.content-wrapper .nav-tabs-custom.nav-tabs-floating .nav.nav-tabs');
        Array.from(tabs.children).forEach((tab)=>{
            tab.remove();
        });
        
        // Settings 
        var settingsOptions = document.querySelectorAll('.content-wrapper .content .box-body .form-group.col-md-4');
        
        settingsOptions.forEach((option)=>{
            option.remove();
        });

        // Add a plugin
        function addPlugin(name, id, description, author, url, last_update, enabled){
            const theme = document.createElement('div');
            theme.classList.add('row');
            theme.setAttribute('data-plugin', id);
            theme.classList.add('col-md-4');
            theme.classList.add('form-group');
            
            const nameElement = document.createElement('label');
            nameElement.textContent = name;
            nameElement.classList.add('control-label');

            const descriptionElement = document.createElement('small');
            descriptionElement.textContent = description;

            const lowerDecription = document.createElement('small');
            if(typeof author !== 'undefined'){
                lowerDecription.innerHTML = `Author: ${escapeHTML(author)}<br>`;
            }
            if(typeof url !== 'undefined'){
                lowerDecription.innerHTML += `URL: <a target="_blank" href="${escapeHTML(url)}">${escapeHTML(url)}</a><br>`;
            }
            if(typeof last_update !== 'undefined'){
                lowerDecription.innerHTML += `Last Updated: ${escapeHTML(last_update)}<br>`;
            }

            const installButton = document.createElement('button');
            
            installButton.textContent = 'Enable';

            // Check current theme
            if(enabled){
                installButton.textContent = 'Disable';
            }
            
            
            installButton.classList.add('btn-primary');
            installButton.classList.add('btn');
            installButton.addEventListener("click", (e) => {
                e.preventDefault();
                const form = document.createElement('form');
                form.method = 'post';
                form.action = window.location.href;

                const themeInput = document.createElement('input');
                themeInput.type = 'hidden';
                themeInput.name = 'theme';
                themeInput.value = theme;

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'custom:plugin';
                idInput.value = id;

                form.appendChild(themeInput);
                form.appendChild(idInput);

                document.body.appendChild(form);
                form.submit();
            });

            theme.appendChild(nameElement);
            theme.appendChild(document.createElement('br'));
            theme.appendChild(descriptionElement);
            theme.appendChild(document.createElement('br'));
            theme.appendChild(lowerDecription);
            theme.appendChild(installButton);

            document.querySelector('.content-wrapper .content .box-body').appendChild(theme);
        }

        <?php
        foreach (scandir(dirname(__FILE__) . '/plugins/') as $folder){
            if ($folder != '.' && $folder != '..' && is_dir(dirname(__FILE__) . '/plugins/' . $folder)) {
                $configFile = dirname(__FILE__) . '/plugins/'.$folder.'/main.ptero';
                
                if(file_exists($configFile) && json_decode(file_get_contents($configFile)) !== null){
                    $themeConfig = json_decode(file_get_contents($configFile), true);

                    $name = isset($themeConfig['name']) ? json_encode($themeConfig['name']) : 'null';
                    $description = isset($themeConfig['description']) ? json_encode($themeConfig['description']) : 'null';
                    $author = isset($themeConfig['author']) ? json_encode($themeConfig['author']) : 'null';
                    $url = isset($themeConfig['url']) ? json_encode($themeConfig['url']) : 'null';
                    $last_updated = isset($themeConfig['last_updated']) ? json_encode($themeConfig['last_updated']) : 'null';
                    
                    $pluginActive = 'false';
                    
                    // Check if plugin is enabled
                    foreach ($settingsObject['plugins'] as $plugin) {
                        if($plugin == $folder){
                            $pluginActive = 'true';
                        }
                    }

                    $themeID = json_encode($folder);
                    ?>
                    addPlugin(<?php echo $name;?>, <?php echo $themeID;?>, <?php echo $description;?>, <?php echo $author;?>, <?php echo $url;?>, <?php echo $last_updated;?>, <?php echo $pluginActive;?>);
                    <?php
                }
            }
        }
        ?>
        
        // Make the saving work
        var settingsOptions = document.querySelector('.content-wrapper .content .col-xs-12 .box form');
        settingsOptions.action = '/admin/custom/plugins';


        // Remove save button
        document.querySelector('.content-wrapper .content .col-xs-12 .box form .box-footer button').remove();

        // Change the title
        $('h3.box-title').text('Plugins');
    });

    function escapeHTML(html) {
        return html
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;")
            .replace(/&/g, "&amp;");
    }
    </script>
<?php
}