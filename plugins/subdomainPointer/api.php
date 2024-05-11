<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$currentUrl = $_SERVER['REQUEST_URI'];
if(isset(explode('/', $currentUrl)[3])){
    if(explode('/', $currentUrl)[3] == 'getsubdomains'){
        if(isset($_GET['server'])){
            // Get the config data
            $data = json_decode(file_get_contents(dirname(__FILE__) . '/config.json'), true);
            
            $ch = curl_init("https://{$_SERVER['HTTP_HOST']}/api/client/servers/{$_GET['server']}");
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIE => $_SERVER['HTTP_COOKIE']]);
            $response = json_decode(curl_exec($ch), true);
            $hasAccess = isset($response['object']) && $response['object'] == "server";
            curl_close($ch);

            // Get the requested server
            $specific_server = $_GET['server'];
            $options_with_specific_server = array();

            // Allow the request if the user is admin
            if($hasAccess){
                foreach ($data['domains'] as $domain) {
                    if ($domain['server'] === $specific_server) {
                        $options_with_specific_server[] = $domain;
                    }
                }
    
            } else {
                echo json_encode([
                    "success" => "false",
                    "error" => "unauthorised"
                ]);
                exit();
            }
            echo json_encode([
                "success" => "true",
                "response" => $options_with_specific_server
            ]);
        }
    }
    if(explode('/', $currentUrl)[3] == 'updateConfig'){
        if(isset($_GET['server']) && isset($_GET['config'])){
            // Get the config data
            $data = json_decode(file_get_contents(dirname(__FILE__) . '/config.json'), true);
            
            $ch = curl_init("https://{$_SERVER['HTTP_HOST']}/api/client/servers/{$_GET['server']}");
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIE => $_SERVER['HTTP_COOKIE']]);
            $response = json_decode(curl_exec($ch), true);
            $hasAccess = isset($response['object']) && $response['object'] == "server";
            curl_close($ch);

            // Get the requested server
            $specific_server = $_GET['server'];
            $config = json_decode($_GET['config'], true);

            // print_r($config);

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

            // Allow the request if the user is admin
            if($hasAccess){
                foreach ($data['domains'] as $domain) {
                    if ($domain['server'] === $specific_server && getServer($domain['name'], $config)['name'] == $domain['name']) {
                        $domain = getServer($domain['name'], $config);
                    }
                    array_push($newData['domains'], $domain);
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

                        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $subdomain)) {
                            echo json_encode(["success" => "false","error" => ('Invalid subdomain name.')]);
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

                // Get the requested server
                $specific_server = $_GET['server'];
                $config = json_decode($_GET['config'], true)['domains'][0];

                $newData = $data;

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

                // More validation
                if(strlen($subdomain) <= 1){
                    echo json_encode(["success" => "false","error" => ('Please ensure that subdomain is at least 1 character.')]);
                    exit();
                }

                if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                    echo json_encode(["success" => "false","error" => ('Invalid domain name.')]);
                    exit();
                }

                if (!preg_match('/^[a-zA-Z0-9\-]+$/', $subdomain)) {
                    echo json_encode(["success" => "false","error" => ('Invalid subdomain name.')]);
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
}