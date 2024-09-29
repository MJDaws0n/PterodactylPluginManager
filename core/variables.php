<?php
namespace Net\MJDawson\AddonManager\Core;

class Variables {
    // Static property to store the cached TPCC string
    private static $tpccCacheString = '';

    public function variableExchange(&$html, $settings) {
        // Set the year in the copyright
        $copyright = str_replace('{year}', date('Y'), $settings['copyright']);
        
        // Update the html
        $html = str_replace('{{COPYRIGHT}}', $copyright, $html);

        // Use cached TPCC if already generated
        $this->TPCC($html);

        return $html;
    }

    public function TPCC(&$html) {
        // Check if TPCC cache string is already generated
        if (self::$tpccCacheString === '') {
            // TPCC - Theme Plugin Cache Control
            $cacheString = '';

            // Get all the plugins
            require_once dirname(__FILE__) . '/plugins.php';
            $pluginsManager = new PluginsManager;
            $plugins = $pluginsManager->getPlugins(true);

            foreach ($plugins as $plugin) {
                // Encrypt the plugin name and version
                $cacheString .= $this->encryptString($plugin['name'] . $plugin['version'], $plugin['name'] . $plugin['version']);
            }

            // Get the active theme
            require_once dirname(__FILE__) . '/themes.php';
            $themesManager = new ThemesManager;
            $theme = $themesManager->getThemeByName($themesManager->getTheme());
            $cacheString .= $this->encryptString($theme['name'] . $theme['version'], $theme['name'] . $theme['version']);

            // Get the addon version
            require_once dirname(__FILE__) . '/config.php';
            $configManager = new Config;
            $cacheString .= $this->encryptString($configManager->getSettings()['version'], $configManager->getSettings()['version']);

            $cacheString = $this->hashString($cacheString);

            // Store the cache string in the static property
            self::$tpccCacheString = $cacheString;
        }

        // Replace the TPCC placeholder in the HTML
        $html = str_replace('{{TPCC}}', self::$tpccCacheString, $html);

        return $html;
    }

    private function encryptString($data, $key) {
        // Generate a secure IV based on the length of the key
        $iv = substr(hash('sha256', $key), 0, 16); 
    
        // Encrypt the data using AES-256-CBC
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    
        return base64_encode($encrypted);
    }

    private function hashString($data) {
        // Hash the data using SHA-256
        return hash('sha256', $data);
    }
}
