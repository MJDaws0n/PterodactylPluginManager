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
                    "error" => "unauthorised."
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

            if(!$config = json_decode($_GET['config'], true) ? : null){
                echo json_encode([
                    "success" => "false",
                    "error" => "Invalid specified config."
                ]);
                exit();
            }

            // Function to get the server
            function getServer($subdomain, $config){
                foreach ($config['domains'] as $domain) {
                    if ($domain['name'] === $subdomain) {
                        return $domain;
                    }
                }
                return $domain;
            }

            $newData = $data;
            $newData['domains'] = [];

            foreach ($data['domains'] as $domain) {
                if ($domain['server'] == $specific_server && getServer($domain['name'], $config)['name'] == $domain['name']) {
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
                    if(!$domains = json_decode(file_get_contents(dirname(__FILE__) . '/domains.json'), true) ? : null){
                        echo json_encode([
                            "success" => "false",
                            "error" => "Server error. Domains list is invalid."
                        ]);
                        exit();
                    }
                    $found = false;
                    foreach ($domains as $localDomain) {
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
            // Modify the 'config.json' file
            file_put_contents(dirname(__FILE__) . '/config.json', json_encode($newData));

            echo json_encode([
                "success" => "true"
            ]);
        }
    }
    if(explode('/', $currentUrl)[3] == 'remove'){
        if(isset($_GET['server']) && isset($_GET['name'])){
            // Get the config data
            $data = json_decode(file_get_contents(dirname(__FILE__) . '/config.json'), true);
            
            $ch = curl_init("https://{$_SERVER['HTTP_HOST']}/api/client/servers/{$_GET['server']}");
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIE => $_SERVER['HTTP_COOKIE']]);
            $response = json_decode(curl_exec($ch), true);
            $hasAccess = isset($response['object']) && $response['object'] == "server";
            curl_close($ch);

            // Get the requested server
            $specific_server = $_GET['server'];
            $domainName = $_GET['name'];

            // Allow the request if the user is admin
            if($hasAccess){
                foreach ($data['domains'] as $domain) {
                    if ($domain['server'] === $specific_server && $domain['name'] == $domainName) {
                        // Found the domain to remove

                        // Remove the domain from cloudflare
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
                            echo json_encode(["success" => "false","error" => ('Server error. Domain name not registered to account.')]);
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

                        $result = json_decode($response, true)['result'];

                        foreach ($result as $record) {
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
                        file_put_contents(dirname(__FILE__) . '/p80.conf', removeLastNewline(remove_section_between_markers(file_get_contents(dirname(__FILE__) . '/p80.conf'), '# START - '.$domain['name'], '# END - '.$domain['name'])));
                        exec('sudo /usr/sbin/nginx -s reload');
                    }
                }
                // Get the new data again (it may have been updated)
                $data = json_decode(file_get_contents(dirname(__FILE__) . '/config.json'), true);
                $newData = $data;
                $newData['domains'] = [];

                // Check again as the file may have changed
                foreach ($data['domains'] as $domain) {
                    if ($domain['server'] === $specific_server && $domain['name'] == $domainName) {
                        unset($domain);
                    }
                    if(isset($domain)){
                        array_push($newData['domains'], $domain);
                    }
                }
    
            } else {
                echo json_encode([
                    "success" => "false",
                    "error" => "unauthorised"
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
                    "error" => "unauthorised"
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

/*
    Sorting out status
    Doing - updateConfig
    Done (
        -   getsubdomains
    )
*/
// Need to finish off updateConfig (don't get fooled, it is un fishied) but also need to start to validate things like $domain['name'] for example, actually exists.