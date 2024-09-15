<?php
namespace Net\MJDawson\AddonManager\Core;

class BundlePatcher{
    private $errorLisnters = [];

    public function patch($xpath, $dom, $uri) {
        // Check if the request is a GET and if we should run the patcher
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' && ($uri[0] == '' || $uri[0] == 'server' || $uri[0] == 'account')){
            // Modify the script that the server requests to load
            $bundleScript = $xpath->query("//script[starts-with(@src, '/assets/bundle')]");

            // Set the to the src to the custom bundle script located at /addon/scripts/bundle.js
            $bundleScript->item(0)->setAttribute('src',  '/addon/scripts/bundle.js');
        }
    }

    public function load(){
        // Get the directory and initialize content
        $dir = dirname(__FILE__) . '/../../public/assets/';
        $files = glob($dir . 'bundle*.js');
        $content = '';
        
        // Use array to gather contents
        $contentsArray = array_map('file_get_contents', $files);
        
        // Concatenate all contents in one go
        $content = implode('', $contentsArray);   
        
        // Get the config
        require dirname(__FILE__) . '/config.php';
        $config = new Config();
        $settings = $config->getSettings();

        // Modify the bundle's copyright
        $this->copyright($content, $settings);

        // Set headers for caching and content type
        header("Content-Type: application/javascript");
        $cacheDuration = 2592000; // 1 month in seconds
        header("Cache-Control: public, max-age=$cacheDuration");
        header("Pragma: cache");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + $cacheDuration) . " GMT");

        // Output the content
        echo $content;
    }

    private function copyright(&$content, $settings){
        // Check the patch exists
        if(!file_exists(dirname(__FILE__).'/patches/copyright/needle.js') && !file_exists(dirname(__FILE__).'/patches/copyright/haystack.js')){
            $this->error(500, 'Missing Patch: copyright');
        }

        // Get the neadle
        $needle = file_get_contents(dirname(__FILE__).'/patches/copyright/needle.js');
        $haystack = file_get_contents(dirname(__FILE__).'/patches/copyright/haystack.js');

        // Run the variable exchanger for the haystack
        require dirname(__FILE__) . '/variables.php';
        $variables = new Variables;

        $variables->variableExchange($haystack, $settings);

        // Modify the copyright of the bundle
        $content = str_replace($needle, $haystack, $content);
        return $content;
    }

    public function onError($listner){
        array_push($this->errorLisnters, $listner);
    }
    private function error($error){
        foreach ($this->errorLisnters as $listner) {
            $listner($error);
        }
    }
}