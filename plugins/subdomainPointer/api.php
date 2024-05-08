<?php
header('Content-Type: application/json');

$currentUrl = $_SERVER['REQUEST_URI'];
if(isset(explode('/', $currentUrl)[3])){
    if(explode('/', $currentUrl)[3] == 'getsubdomains'){
        if(isset($_GET['server'])){
            // Get the config data
            $data = json_decode(file_get_contents(dirname(__FILE__) . '/config.json'), true);

            // Get infomation about the user
            $ch = curl_init("https://{$_SERVER['HTTP_HOST']}/api/client/account");
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIE => $_SERVER['HTTP_COOKIE']]);
            $response = json_decode(curl_exec($ch), true);
            $userID = $response['attributes']['id'];
            $isAdmin = $response['attributes']['admin'];
            curl_close($ch);

            // Get the requested server
            $specific_server = $_GET['server'];
            $options_with_specific_server = array();

            // Allow the request if the user is admin
            if($isAdmin){
                foreach ($data['domains'] as $domain) {
                    if ($domain['server'] === $specific_server) {
                        $options_with_specific_server[] = $domain;
                    }
                }
    
            }
            print_r($options_with_specific_server);
        }
    }
}