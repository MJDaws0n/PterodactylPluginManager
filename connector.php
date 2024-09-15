<?php
namespace Net\MJDawson\AddonManager;

use Net\MJDawson\AddonManager\Core\Installer;
use Net\MJDawson\AddonManager\Core\Init;
use Net\MJDawson\AddonManager\Core\BundlePatcher;

class Connector {
    public function __construct($response) {
        if($this->skipLoad()){
            $response->send();
            return;
        }

        $this->scripts();

        // Check and install the addon
        require dirname(__FILE__).'/core/install.php';

        $addonInstaller = new Installer();
        $addonInstaller->install();

        // Init the main loader
        require dirname(__FILE__).'/core/init.php';
        $init = new Init();
        $crash = [$this, 'crash'];
        $init->onError(function($err) use ($crash){
            $crash(500, $err);
        });


        $init->load($response, $this->get_uri_components());
    }

    private function skipLoad(){
        // For some reason pterodactyl kills itself with this
        if($this->get_uri_components()[0] == 'sanctum' && $this->get_uri_components()[1] == 'csrf-cookie'){
            return true;
        }
        if($this->get_uri_components()[0] == 'auth' && $this->get_uri_components()[1] == 'logout'){
            return true;
        }

        return false;
    }

    public function crash($code, $error){
        http_response_code($code);
        if (error_reporting() !== 0) {
            echo "Error: <br> $error";
        } else{
            echo "Addon error. If this is your panel, consider enable error reporting with PHP to see the error";
        }
        exit();
    }

    private function get_uri_components() {
        $parsed_url = parse_url($this->get_current_url());
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $trimmed_path = trim($path, '/');
        $components = explode('/', $trimmed_path);
        return $components;
    }
    private function get_current_url(){
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $request_uri = $_SERVER['REQUEST_URI'];
        return $scheme . "://" . $host . $request_uri;
    }
    private function scripts(){
        if($this->get_uri_components()[0] == 'addon' && $this->get_uri_components()[1] == 'scripts'){
            require dirname(__FILE__).'/core/bundlePatcher.php';
            $patcher = new BundlePatcher();
            $patcher->load();
            exit();
        }
    }
}
