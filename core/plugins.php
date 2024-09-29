<?php
namespace Net\MJDawson\AddonManager\Core;

class PluginsManager{
    public function getPlugins($activeOnly = false) {
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

        // Add the plugins
        foreach ($pluginsFolders as $pluginFolder) {
            if (file_exists($pluginFolder . '/main.ptero')) {
                // Get plugin info
                $plugin = json_decode(file_get_contents($pluginFolder . '/main.ptero'), true);
                if (!$plugin || !$this->validateConfig($plugin)) {
                    // Invalid config or invalid plugin, skip
                    continue;
                }
        
                // Set default 'active' status
                $plugin['active'] = false;
        
                // Load config and settings once
                require_once dirname(__FILE__) . '/config.php';
                $config = new Config;
                $settings = $config->getSettings();
        
                // Check if the plugin is in the active plugin list
                if (in_array($plugin['name'], $settings['plugins'])) {
                    $plugin['active'] = true;
                }
        
                // If activeOnly is true, skip inactive plugins
                if ($activeOnly && !$plugin['active']) {
                    continue;
                }
        
                // Add valid plugin to the list
                array_push($plugins, $plugin);
            }
        }        

        return $plugins;
    }
    public function validateConfig($config){
        // Plugins config use the following (* for require_onced):
        // name*
        // description*
        // author*
        // entry*
        // version*
        // url
        // last_updated

        if(
            isset($config['name']) && is_string($config['name']) && 
            isset($config['description']) && is_string($config['description']) && 
            isset($config['author']) && is_string($config['author']) && 
            isset($config['entry']) && is_string($config['entry']) &&
            isset($config['version']) && is_string($config['version'])
        ){
            return true;
        }
        return false;
    }

    public function load($pluginName){
        // Validate plugin exists
        if(file_exists(dirname(__FILE__) . '/../plugins/' . strval($pluginName) . '/main.ptero')){
            $plugin = json_decode(file_get_contents(dirname(__FILE__) . '/../plugins/' . strval($pluginName) . '/main.ptero'), true);
            if (!$plugin || !$this->validateConfig($plugin)) {
                return null;
            }

            // Get the file entry
            $entry = $plugin['entry'];

            // Check the entry file exists
            if(file_exists(dirname(__FILE__) . '/../plugins/' . strval($pluginName) . '/' . $entry)){
                // Just try and load it
                include_once(dirname(__FILE__).'/pluginsAPI.php');
                $pterodactyl = include_once(dirname(__FILE__) . '/../plugins/' . strval($pluginName) . '/' . $entry);

                return $pterodactyl;
            }
        }
    }
    public function upload($file) {
        // Check if a file was uploaded
        if (!isset($file)) {
            return [1, 'Invalid file'];
        }
    
        // Ensure it's a .zip file
        $fileType = mime_content_type($file['tmp_name']);
        if ($fileType !== 'application/zip') {
            return [1, 'Invalid file type. Please upload a valid .zip plugin.'];
        }
    
        // Define the plugin directory
        $pluginDir = dirname(__FILE__) . '/../plugins/';
        
        // Create a temporary location for the uploaded zip file
        $tempDir = sys_get_temp_dir() . '/' . basename($file['name']);
        
        // Move the uploaded file to the temp directory
        if (!move_uploaded_file($file['tmp_name'], $tempDir)) {
            return [1, "Failed to move the uploaded file."];
        }
    
        // Open and extract the .zip file
        $zip = new \ZipArchive();
        if ($zip->open($tempDir) === TRUE) {
            $extractPath = $pluginDir . basename($file['name'], '.zip');
    
            // Check if the folder already exists
            if (is_dir($extractPath)) {
                $zip->close();
                return [1, "Plugin directory already exists."];
            }
    
            // Extract the contents of the zip
            $zip->extractTo($extractPath);
            $zip->close();
    
            // Check for the presence of main.ptero file
            $mainPtero = $extractPath . '/main.ptero';
            if (!file_exists($mainPtero)) {
                // Cleanup: remove the extracted folder
                $this->removeDirectory($extractPath);
                return [1, "Invalid plugin. main.ptero not found."];
            }
    
            // Validate the plugin config
            $pluginConfig = json_decode(file_get_contents($mainPtero), true);
            if (!$pluginConfig || !$this->validateConfig($pluginConfig)) {
                echo "Invalid plugin configuration.";
                // Cleanup: remove the extracted folder
                $this->removeDirectory($extractPath);
                return [1, "Invalid plugin. main.ptero not found."];
            }
        } else {
            unlink($tempDir);
            return [1, "Failed to open the zip file."];
        }
    
        // Remove the temporary zip file
        unlink($tempDir);
        return [0, 'Good'];
    }
    private function removeDirectory($path) {
        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    if (is_dir($path . '/' . $file)) {
                        $this->removeDirectory($path . '/' . $file);
                    } else {
                        unlink($path . '/' . $file);
                    }
                }
            }
            rmdir($path);
        }
    }
    public function delete($pluginName){
        // Plugin folder path (modify to fit your directory structure)
        $pluginDir = dirname(__FILE__) . '/../plugins/' . $pluginName;
        
        // Ensure the plugin folder exists
        if (is_dir($pluginDir)) {
            // Delete the entire plugin folder and its contents
            function deleteDirectory($dir) {
                if (!is_dir($dir)) return;
                $items = scandir($dir);
    
                foreach ($items as $item) {
                    if ($item == '.' || $item == '..') continue;
    
                    $itemPath = $dir . '/' . $item;
                    if (is_dir($itemPath)) {
                        deleteDirectory($itemPath); // Recursively delete subdirectories
                    } else {
                        unlink($itemPath); // Delete files
                    }
                }
    
                rmdir($dir); // Finally, remove the directory itself
            }
    
            // Perform deletion
            deleteDirectory($pluginDir);
    
            echo "Plugin '$pluginName' has been successfully deleted.";
        } else {
            echo "Plugin '$pluginName' not found.";
        }
    }
}