<?php
header('Content-Type: application/json');

$currentUrl = $_SERVER['REQUEST_URI'];
if(isset(explode('/', $currentUrl)[3])){
    if(explode('/', $currentUrl)[3] == 'getsubdomains'){
        if(isset($_GET['server'])){
            // Get the config data
            if(!file_exists(dirname(__FILE__) . '/config.json')){
                echo json_encode([
                    "success" => "false",
                    "error" => "Server error. No config file found."
                ]);
                exit();
            }
            if(!$data = json_decode(file_get_contents(dirname(__FILE__) . '/config.json'), true) ? : null){
                echo json_encode([
                    "success" => "false",
                    "error" => "Server error. Inavlid config file."
                ]);
                exit();
            }
            
            $ch = curl_init("https://{$_SERVER['HTTP_HOST']}/api/client/servers/{$_GET['server']}");
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIE => $_SERVER['HTTP_COOKIE']]);
            $response = json_decode(curl_exec($ch), true);
            curl_close($ch);

            $hasAccess = isset($response['object']) && $response['object'] == "server";
            if(!$hasAccess){
                echo json_encode([
                    "success" => "false",
                    "error" => "Unauthorised."
                ]);
                exit();
            }

            // Get the requested server
            $specific_server = $_GET['server'];
            $options_with_specific_server = array();

            // Allow the request if the user is admin
            foreach ($data['domains'] as $domain) {
                if ($domain['server'] === $specific_server) {
                    $options_with_specific_server[] = $domain;
                }
            }
            echo json_encode([
                "success" => "true",
                "response" => $options_with_specific_server
            ]);
            exit();
        } else{
            echo json_encode([
                "success" => "false",
                "error" => "No specified server."
            ]);
            exit();
        }
    }
    if(explode('/', $currentUrl)[3] == 'updateConfig'){
        if(isset($_GET['server']) && isset($_GET['config'])){
            // Get the config data
            if(!file_exists(dirname(__FILE__) . '/config.json')){
                echo json_encode([
                    "success" => "false",
                    "error" => "Server error. No config file found."
                ]);
                exit();
            }
            if(!$data = json_decode(file_get_contents(dirname(__FILE__) . '/config.json'), true) ? : null){
                echo json_encode([
                    "success" => "false",
                    "error" => "Server error. Inavlid config file."
                ]);
                exit();
            }
            
            $ch = curl_init("https://{$_SERVER['HTTP_HOST']}/api/client/servers/{$_GET['server']}");
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIE => $_SERVER['HTTP_COOKIE']]);
            $response = json_decode(curl_exec($ch), true);
            curl_close($ch);
            $hasAccess = isset($response['object']) && $response['object'] == "server";

            if(!$hasAccess || !isset($response['attributes']['relationships']['allocations']['data'])){
                echo json_encode([
                    "success" => "false",
                    "error" => "Unauthorised."
                ]);
                exit();
            }
            $allocationFound = false;
            foreach ($response['attributes']['relationships']['allocations']['data'] as $allocation) {
                if($allocation['attributes']['is_default']){
                    $allocationFound = true;
                    $serverProxyUrl = 'http://'.$allocation['attributes']['ip_alias'].':'.$allocation['attributes']['port'];
                }
            }
            if(!$allocationFound){
                echo json_encode([
                    "success" => "false",
                    "error" => "Server error. Default allocation not found."
                ]);
                exit();
            }

            // Get the requested server
            $specific_server = $_GET['server'];

            // Validate the config
            if(!$config = json_decode($_GET['config'], true) ? : null){
                echo json_encode([
                    "success" => "false",
                    "error" => "Invalid specified config."
                ]);
                exit();
            }

            validateConfig($config);

            // Function to get the server
            function getServer($subdomain, $config){
                foreach ($config['domains'] as $domain) {
                    if ($domain['name'] === $subdomain) {
                        return $domain;
                    }
                }
                return $domain;
            }

            if(!isset($data['domains'])){
                echo json_encode([
                    "success" => "false",
                    "error" => "Server error. Invalid config file."
                ]);
                exit();
            }

            $newData = $data;
            $newData['domains'] = [];

            $domainFound = false;

            foreach ($data['domains'] as $domain) {
                if(
                    !isset($domain['name']) ||
                    !isset($domain['server']) ||
                    !isset($domain['proxied']) ||
                    !isset($domain['notes'])
                ){
                    echo json_encode([
                        "success" => "false",
                        "error" => "Server error. Invalid config file."
                    ]);
                    exit();
                }
                if ($domain['server'] == $specific_server && getServer($domain['name'], $config)['name'] == $domain['name']) {
                    $domainFound = true;
                    // Check domains meet the specified domains regulations

                    // Get the desired infomation
                    $domainParts = explode('.', getServer($domain['name'], $config)['name']);
                    $certs = ["fullchain"=>"", "privkey"=>""];

                    // Domain and subdomain details
                    $domainStr = implode('.', array_slice($domainParts, -2));
                    $subdomainStr = implode('.', array_slice($domainParts, 0, -2));

                    if(!file_exists(dirname(__FILE__) . '/domains.json')){
                        echo json_encode([
                            "success" => "false",
                            "error" => "Server error. Missing domains list file."
                        ]);
                        exit();
                    }
                    if(!file_exists(dirname(__FILE__) . '/domains.json')){
                        echo json_encode([
                            "success" => "false",
                            "error" => "Server error. Domains list connfig file not found."
                        ]);
                        exit();
                    }
                    if(!$domains = json_decode(file_get_contents(dirname(__FILE__) . '/domains.json'), true) ? : null){
                        echo json_encode([
                            "success" => "false",
                            "error" => "Server error. Domains list is invalid."
                        ]);
                        exit();
                    }
                    $found = false;
                    foreach ($domains as $localDomain) {
                        if(!isset($localDomain['domain'])){
                            echo json_encode([
                                "success" => "false",
                                "error" => "Server error. Domains list contains invalid domain configuration: Section 'name'."
                            ]);
                            exit();
                        }
                        if(!isset($localDomain['fullchain']) || !isset($localDomain['privkey'])){
                            echo json_encode([
                                "success" => "false",
                                "error" => "Server error. Domains list contains invalid domain configuration: Section 'fullchain' or 'privkey'."
                            ]);
                            exit();
                        }
                        if(!isset($localDomain['allow'])){
                            echo json_encode([
                                "success" => "false",
                                "error" => "Server error. Domains list contains invalid domain configuration: Section 'allow'."
                            ]);
                            exit();
                        }
                        if($localDomain['domain'] == $domainStr){
                            $found = true;
                            if(!preg_match("/{$localDomain['allow']}/", $subdomainStr . '.' . $domainStr)){
                                echo json_encode(["success" => "false","error" => ('The selected subdomain does not comply with the domains settings. Please pick something else.')]);
                                exit();
                            }

                            $certs["fullchain"] = $localDomain['fullchain'];
                            $certs["privkey"] = $localDomain['privkey'];
                        }
                    }
                    if(!$found){
                        echo json_encode(["success" => "false","error" => ('Domain not in domain list.')]);
                        exit();
                    }

                    if(!$domain['proxied'] && getServer($domain['name'], $config)['proxied']){ // Is proxied but was not before
                        // Add new value
                        updateConfFile($domain['name'], $certs['fullchain'], $certs['privkey'], $serverProxyUrl);
                    } else if($domain['proxied'] && !getServer($domain['name'], $config)['proxied']){ // Not proxied but was before
                        // Remove value
                        if(!file_exists(dirname(__FILE__) . '/p80.conf')){
                            echo json_encode([
                                "success" => "false",
                                "error" => "Server error. Missing p80.conf file."
                            ]);
                            exit();
                        }
                        
                        file_put_contents(dirname(__FILE__) . '/p80.conf', removeLastNewline(remove_section_between_markers(file_get_contents(dirname(__FILE__) . '/p80.conf'), '# START - '.$domain['name'], '# END - '.$domain['name'])));
                        exec('sudo /usr/sbin/nginx -s reload');
                    }

                    $domain = getServer($domain['name'], $config);
                }
                array_push($newData['domains'], $domain);
            }
            if(!$domainFound){
                echo json_encode([
                    "success" => "false",
                    "error" => "Domain not registerd to this server."
                ]);
                exit();
            }
            // Modify the 'config.json' file
            file_put_contents(dirname(__FILE__) . '/config.json', json_encode($newData));

            echo json_encode([
                "success" => "true"
            ]);
        } else{
            echo json_encode([
                "success" => "false",
                "error" => "No specified server and or config."
            ]);
            exit();
        }
    }
    if(explode('/', $currentUrl)[3] == 'remove'){
        if(isset($_GET['server']) && isset($_GET['name'])){
            // Get the config data
            if(!file_exists(dirname(__FILE__) . '/config.json')){
                echo json_encode([
                    "success" => "false",
                    "error" => "Server error. No config file found."
                ]);
                exit();
            }
            if(!$data = json_decode(file_get_contents(dirname(__FILE__) . '/config.json'), true) ? : null){
                echo json_encode([
                    "success" => "false",
                    "error" => "Server error. Inavlid config file."
                ]);
                exit();
            }
            
            $ch = curl_init("https://{$_SERVER['HTTP_HOST']}/api/client/servers/{$_GET['server']}");
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIE => $_SERVER['HTTP_COOKIE']]);
            $response = json_decode(curl_exec($ch), true);
            curl_close($ch);

            // Check the user has access
            $hasAccess = isset($response['object']) && $response['object'] == "server";
            if(!$hasAccess || !isset($response['attributes']['relationships']['allocations']['data'])){
                echo json_encode([
                    "success" => "false",
                    "error" => "Unauthorised."
                ]);
                exit();
            }

            // Get the requested server
            $specific_server = $_GET['server'];
            $domainName = $_GET['name'];

            foreach ($data['domains'] as $domain) {
                if ($domain['server'] === $specific_server && $domain['name'] == $domainName) {
                    // Validation
                    if(
                        !isset($domain['name']) ||
                        !isset($domain['server']) ||
                        !isset($domain['proxied']) ||
                        !isset($domain['notes'])
                    ){
                        echo json_encode([
                            "success" => "false",
                            "error" => "Server error. Invalid config file."
                        ]);
                        exit();
                    }

                    // Remove the domain from cloudflare
                    // Load environment variables from .env file
                    $envFile = __DIR__ . '/.env';
                    if (file_exists($envFile)) {
                        $env = parse_ini_file($envFile);
                        if (!$env) {
                            echo json_encode([
                                "success" => "false",
                                "error" => "Server error. .env file is invalid or empty."
                            ]);
                            exit();
                        }
                    } else {
                        echo json_encode([
                            "success" => "false",
                            "error" => "Server error. .env file now found."
                        ]);
                        exit();
                    }

                    if(!$email = isset($env['CLOUDFLARE_EMAIL']) ? $env['CLOUDFLARE_EMAIL'] : null){
                        echo json_encode([
                            "success" => "false",
                            "error" => "Server error. .env file is missing CLOUDFLARE_EMAIL."
                        ]);
                        exit();
                    }
                    if(!$apiToken = isset($env['CLOUDFLARE_API_TOKEN']) ? $env['CLOUDFLARE_API_TOKEN'] : null){
                        echo json_encode([
                            "success" => "false",
                            "error" => "Server error. .env file is missing CLOUDFLARE_API_TOKEN."
                        ]);
                        exit();
                    }

                    // Get the desired information
                    $domainParts = explode('.', $domain['name']);

                    // Domain and subdomain details
                    $domainStr = implode('.', array_slice($domainParts, -2));
                    $subdomain = implode('.', array_slice($domainParts, 0, -2));

                    // More validation
                    if(strlen($subdomain) <= 1){
                        echo json_encode(["success" => "false","error" => ('Please ensure that subdomain is at least 1 character.')]);
                        exit();
                    }

                    if (!filter_var($domainStr, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                        echo json_encode(["success" => "false","error" => ('Invalid domain name.')]);
                        exit();
                    }

                    // Cloudflare API endpoint
                    $endpoint = "https://api.cloudflare.com/client/v4/zones";

                    // Zone ID for the domain - automatically fetched
                    $zoneID = ''; 

                    // Fetch zone ID for the domain
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $endpoint . '?name=' . $domainStr);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'X-Auth-Email: ' . $email,
                        'Authorization: Bearer ' . $apiToken,
                        'Content-Type: application/json'
                    ]);
                    $response = curl_exec($ch);
                    curl_close($ch);

                    $result = json_decode($response, true);

                    if ($result && isset($result['result'][0]['id'])) {
                        $zoneID = $result['result'][0]['id'];
                    } else {
                        echo json_encode(["success" => "false","error" => ('Server error. Cloudflare rejected request. This is usually becasue your Key / Email is Invalid or the domain is not registerd with your account.')]);
                        exit();
                    }

                    // List all the records and remove the correct one
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $endpoint . '/'.$zoneID.'/dns_records');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'X-Auth-Email: ' . $email,
                        'Authorization: Bearer ' . $apiToken,
                        'Content-Type: application/json'
                    ]);
                    $response = curl_exec($ch);
                    curl_close($ch);

                    $domainID = '';

                    if(!$result = json_decode($response, true) ?: null){
                        echo json_encode(["success" => "false","error" => ('Server error. Cloudflare rejected listing the subdomains.')]);
                        exit();
                    }
                    if(!$result = $result['result'] ?: null){
                        echo json_encode(["success" => "false","error" => ('Server error. Cloudflare rejected listing the subdomains.')]);
                        exit();
                    }

                    foreach ($result as $record) {
                        if(!isset($record['name']) || !isset($record['id'])){
                            echo json_encode(["success" => "false","error" => ('Server error. Cloudflare response contains invalid data.')]);
                            exit();
                        }
                        if($record['name'] == $subdomain.'.'.$domainStr){
                            $domainID = $record['id'];
                        }
                    }

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $endpoint . '/' . $zoneID . '/dns_records/'.$domainID);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'X-Auth-Email: ' . $email,
                        'Authorization: Bearer ' . $apiToken,
                        'Content-Type: application/json'
                    ]);
                    $response = curl_exec($ch);
                    curl_close($ch);
    
                    $result = json_decode($response, true);
                    if (!($result && isset($result['success']) && $result['success']) && ($result && isset($result['errors'][0]['message']))) {
                        echo json_encode(["success" => "false","error" => ('Server error. '.$result['errors'][0]['message'])]);
                        exit();
                    } else if(!($result && isset($result['success']) && $result['success']) && !($result && isset($result['errors'][0]['message']))) {
                        echo json_encode(["success" => "false","error" => ('Server error. Unknown error occurred.')]);
                        exit();
                    }

                    // Remove from p80 conf
                    if(!file_exists(dirname(__FILE__) . '/p80.conf')){
                        echo json_encode([
                            "success" => "false",
                            "error" => "Server error. Domain successfully removed from cloudflare however missing p80.conf file preventing from removing the possible proxy config."
                        ]);
                        exit();
                    }

                    file_put_contents(dirname(__FILE__) . '/p80.conf', removeLastNewline(remove_section_between_markers(file_get_contents(dirname(__FILE__) . '/p80.conf'), '# START - '.$domain['name'], '# END - '.$domain['name'])));
                    exec('sudo /usr/sbin/nginx -s reload');
                }
            }
            // Get the new data again (it may have been updated)
            if(!file_exists(dirname(__FILE__) . '/config.json')){
                echo json_encode([
                    "success" => "false",
                    "error" => "Server error. No config file found."
                ]);
                exit();
            }
            if(!$data = json_decode(file_get_contents(dirname(__FILE__) . '/config.json'), true) ?: null){
                echo json_encode([
                    "success" => "false",
                    "error" => "Server error. Invalid config file."
                ]);
                exit();
            }
            $newData = $data;
            $newData['domains'] = [];

            // Check again as the file may have changed
            foreach ($data['domains'] as $domain) {
                // Validation
                if(
                    !isset($domain['name']) ||
                    !isset($domain['server']) ||
                    !isset($domain['proxied']) ||
                    !isset($domain['notes'])
                ){
                    echo json_encode([
                        "success" => "false",
                        "error" => "Server error. Invalid config file."
                    ]);
                    exit();
                }
                if ($domain['server'] === $specific_server && $domain['name'] == $domainName) {
                    unset($domain);
                }
                if(isset($domain)){
                    array_push($newData['domains'], $domain);
                }
            }
            
            // Modify the 'config.json' file
            file_put_contents(dirname(__FILE__) . '/config.json', json_encode($newData));

            echo json_encode([
                "success" => "true"
            ]);
        } else{
            echo json_encode([
                "success" => "false",
                "error" => "No specified server and or name."
            ]);
            exit();
        }
    }
    if(explode('/', $currentUrl)[3] == 'create'){
        if(isset($_GET['server']) && isset($_GET['config'])){
            // Get the config data
            $data = json_decode(file_get_contents(dirname(__FILE__) . '/config.json'), true);
            
            $ch = curl_init("https://{$_SERVER['HTTP_HOST']}/api/client/servers/{$_GET['server']}");
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIE => $_SERVER['HTTP_COOKIE']]);
            $response = json_decode(curl_exec($ch), true);
            curl_close($ch);

            $hasAccess = isset($response['object']) && $response['object'] == "server";
            if($hasAccess){
                $serverIPS = $response['attributes']['relationships']['allocations']['data'];
                $serverIP = [];

                foreach ($serverIPS as $currentServerIP) {
                    if($currentServerIP['object'] == 'allocation' && isset($currentServerIP['attributes'])){
                        if($currentServerIP['attributes']['is_default']){
                            $serverIP = $currentServerIP['attributes'];
                        }
                    }
                }

                foreach ($response['attributes']['relationships']['allocations']['data'] as $allocation) {
                    if($allocation['attributes']['is_default']){
                        $serverProxyUrl = 'http://'.$allocation['attributes']['ip_alias'].':'.$allocation['attributes']['port'];
                    }
                }

                // Get the requested server
                $specific_server = $_GET['server'];
                $config = json_decode($_GET['config'], true)['domains'][0];

                // Check the domain is not already in use
                foreach ($data['domains'] as $domain) {
                    if ($domain['name'] == $config['name']) {
                        echo json_encode([
                            "success" => "false",
                            "error" => "The specified domain is already in use."
                        ]);
                        exit();
                    } 
                }

                // Backend Validation
                if(strlen($config['name']) <= 8 || strlen($config['name']) > 250){
                    echo json_encode(["success" => "false","error" => ('Please ensure that whole domain is between 1 and 250 characters.'.$config['name'])]);
                    exit();
                }
                if(str_contains($config['name'], ' ')){
                    echo json_encode(["success" => "false","error" => ('Please ensure that the subdomain contains no spaces.')]);
                    exit();
                }
                if(str_contains($config['name'], '--')){
                    echo json_encode(["success" => "false","error" => ('Please ensure that the subdomain contains no double hyphens.')]);
                    exit();
                }
                if(str_contains($config['name'], '..')){
                    echo json_encode(["success" => "false","error" => ('Please ensure that the subdomain contains no double dots.')]);
                    exit();
                }
                if(str_contains($config['name'], '.-')){
                    echo json_encode(["success" => "false","error" => ('Please ensure that the subdomain contains no dots followed by hyphens.')]);
                    exit();
                }
                if(str_contains($config['name'], '-.')){
                    echo json_encode(["success" => "false","error" => ('Please ensure that the subdomain contains no hyphens followed by dots.')]);
                    exit();    
                }
                if(str_starts_with($config['name'], '-')){
                    echo json_encode(["success" => "false","error" => ('Please ensure that the subdomain does not start with a hyphen.')]);
                    exit();
                }
                if(str_ends_with($config['name'], '-')){
                    echo json_encode(["success" => "false","error" => ('Please ensure that the subdomain does not end with a hyphen.')]);
                    exit();
                }
                if(str_starts_with($config['name'], '.')){
                    echo json_encode(["success" => "false","error" => ('Please ensure that the subdomain does not start with a dot.')]);
                    exit();
                }
                if(str_ends_with($config['name'], '.')){
                    echo json_encode(["success" => "false","error" => ('Please ensure that the subdomain does not end with a dot.')]);
                    exit();
                }
                if(preg_match('/[^a-z0-9\-\.]/', $config['name'])){
                    echo json_encode(["success" => "false","error" => ('Please ensure that the subdomain only contains a-z 0-9 . and - .')]);
                    exit();
                }
                $splitSubdomain = explode(".", $config['name']);
                foreach ($splitSubdomain as $subSubDomain) {
                    if(strlen($subSubDomain) >= 63){
                        echo json_encode(["success" => "false","error" => ('Please ensure that each individual subdomain only contains up to 63 characters.')]);
                        exit();
                    }
                }

                // Link the domain with cloudflare
                // Load environment variables from .env file
                $envFile = __DIR__ . '/.env';
                if (file_exists($envFile)) {
                    $env = parse_ini_file($envFile);
                    if (!$env) {
                        die('.env file is invalid or empty.');
                    }
                } else {
                    die('.env file not found.');
                }

                $email = isset($env['CLOUDFLARE_EMAIL']) ? $env['CLOUDFLARE_EMAIL'] : null;
                $apiToken = isset($env['CLOUDFLARE_API_TOKEN']) ? $env['CLOUDFLARE_API_TOKEN'] : null;

                // Get the desired infomation
                $domainParts = explode('.', $config['name']);

                // Domain and subdomain details
                $domain = implode('.', array_slice($domainParts, -2));
                $subdomain = implode('.', array_slice($domainParts, 0, -2));
                $targetCNAME = $serverIP['ip_alias'];

                // Check domains meet the specified domains regulations
                $domains = json_decode(file_get_contents(dirname(__FILE__) . '/domains.json'), true) ? : null;
                $certs = ["fullchain"=>"", "privkey"=>""];

                $found = false;

                foreach ($domains as $localDomain) {
                    if($localDomain['domain'] == $domain){
                        $found = true;
                        if(!preg_match("/{$localDomain['allow']}/", $subdomain . '.' . $domain)){
                            echo json_encode(["success" => "false","error" => ('The selected subdomain does not comply with the domains settings. Please pick something else.')]);
                            exit();
                        }

                        $certs["fullchain"] = $localDomain['fullchain'];
                        $certs["privkey"] = $localDomain['privkey'];
                    }
                }

                if(!$found){
                    echo json_encode(["success" => "false","error" => ('Domain not in domain list.')]);
                    exit();
                }

                // More validation
                if(strlen($subdomain) <= 1){
                    echo json_encode(["success" => "false","error" => ('Please ensure that subdomain is at least 1 character.')]);
                    exit();
                }

                if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                    echo json_encode(["success" => "false","error" => ('Invalid domain name.')]);
                    exit();
                }

                // Cloudflare API endpoint
                $endpoint = "https://api.cloudflare.com/client/v4/zones";

                // Zone ID for the domain - automatically fetched
                $zoneID = ''; 

                // Fetch zone ID for the domain
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $endpoint . '?name=' . $domain);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'X-Auth-Email: ' . $email,
                    'Authorization: Bearer ' . $apiToken,
                    'Content-Type: application/json'
                ]);
                $response = curl_exec($ch);
                curl_close($ch);

                $result = json_decode($response, true);

                if ($result && isset($result['result'][0]['id'])) {
                    $zoneID = $result['result'][0]['id'];
                } else {
                    echo json_encode(["success" => "false","error" => ('Server error. Domain name not registerd to account.')]);
                    exit();
                }

                // Create subdomain with CNAME record
                $recordData = [
                    'type' => 'CNAME',
                    'name' => $subdomain . '.' . $domain,
                    'content' => $targetCNAME,
                    'proxied' => false // Event with proxy enabled, we are not talking about the cloudflare froxy
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $endpoint . '/' . $zoneID . '/dns_records');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($recordData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'X-Auth-Email: ' . $email,
                    'Authorization: Bearer ' . $apiToken,
                    'Content-Type: application/json'
                ]);
                $response = curl_exec($ch);
                curl_close($ch);

                $result = json_decode($response, true);
                if (!($result && isset($result['success']) && $result['success']) && ($result && isset($result['errors'][0]['message']))) {
                    echo json_encode(["success" => "false","error" => ('Server error. '.$result['errors'][0]['message'])]);
                    exit();
                } else if(!($result && isset($result['success']) && $result['success']) && !($result && isset($result['errors'][0]['message']))) {
                    echo json_encode(["success" => "false","error" => ('Server error. Unknown error occurred.')]);
                    exit();
                }

                // Add to p80.conf
                if($config['proxied']){
                    updateConfFile($config['name'], $certs['fullchain'], $certs['privkey'], $serverProxyUrl);
                }

                // Get the latest file infomation
                $newData = json_decode(file_get_contents(dirname(__FILE__) . '/config.json'), true);

                // Finally push the array to the file
                array_push($newData['domains'], $config);
            } else {
                echo json_encode([
                    "success" => "false",
                    "error" => "Unauthorised"
                ]);
                exit();
            }
            // Modify the 'config.json' file
            file_put_contents(dirname(__FILE__) . '/config.json', json_encode($newData));

            echo json_encode([
                "success" => "true"
            ]);
        }
    }
    if(explode('/', $currentUrl)[3] == 'getDomains'){
        if(file_exists(dirname(__FILE__) . '/domains.json')){
            $domains = json_decode(file_get_contents(dirname(__FILE__) . '/domains.json'), true) ? : null;
            $domainsReturn = [];

            foreach ($domains as $domain) {
                $domainsReturn[$domain['domain']] = $domain['display'];
            }

            echo json_encode([
                "success" => "true",
                "results" => $domainsReturn
            ]);
            exit();
        } else{
            echo json_encode([
                "success" => "false",
                "error" => "Invalid config.".dirname(__FILE__) . '/domains.json'
            ]);
            exit();
        }
    }
}

function updateConfFile($serverName, $sslCertPath, $sslKeyPath, $proxyPass){
    $confDir = dirname(__FILE__) . '/p80.conf';

    // Check the config exists
    if(!file_exists($confDir)){
        echo json_encode([
            "success" => "false",
            "error" => "No Nginx config file found."
        ]);
        exit();
    }

    // Get template
    if(!file_exists(dirname(__FILE__) . '/template.conf')){
        echo json_encode([
            "success" => "false",
            "error" => "No Nginx template config file found."
        ]);
        exit();
    }

    $template = file_get_contents(dirname(__FILE__) . '/template.conf');

    $config = str_replace(
        array('{{SERVER_NAME}}', '{{SSL_CERT}}', '{{SSL_KEY}}', '{{PROXY_PASS}}'),
        array($serverName, $sslCertPath, $sslKeyPath, $proxyPass),
        $template
    );

    $oldConfig = file_get_contents($confDir);

    $newConfig = removeLastNewline(remove_section_between_markers($oldConfig, '# START - '.$serverName, '# END - '.$serverName))."\n".$config;
    file_put_contents(dirname(__FILE__) . '/p80.conf', $newConfig);

    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $env = parse_ini_file($envFile);
        if (!$env) {
            echo json_encode([
                "success" => "false",
                "error" => "Missing .env file."
            ]);
            exit();
        }
    } else {
        echo json_encode([
            "success" => "false",
            "error" => "Missing .env file."
        ]);
        exit();
    }

    exec("sudo /usr/sbin/nginx -t", $output, $return_var);
    
    // Check if test was successful
    if ($return_var !== 0) {
        // Go back to old config
        file_put_contents(dirname(__FILE__) . '/p80.conf', $oldConfig);
        echo json_encode([
            "success" => "false",
            "error" => "Failed to valididate new config.",
            "config" => $newConfig
        ]);
        exit();
    } else{
        exec('sudo /usr/sbin/nginx -s reload', $output, $return_var);
        return true;
    }
}
function remove_section_between_markers($text, $start_marker, $end_marker) {
    return preg_replace("/$start_marker.*?$end_marker/s", "", $text);
}
function removeLastNewline($string) {
    return substr($string, -1) === "\n" ? rtrim($string, "\n") : $string;
}
function validateConfig($config){
    if(!isset($config['domains'])){
        echo json_encode([
            "success" => "false",
            "error" => "Invalid request. Config file missing domains section."
        ]);
        exit();
    }
    if(!isset($config['domains'][0])){
        echo json_encode([
            "success" => "false",
            "error" => "Invalid request. Config file missing domains section."
        ]);
        exit();
    }
    if(!isset($config['domains'][0]['name'])){
        echo json_encode([
            "success" => "false",
            "error" => "Invalid request. Domain missing name."
        ]);
        exit();
    }
    if(!isset($config['domains'][0]['server'])){
        echo json_encode([
            "success" => "false",
            "error" => "Invalid request. Domain missing server."
        ]);
        exit();
    }
    if(!isset($config['domains'][0]['proxied'])){
        echo json_encode([
            "success" => "false",
            "error" => "Invalid request. Domain missing proxied."
        ]);
        exit();
    }
    if(!isset($config['domains'][0]['notes'])){
        echo json_encode([
            "success" => "false",
            "error" => "Invalid request. Domain missing notes."
        ]);
        exit();
    }
    if(isset($config['domains'][1])){
        echo json_encode([
            "success" => "false",
            "error" => "Only one config aloud to update at a time."
        ]);
        exit();
    }
}

/*
    Sorting out status
    Doing - create
    Done (
        -   getsubdomains
        -   updateConfig
        -   remove
    )
*/