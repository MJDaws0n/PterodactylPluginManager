<?php
namespace Net\MJDawson\AddonManager\Core;

class ScriptsManager{
    public function load($uri, $pluginInstances){
        if($uri[0] == 'addon' && $uri[1] == 'scripts'){
            $this->scriptsManager($uri, $pluginInstances);
        }
        if($uri[0] == 'addon' && $uri[1] == 'scripts' && $uri[2] == 'theme'){
            $this->scriptsManagerTheme($uri, $pluginInstances);
        }
        if($uri[0] == 'addon' && $uri[1] == 'styles' && $uri[2] == 'theme'){
            $this->stylesManagerTheme($uri, $pluginInstances);
        }
    }
    private function scriptsManagerTheme($uri){
        // Get the requested script
        $scriptRequest = $uri[3];

        // Get the current theme
        require_once dirname(__FILE__) . '/themes.php';
        $themesManager = new ThemesManager;
        $theme = $themesManager->getThemeByName($themesManager->getTheme());

        // Check if the script exists according to the theme;
        if(isset($theme['load_js']) && (
            in_array($scriptRequest, $theme['load_js']) ||
            in_array('/'.$scriptRequest, $theme['load_js']) ||
            in_array('./'.$scriptRequest, $theme['load_js'])
        )){
            // Check the file actuall exists
            if(file_exists(dirname(__FILE__) . '/../themes/'.$theme['name'].'/'.$scriptRequest)){
                // Set headers for caching and content type
                header("Content-Type: application/javascript");
                $cacheDuration = 2592000; // 1 month in seconds
                header("Cache-Control: public, max-age=$cacheDuration");
                header("Pragma: cache");
                header("Expires: " . gmdate("D, d M Y H:i:s", time() + $cacheDuration) . " GMT");

                echo file_get_contents(dirname(__FILE__) . '/../themes/'.$theme['name'].'/'.$scriptRequest);
            }
        }

        exit();
    }
    private function stylesManagerTheme($uri){
        // Get the requested script
        $stylesRequest = $uri[3];

        // Get the current theme
        require_once dirname(__FILE__) . '/themes.php';
        $themesManager = new ThemesManager;
        $theme = $themesManager->getThemeByName($themesManager->getTheme());

        // Check if the script exists according to the theme;
        if(isset($theme['load_css']) && (
            in_array($stylesRequest, $theme['load_css']) ||
            in_array('/'.$stylesRequest, $theme['load_css']) ||
            in_array('./'.$stylesRequest, $theme['load_css'])
        )){
            // Check the file actuall exists
            if(file_exists(dirname(__FILE__) . '/../themes/'.$theme['name'].'/'.$stylesRequest)){
                // Set headers for caching and content type
                header("Content-Type: text/css");
                $cacheDuration = 2592000; // 1 month in seconds
                header("Cache-Control: public, max-age=$cacheDuration");
                header("Pragma: cache");
                header("Expires: " . gmdate("D, d M Y H:i:s", time() + $cacheDuration) . " GMT");

                echo file_get_contents(dirname(__FILE__) . '/../themes/'.$theme['name'].'/'.$stylesRequest);
            }
        }

        exit();
    }
    private function scriptsManager($uri, $pluginInstances){
        switch($uri[2]){
            case('bundle.js'):{
                require_once dirname(__FILE__).'/bundlePatcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
            case('vendors~dashboard~server.js'):{
                require_once dirname(__FILE__).'/vendorDashPatcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
            case('vendors~auth~dashboard~server.js'):{
                require_once dirname(__FILE__).'/vendorAuthDashPatcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
            case('dashboard~server.js'):{
                require_once dirname(__FILE__).'/dashboardServerPatcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
            case('dashboard.js'):{
                require_once dirname(__FILE__).'/dashboardPatcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
            case('auth.js'):{
                require_once dirname(__FILE__).'/authPatcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
            case('server.js'):{
                require_once dirname(__FILE__).'/serverPatcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
            case('7.js'):{
                require_once dirname(__FILE__).'/7Patcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
            case('8.js'):{
                require_once dirname(__FILE__).'/8Patcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
            case('9.js'):{
                require_once dirname(__FILE__).'/9Patcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
            case('10.js'):{
                require_once dirname(__FILE__).'/10Patcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
            case('11.js'):{
                require_once dirname(__FILE__).'/11Patcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
            case('12.js'):{
                require_once dirname(__FILE__).'/12Patcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
            case('13.js'):{
                require_once dirname(__FILE__).'/13Patcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
            case('14.js'):{
                require_once dirname(__FILE__).'/14Patcher.php';
                $patcher = new Patcher();
                $patcher->load($pluginInstances);
                exit();
                break;
            }
        }
    }
}