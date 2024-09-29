<?php
namespace Net\MJDawson\AddonManager;

use Directory;
use Net\MJDawson\AddonManager\Core\Installer;
use Net\MJDawson\AddonManager\Core\Init;
use Net\MJDawson\AddonManager\Core\Config;
use Net\MJDawson\AddonManager\Core\ScriptsManager;
use Net\MJDawson\AddonManager\Core\ThemesManager;
use Net\MJDawson\AddonManager\Core\PluginsManager;

class Connector {
    public function __construct($response) {
        if($this->skipLoad()){
            $response->send();
            return;
        }

        // Check and install the addon
        require_once dirname(__FILE__).'/core/install.php';

        $addonInstaller = new Installer();
        $addonInstaller->install();

        // Load the theme
        $uri = $this->get_uri_components();
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' && ($uri[0] == '' || $uri[0] == 'server' || $uri[0] == 'account' || ($uri[0] == 'auth' && $uri[1] == 'login'))) {
            require_once dirname(__FILE__) . '/core/themes.php';
            $themeManage = new ThemesManager;
            $theme = $themeManage->load();
    
            // Add the scripts
            foreach ($theme['scripts'] as $script) {
                echo "<script src=\"$script\"></script>";
            }
            // Add the styles
            foreach ($theme['styles'] as $style) {
                echo "<link rel=\"stylesheet\" href=\"$style\">";
            }
        }

        // Load the plugins
        require_once dirname(__FILE__) . '/core/plugins.php';
        $pluginsManager = new PluginsManager;
        $pluginInstances = [];

        $plugins = $pluginsManager->getPlugins(true);
        foreach ($plugins as $plugin) {
            // Run the plugin
            $pterodactyl = $pluginsManager->load($plugin['name']);

            // Add to the plugin instances
            array_push($pluginInstances, $pterodactyl);
        }

        $this->scripts($pluginInstances);

        // Init the main loader
        require_once dirname(__FILE__).'/core/init.php';
        $init = new Init($pluginInstances);
        $crash = [$this, 'crash'];
        $init->onError(function($err) use ($crash){
            $crash(500, $err);
        });

        $addonInstaller = new Installer();
        $addonInstaller->install();

        // Load the site
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
        if($this->get_uri_components()[0] == 'api'){
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
    private function scripts($pluginInstances){
        require_once(dirname(__FILE__).'/core/scripts.php');
        $scriptsManager = new ScriptsManager;

        $scriptsManager->load($this->get_uri_components(), $pluginInstances);
    }
}
