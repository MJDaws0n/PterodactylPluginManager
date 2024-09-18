<?php
namespace Net\MJDawson\AddonManager\Core;

class PluginsManager{
    public function getPlugins() {
        // Search through the current dir then ../plugins for all folders
        // Then inside each folder open the main.ptero file and get the
        // Infomation from it in JSON format
        $plugins = array();

        if(!is_dir(dirname(__FILE__) . '/../plugins/')){
            // Punish the idiot
            echo "Plugin directory non existance. Did you really just go and delete it. Do you know how much time I spent building this? Oh my. You idiot!";
            exit();
        }
        $pluginsFolders = array_filter(glob(dirname(__FILE__) . '/../plugins/' . '/*'), 'is_dir');
        $plugins = [];

        foreach ($pluginsFolders as $pluginFolder) {
            if(file_exists($pluginFolder.'/main.ptero')){
                // It's a plugin, lets get the infomation
                if(!$plugin = json_decode(file_get_contents($pluginFolder.'/main.ptero'), true)){
                    // Plugin has an invalid config, now we just don't try to load the plugin
                    continue;
                }

                // Now need to update the plugins to display if it's active or not
                $plugin['active'] = false;

                // Check it's not already been loaded in
                if (!in_array(dirname(__FILE__) . '/config.php', get_included_files())){
                    require dirname(__FILE__) . '/config.php';
                }

                // Get the config
                $config = new Config;
                $settings = $config->getSettings();

                // Check if the plugin is in the active plugin list
                if(in_array($plugin['name'], $settings['plugins'])){
                    $plugin['active'] = true;
                }

                if(!$this->validateConfig($plugin)){
                    // Plugin has an invalid config, now we just don't try to load the plugin
                    continue;
                }

                // Add the plugins config to the file
                array_push($plugins, $plugin);
            }
        }

        return $plugins;
    }
    private function validateConfig($config){
        // Plugins config use the following (* for required):
        // name*
        // description*
        // author*
        // execute*
        // url
        // last_updated

        if(
            isset($config['name']) && is_string($config['name']) && 
            isset($config['description']) && is_string($config['description']) && 
            isset($config['author']) && is_string($config['author']) && 
            isset($config['execute']) && is_string($config['execute'])
        ){
            return true;
        }
        return false;
    }
}